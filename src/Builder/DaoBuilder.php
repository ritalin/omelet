<?php

namespace Omelet\Builder;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PhpParser;

use Doctrine\DBAL\Types\Type;

use Omelet\Annotation\Core\DaoAnnotation;
use Omelet\Annotation\AnnotationConverterAdapter;

use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\SequenceHint;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Select;
use Omelet\Annotation\Delete;
use Omelet\Annotation\Returning;
use Omelet\Annotation\Core\AbstractCommandAnnotation;

use Omelet\Domain\DomainFactory;
use Omelet\Domain\ArrayDomain;
use Omelet\Domain\ComplexDomain;

use Omelet\Sequence\SequenceNameStrategyInterface;

use Omelet\Util\CaseSensor;

class DaoBuilder
{
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
     * @var string[]
     */
    private $config = [];

    /**
     * @var SequenceHint
     */
    private $seqHint;

    /**
     * @var DomainFactory
     */
    private $factory;

    /**
     * @var CaseSensor
     */
    private $paramCaseSensor;
    /**
     * @var CaseSensor
     */
    private $returnCaseSensor;

    public function __construct(\ReflectionClass $intf, $className)
    {
        $this->intf = $intf;
        $this->className = $className;

        $this->factory = new DomainFactory();
        $this->paramCaseSensor = $this->returnCaseSensor = CaseSensor::LowerSnake();
    }

    public function getInterfaceName()
    {
        return $this->intf->name;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getParamCaseSensor()
    {
        return $this->paramCaseSensor;
    }

    public function setParamCaseSensor($sensor)
    {
        if ($sensor === null) {
            return;
        }

        $this->paramCaseSensor = $sensor;
    }

    public function getReturnCaseSensor()
    {
        return $this->returnCaseSensor;
    }

    public function setReturnCaseSensor($sensor)
    {
        if ($sensor === null) {
            return;
        }

        $this->returnCaseSensor = $sensor;
    }

    private function extractAnnotation(array $attrs, $class)
    {
        $results = array_filter(
            $attrs,
            function ($a) use ($class) {
                return ($a instanceof $class);
            }
        );

        return array_shift($results);
    }

    public function prepare()
    {
        $reader = new AnnotationReader();

        $commentParser = new AnnotationConverterAdapter($this->intf);

        $annotations = $commentParser->getClassAnnotations();
        $this->config = $this->extractDaoClassConfig($annotations);
        $this->seqHint = $this->extractAnnotation($annotations, SequenceHint::class);

        $this->methods = array_reduce(
            $this->intf->getMethods(),
            function (array &$tmp, \ReflectionMethod $m) use ($reader, $commentParser) {
                $attrs = $commentParser->getMethodAnnotations($m);

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

    private function extractDaoClassConfig(array $annotations)
    {
        $config = [];

        $a = $this->extractAnnotation($annotations, Dao::class);
        if (isset($a)) {
            foreach ($a as $field => $value) {
                $config[$field] = $value;
            }
        }
        return $config;
    }

    private function paramToDomain(array $params, array $attrs, AnnotationReader $reader)
    {
        $paramDefs = $this->extractParamDefs($attrs);

        $domains = array_reduce(
            $params,
            function (&$tmp, \ReflectionParameter $p) use ($reader, $paramDefs) {
                if (isset($paramDefs[$p->name])) {
                    $t = $paramDefs[$p->name];
                }
                elseif ($p->getClass() !== null) {
                    $t = $p->getClass()->name;
                }
                else {
                    $t = Type::STRING;
                }

                return $tmp + [$p->name => $this->factory->parse('', $t, $this->paramCaseSensor)];
            },
            []
        );

        return new ComplexDomain($domains);
    }

    private function returningToDomain(array $attrs, AnnotationReader $reader)
    {
        if ($this->extractAnnotation($attrs, Select::class) !== null) {
            $returning = $this->extractAnnotation($attrs, Returning::class);

            return $this->factory->parse('', isset($returning) ? $returning->type : 'array', $this->returnCaseSensor);
        }
        else {
            return $this->factory->parse('', 'int', $this->returnCaseSensor);
        }
    }

    private function extractParamDefs(array $attrs)
    {
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

    public function export($return = false)
    {
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

            return;
        }
    }

    private function classTemplate()
    {
        $className = $this->getClassName();

        $p = strrpos($className, '\\');
        $ns = substr($className, 0, $p);
        $name = substr($className, $p + 1);

        $accessRoute = array_merge(
            preg_split('/[\/\\\\]/', ($this->config['route'] !== '') ? $this->config['route'] : $ns),
            [$this->intf->getShortName()]
        );
        $accessRoute = implode(DIRECTORY_SEPARATOR, $accessRoute);

        if ($ns !== '') {
            $ns = "namespace {$ns};";
        }

        $seqHint = var_export($this->seqHint, true);

        return
"<?php

$ns
/// Auto-generated class

use Omelet;
use Omelet\Core\DaoBase;
use Omelet\Builder\DaoBuilderContext;
use Omelet\Util\CaseSensor;

use Doctrine\DBAL\Driver\Connection;

class {$name} extends DaoBase implements \\{$this->getInterfaceName()} {
    const AccessRoute = '{$accessRoute}';

    private \$paramFormatter;

    public function __construct(Connection \$conn, DaoBuilderContext \$context) {
        \$hint = {$seqHint};
        \$seq = \$context
            ->getSequenceNameManager()
            ->findStrategy(\$conn->getDriver()->getName())
            ->resolve(\$hint);

        \$this->paramFormatter = CaseSensor::{\$context->getConfig()->paramCaseSensor}();

        parent::__construct(\$conn, \$context->queriesOf('\\{$this->intf->name}'), empty(\$seq) ? null : \$seq);
    }
%s
}
"
        ;
    }

    private function extractTypeHint(\ReflectionParameter $p)
    {
        $hint = $p->getClass();

        if (isset($hint)) {
            return "\\{$hint->name} ";
        }
        elseif ($p->isArray()) {
            return 'array ';
        }
        else {
            return '';
        }
    }

    private function getMethodByType($annotation, $returnDomain = null)
    {
        $type = get_class($annotation);

        switch (true) {
            case $type === Select::class:
                $class = ArrayDomain::class;
                if ($returnDomain instanceof $class) {
                    return ['fetchAll', []];
                }
                else {
                    return ['fetchRow', []];
                }
            case is_subclass_of($type, AbstractCommandAnnotation::class):
                return ['execute', ['returning' => $annotation->returning]];
            case $type === Delete::class:
                return ['execute', []];
            default:
                throw new \LogicException("Unsupported command/Query annotation. ($type)");
        }
    }

    private function methodTemplate(array $method)
    {
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
        $params = implode(
            ', ',
            array_map(
                function (\ReflectionParameter $p) {
                    return "'{$p->name}' => \${$p->name}";
                },
                $method['params']
            )
        );
        $returning = var_export($method['returnDomain'], true);

        list($caller, $opts) = $this->getMethodByType($method['type'], $method['returnDomain']);
        $opts = var_export($opts, true);

        return
"    public function {$methodName}({$paramDefs}) {
        \$params = [$params];
        \$opts = $opts;
        \$returnDomain = {$returning};

        \$rows = \$this->{$caller}('$methodName', \$opts, function (\$paramNames) use (\$params) {
            \$paramDomain = {$domain};

            return [
                \$paramDomain->expandValues(\$paramNames, '', \$params, \$this->paramFormatter),
                \$paramDomain->expandTypes(\$paramNames, '', \$params, \$this->paramFormatter)
            ];
        });

        return \$this->convertResults(\$rows, \$returnDomain);
    }"
        ;
    }
}
