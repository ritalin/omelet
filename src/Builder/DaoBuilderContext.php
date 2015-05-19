<?php

namespace Omelet\Builder;

use Composer\Autoload\ClassLoader;

class DaoBuilderContext {
    public static function defaultConfig() {
        static $config = [
            'daoClassPath' => '_auto_generated',
            'sqlRootDir' => 'sql',
            'pdoDsn' => [],
            'daoClassSuffix' => 'Impl'
        ];
        
        return $config;
    }
    
    private $config;
    
    public function __construct(array $config = []) {
        $this->config = array_merge(self::defaultConfig(), $config);
        
        $loader = new ClassLoader();
        $loader->addPsr4('', $this->config['daoClassPath']);
        // AnnotationRegistry::registerLoader([$loader, 'loadClass']);
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
    
    public function queriesOf($intfName) {
        $rootDir = $this->config['sqlRootDir'] . $intfName;
        
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
        
        $setting = new DaoBuilder($intfName, $className);
        
        $path = "{$classPath}/{$className}.php";
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $setting->export(true));
    }
    
    public function getConfig() {
        return $this->config;
    }
}
