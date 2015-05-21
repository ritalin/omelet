<?php

namespace Omelet\Builder;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PhpParser;

use Doctrine\DBAL\Types\Type;

use Omelet\Annotation\Core\DaoAnnotation;
use Omelet\Annotation\AnnotationFactory;

use Omelet\Annotation\ParamAlt;

use Omelet\Annotation\Select;
use Omelet\Annotation\Returning;

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
    
    /**
     * @var DomainFactory
     */
    private $factory;
    
    public function __construct(\ReflectionClass $intf, $className) {
        $this->intf = $intf;
        $this->className = $className;
        $this->factory = new DomainFactory();
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
    
    private function extractAnnotation(array $attrs, $class) {
        $results = array_filter(
            $attrs,
            function ($a) use($class) {
                return ($a instanceof $class);
            }
        );
        
        return array_shift($results);
    }
    
    public function prepare() {
        $reader = new AnnotationReader();

        $classParser = new PhpParser;
        $commentParser = new AnnotationFactory(
            $classParser->parseClass($this->intf) + [$this->intf->getNamespaceName()]
        );

        $this->methods = array_reduce(
            $this->intf->getMethods(),
            function (array &$tmp, \ReflectionMethod $m) use($reader, $commentParser) {
                $attrs = array_merge($reader->getMethodAnnotations($m), $commentParser->getMethodAnnotations($m));

                return $tmp + [$m->name => [
                    'name' => $m->name,
                    'type' => $this->extractAnnotation($attrs, DaoAnnotation::class),
                    'params' => $m->getParameters(),
                    'paramDomain' => $this->paramToDomain($m->getParameters(), $attrs, $reader),
                    'returnDomain' => $this->returningToDomain($attrs, $reader),
                ]];
            },
            []
        );
    }
    
    private function paramToDomain(array $params, array $attrs, AnnotationReader $reader) {
        $paramDefs = $this->extractParamDefs($attrs);
        
        $domains = array_reduce(
            $params,
            function (&$tmp, \ReflectionParameter $p) use($reader, $paramDefs) {
                if ((isset($paramDefs[$p->name])) ) {
                    $t = $paramDefs[$p->name];
                }
                else if ($p->getClass() !== null) {
                    $t = $p->getClass()->name;
                }
                else {
                    $t = Type::STRING;
                }

                return $tmp + [$p->name => $this->factory->parse('', $t, $reader)];
            },
            []
        );
        
        return new ComplexDomain($domains);
    }
    
    private function returningToDomain(array $attrs, AnnotationReader $reader) {
        if ($this->extractAnnotation($attrs, Select::class) !== null) {
            $returning = $this->extractAnnotation($attrs, Returning::class);
            
            return $this->factory->parse('', isset($returning) ? $returning->type : 'array', $reader);
        }
        else {
            return $this->factory->parse('', 'int', $reader);
        }
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
        $returning = var_export($method['returnDomain'], true);
        
        switch (get_class($method['type'])) {
        case Select::class:
            $caller = "fetchAll";
            break;
        default:
            $caller = "execute";
            break;
        }
        
        return 
"    public function {$methodName}({$paramDefs}) {
        \$paramDomain = {$domain};
        \$params = [$params];
        \$returnDomain = {$returning};
        
        \$rows = \$this->{$caller}('$methodName', \$paramDomain->expandValues('', \$params), \$paramDomain->expandTypes('', \$params));
        
        return \$this->convertResults(\$rows, \$returnDomain);
    }"

        ;
    }
}
