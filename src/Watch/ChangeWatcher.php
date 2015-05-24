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
    
    public function historyModified() {
        $d1 = count(array_diff_assoc($this->oldHistories, $this->newHistories));
        $d2 = count(array_diff_assoc($this->newHistories, $this->oldHistories));

        return ($d1 !== 0) || ($d2 !== 0);
    }
    
    public function sqlOutdated($route) {
        $results = [];
        foreach (glob("{$route}/*.sql", GLOB_NOSORT) as $path) {
            $results[] = $this->outdated($path);
        }
        
        return in_array(true, $results);
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
        $this->newHistories[$path] = $hash = md5_file($path);
        
        return isset($this->oldHistories[$path]) ? ($this->oldHistories[$path] !== $hash) : true;
    }
    
    public function clear() {
        $this->clearHistory($this->getHistoryPath());
            
        $this->oldHistories = $this->newHistories = [];
    }

    private function clearHistory($file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function save($permanently = true) {
        if ($this->historyModified()) {
            if ($permanently) {
                $path = $this->getHistoryPath();
                $dir = basename($path);
                
                if (! file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
                
                file_put_contents($path, json_encode($this->newHistories));
            }
            $this->oldHistories = $this->newHistories;
        }
    }

    public function getHistoryPath() {
        $f = self::$fileName;
        
        return "{$this->daoRootPath}/{$f}";
    }
}
