<?php

namespace Omelet\Watch;

class ChangeWatcher {
    /** 
     * @var string
     */
    private $daoRootPath;
    
    /**
     * @var WatchMode
     */
    private $mode;
    
    /**
     * @var string[]
     */
    private $newHistories = [];
    
    /**
     * @var string[]
     */
    private $oldHistories = [];
    
    /**
     * @param string daoRootPath
     */
     
    private static $fileName = 'history.json';
     
    public function __construct($daoRootPath, WatchMode $mode) {
        $this->daoRootPath = $daoRootPath;
        $this->mode = $mode;
        
        $path = $this->getHistoryPath();

        if (file_exists($path)) {
            $this->oldHistories = json_decode(file_get_contents($path), true);
        }
        
    }
    
    public function __destruct() {
        if ($this->historyModified()) {
            $path = $this->getHistoryPath();
            $dir = basename($path);
            
            if (! file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
            
            file_put_contents($path, json_encode($this->newHistories));
        }
    }
    
    public function historyModified() {
        $d1 = count(array_diff_assoc($this->oldHistories, $this->newHistories));
        $d2 = count(array_diff_assoc($this->newHistories, $this->oldHistories));

        return ($d1 !== 0) || ($d2 !== 0);
    }
    
    public function sqlOutdated($fqcn) {
        if (! class_exists($fqcn)) return true;
        
        $route = $fqcn::AccessRoute;
        
        foreach (glob("{$route}/*.sql", GLOB_NOSORT) as $path) {
            if ($this->outdated($path)) return true;
        }
        
        return false;
    }
    
    public function outdated($path) {
        if ($this->mode == WatchMode::Always()) {
            return true;
        }
        else if ($this->mode == WatchMode::Once()) {
            if (isset($this->oldHistories[$path])) {
                $this->newHistories[$path] = $this->oldHistories[$path];
                return false;
            }
        }

        $hash = $this->newHistories[$path] = (string)md5_file($path);
        
        return isset($this->oldHistories[$path]) ? ($this->oldHistories[$path] !== $hash) : true;
    }
    
    public function getHistoryPath() {
        $f = self::$fileName;
        
        return "{$this->daoRootPath}/{$f}";
    }
}
