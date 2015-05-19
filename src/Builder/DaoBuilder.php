<?php

namespace Omelet\Builder;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;

use Omelet\Annotation\Core\DaoAnnotation;
use Omelet\Annotation\ParamAlt;

use Omelet\Domain\DomainFactory;
use Omelet\Domain\ComplexDomain;

class DaoBuilder {
    /**
     * @var \ReflectionClass 
     */
    private $intf;
    /**
     * @var string 
     */
    private $className;
    private $methods = [];
    
    public function __construct(\ReflectionClass $intf, $className) {
        $this->intf = $intf;
        $this->className = $className;
    }
    
    public function getInterfaceName() {
        return $this->intf->name;
    }
    
    public function getClassName() {
        return $this->className;
    }
    
    public function getMethods() {
        return $this->methods;
    }
    
    private function extractQueryType(array $attrs) {
        $results = array_filter(
            $attrs,
            function ($a) {
                return ($a instanceof DaoAnnotation);
            }
        );
        
        return array_shift($results);
    }
    
    public function prepare() {
        $reader = new AnnotationReader();
    
        $this->methods = array_reduce(
            $this->intf->getMethods(),
            function (array &$tmp, \ReflectionMethod $m) use($reader) {
                $attrs = $reader->getMethodAnnotations($m);

                return $tmp + [$m->name => [
                    'name' => $m->name,
                    'type' => $this->extractQueryType($attrs),
                    'params' => $m->getParameters(),
                    'paramDomain' => $this->paramToDomain($m->getParameters(), $attrs, $reader),
                ]];
            },
            []
        );
    }
    
    private function paramToDomain(array $params, array $attrs, AnnotationReader $reader) {
        $factory = new DomainFactory();

        $paramDefs = $this->extractParamDefs($attrs);
        
        $domains = array_reduce(
            $params,
            function (&$tmp, \ReflectionParameter $p) use($reader, $factory, $paramDefs) {
                if ((isset($paramDefs[$p->name])) ) {
                    $t = $paramDefs[$p->name];
                }
                else if ($p->getClass() !== null) {
                    $t = $p->getClass()->name;
                }
                else {
                    $t = Type::STRING;
                }

                return $tmp + [$p->name => $factory->parse('', $t, $reader)];
            },
            []
        );
        
        return new ComplexDomain($domains);
    }
    
    private function extractParamDefs(array $attrs) {
        $defs = array_filter(
            $attrs,
            function ($a) { return $a instanceof ParamAlt; }
        );
        
        return array_reduce(
            $defs,
            function (array &$tmp, $d) {
                return $tmp + [$d->name => $d->type];
            },
            []
        );
    }
    
    public function export($return = false) {
        $classDef = $this->classTemplate();
        $methodDefs = array_map(
            function (array $m) {
                return $this->methodTemplate($m);
            },
            $this->methods
        );
        
        $def = sprintf($classDef, implode("\n", $methodDefs));
        
        if ($return) {
            return $def;
        }
        else {
            echo $def;
            return null;
        }
    }
    
    private function classTemplate() {
        $className = $this->getClassName();
        
        $p = strrpos($className, '\\');
        $ns = substr($className, 0, $p);
        $name = substr($className, $p+1);
        
        if ($ns !== '') {
            $ns = "namespace {$ns};";
        }
        
        return 
"<?php

$ns
/// Auto-generated class 

use Omelet;
use Omelet\Core\DaoBase;
use Omelet\Builder\DaoBuilderContext;

use Doctrine\DBAL\Driver\Connection;

class {$name} extends DaoBase implements \\{$this->getInterfaceName()} {
    public function __construct(Connection \$conn, DaoBuilderContext \$context) {
        parent::__construct(\$conn, \$context->queriesOf('\\{$this->intf->name}'));
    }
    
%s
}
"
         ;
    }
    
    private function extractTypeHint(\ReflectionParameter $p) {
        $hint = $p->getClass();
        
        if (isset($hint)) {
            return "\\{$hint->name} ";
        }
        else if ($p->isArray()) {
            return "array ";
        }
        else {
            return "";
        }
    }
    
    private function methodTemplate(array $method) {
        $paramDefs = implode(', ', 
            array_map(
                function (\ReflectionParameter $p) {
                    return $this->extractTypeHint($p) . "\${$p->name}";
                },
                $method['params']
            )
        );

        $methodName = $method['name'];
        $domain = var_export($method['paramDomain'], true);
        $params = implode(', ', 
            array_map(
                function (\ReflectionParameter $p) {
                    return "'{$p->name}' => \${$p->name}";
                },
                $method['params']
            )
        );

        return 
"    public function {$methodName}({$paramDefs}) {
        \$domain = {$domain};
        \$params = [$params];
        
        return \$this->execute('$methodName', \$domain->expandValues('', \$params), \$domain->expandTypes('', \$params));
    }"

        ;
    }
}
