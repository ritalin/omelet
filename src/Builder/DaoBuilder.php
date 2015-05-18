<?php

namespace Omelet\Builder;

use Doctrine\Common\Annotations\AnnotationReader;

use Omelet\Annotation\Core\DaoAnnotation;

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
                ]];
            },
            []
        );
    }
    
    public function export($return = false) {
        $classDef = $this->classTemplate();
        $methodDefs = array_map(
            function (\ReflectionMethod $m) {
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

use MyVendor\Weekday\Module\Query\DaoBase;
use MyVendor\Weekday\Module\Query\DaoBuilderContext;

use Doctrine\DBAL\Driver\Connection;

class {$name} extends DaoBase implements \\{$this->getInterfaceName()} {
    public function __construct(Connection \$conn, DaoBuilderContext \$context) {
        parent::__construct(\$conn, \$context->queriesOf('{$this->intfName}'));
    }
    
%s
}
"
         ;
    }
    
    private function methodTemplate(\ReflectionMethod $method) {
        $paramDefs = implode(', ', 
            array_map(
                function (\ReflectionParameter $p) {
                    $hint = $p->getClass();
                    
                    return (isset($hint) ? "\\{$hint->getName()} ": "") . "\${$p->getName()}";
                },
                $method->getParameters()
            )
        );
        
        $params = implode(', ', 
            array_map(
                function (\ReflectionParameter $p) {
                    return "'{$p->name}' => \${$p->name}";
                },
                $method->getParameters()
            )
        );
        $methodName = $method->getName();
        
        return 
"    public function {$methodName}({$paramDefs}) {
        return \$this->execute( '$methodName', [{$params}]);
    }"

        ;
    }
}
