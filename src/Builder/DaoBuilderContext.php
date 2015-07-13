<?php

namespace Omelet\Builder;

use Composer\Autoload\ClassLoader;
use Omelet\Watch\ChangeWatcher;
use Omelet\Watch\WatchMode;
use Omelet\Sequence\SequenceNameManager;
use Omelet\Sequence\SequenceNameStrategyInterface;
use Omelet\Util\CaseSensor;

class DaoBuilderContext
{
    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var ChangeWatcher
     */
    private $watcher;

    /**
     * @var SequenceNameManager
     */
    private $sequenceManager;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->config->validate();

        $this->watcher = new ChangeWatcher($this->config->daoClassPath, WatchMode::{$this->config->watchMode}());
        $this->sequenceManager = new SequenceNameManager();

        $this->registerClassLoader();
    }

    private function registerClassLoader()
    {
        $loader = new ClassLoader();
        $loader->addPsr4('', $this->config->daoClassPath);
        $loader->register();
    }

    /**
     * @param string intfName
     *
     * @return string
     */
    public function getDaoClassName($intfName)
    {
        return $intfName . $this->config->daoClassSuffix;
    }

    /**
     * @return string
     */
    public function connectionString()
    {
        return $this->config->connectionString;
    }

    private function normalizePath($path)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    public function getSequenceNameManager()
    {
        return $this->sequenceManager;
    }
    /**
     * @param string intfName
     */
    public function queriesOf($intfName)
    {
        $className = $this->getDaoClassName($intfName);
        $accessRoute = $className::AccessRoute;

        $rootDir = $this->normalizePath("{$this->config->sqlRootDir}/{$accessRoute}");

        $t = new \ReflectionClass($intfName);

        return array_reduce(
            $t->getMethods(),
            function (array &$tmp, $m) use ($rootDir) {
                $path = $rootDir . "/{$m->name}.sql";
                if (! is_readable($path)) {
                    throw new \Exception("File not found: $path");
                }

                return $tmp + [$m->name => file_get_contents($path)];
            },
            []
        );
    }

    /**
     * @param string intfName
     */
    public function build($intfName)
    {
        $classPath = $this->config->daoClassPath;
        $className = $this->getDaoClassName($intfName);

        $path = $this->normalizePath("{$classPath}/{$className}.php");
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $ref = new \ReflectionClass($intfName);
        if ($this->watcher->outdated($ref->getFileName()) || $this->watcher->sqlOutdated($className::AccessRoute)) {
            $builder = new DaoBuilder($ref, $className);
            $builder->setParamCaseSensor(CaseSensor::{$this->config->paramCaseSensor}());
            $builder->setReturnCaseSensor(CaseSensor::{$this->config->returnCaseSensor}());

            $builder->prepare();

            file_put_contents($path, $builder->export(true));
        }
    }

    public function saveHistory()
    {
        $this->watcher->save();
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function __wakeup()
    {
        $this->registerClassLoader();
    }
}
