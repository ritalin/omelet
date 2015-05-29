<?php

namespace Omelet\Builder;

use Composer\Autoload\ClassLoader;

use Omelet\Watch\ChangeWatcher;
use Omelet\Watch\WatchMode;
use Omelet\Util\CaseSensor;

class DaoBuilderContext {
    public static function defaultConfig() {
        return [
            'daoClassPath' => '_auto_generated',
            'sqlRootDir' => 'sql',
            'pdoDsn' => [],
            'daoClassSuffix' => 'Impl',
            'watchMode' => WatchMode::Whenever(),
            'paramCaseSensor' => null,
            'returnCaseSensor' => null,
        ];
    }
    
    /** 
     * @var array
     */
    private $config;
    
    /**
     * @var ChangeWatcher
     */
    private $watcher;

    public function __construct(array $config = []) {
        $this->config = array_merge(self::defaultConfig(), $config);
        $this->watcher = new ChangeWatcher($this->config['daoClassPath'], $this->config['watchMode']);

        $loader = new ClassLoader();
        $loader->addPsr4('', $this->config['daoClassPath']);
        $loader->register();
    }
    
    public function getDaoClassName($intfName) {
        return $intfName . $this->config['daoClassSuffix'];
    }
    
    public function connectionString() {
        return implode('&', array_map(
            function ($k, $v) { return "{$k}=${v}"; },
            array_keys($this->config['pdoDsn']),
            $this->config['pdoDsn']
        ));
    }
    
    private function normalizePath($path) {
        return str_replace(['/', "\\"], DIRECTORY_SEPARATOR, $path);
    }

    public function queriesOf($intfName) {
        $className = $this->getDaoClassName($intfName);
        $accessRoute = $className::AccessRoute;

        $rootDir = $this->normalizePath("{$this->config['sqlRootDir']}/{$accessRoute}");

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
    
    public function build($intfName) {
        $classPath = $this->config['daoClassPath'];
        $className = $this->getDaoClassName($intfName);
        
        $path = $this->normalizePath("{$classPath}/{$className}.php");
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        
        $ref = new \ReflectionClass($intfName);
        if ($this->watcher->outdated($ref->getFileName()) || $this->watcher->outdated($className::AccessRoute)) {
            $builder = new DaoBuilder($ref, $className);
            $builder->setParamCaseSensor($this->config['paramCaseSensor']);
            $builder->setReturnCaseSensor($this->config['returnCaseSensor']);
            
            $builder->prepare();
            
            file_put_contents($path, $builder->export(true));
        }
    }
    
    public function getConfig() {
        return $this->config;
    }
}
