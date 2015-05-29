<?php

namespace Omelet\Builder;

use Composer\Autoload\ClassLoader;

use Omelet\Watch\ChangeWatcher;
use Omelet\Watch\WatchMode;
use Omelet\Util\CaseSensor;

class DaoBuilderContext {
    /** 
     * @var Configuration
     */
    private $config;
    
    /**
     * @var ChangeWatcher
     */
    private $watcher;

    public function __construct(Configuration $config) {
        $this->config = $config;
        $this->config->validate();

        $this->watcher = new ChangeWatcher($this->config->daoClassPath, WatchMode::{$this->config->watchMode}());

        $loader = new ClassLoader();
        $loader->addPsr4('', $this->config->daoClassPath);
        $loader->register();
    }
    
    /**
     * @param string intfName
     * @return string
     */
    public function getDaoClassName($intfName) {
        return $intfName . $this->config->daoClassSuffix;
    }
    
    /**
     * @return string
     */
    public function connectionString() {
        return $this->config->pdoDsn;
    }
    
    /**
     * @return array
     */
    public function dsn() {
        return array_reduce(
            explode('&', $this->config->pdoDsn),
            function (array &$tmp, $kv) {
                $tk = explode('=', $kv);

                return $tmp + [trim($tk[0]) => trim($tk[1])];
            },
            []
        );
    }
    
    private function normalizePath($path) {
        return str_replace(['/', "\\"], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @param string intfName
     */
    public function queriesOf($intfName) {
        $className = $this->getDaoClassName($intfName);
        $accessRoute = $className::AccessRoute;

        $rootDir = $this->normalizePath("{$this->config->sqlRootDir}/{$accessRoute}");

        $t = new \ReflectionClass($intfName);
        
        return array_reduce(
            $t->getMethods(),
            function (array &$tmp, $m) use($rootDir) {
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
    public function build($intfName) {
        $classPath = $this->config->daoClassPath;
        $className = $this->getDaoClassName($intfName);
        
        $path = $this->normalizePath("{$classPath}/{$className}.php");
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        
        $ref = new \ReflectionClass($intfName);
        if ($this->watcher->outdated($ref->getFileName()) || $this->watcher->outdated($className::AccessRoute)) {
            $builder = new DaoBuilder($ref, $className);
            $builder->setParamCaseSensor(CaseSensor::{$this->config->paramCaseSensor}());
            $builder->setReturnCaseSensor(CaseSensor::{$this->config->returnCaseSensor}());
            
            $builder->prepare();
            
            file_put_contents($path, $builder->export(true));
        }
    }
    
    /**
     * @return Configuration
     */
    public function getConfig() {
        return $this->config;
    }
}
