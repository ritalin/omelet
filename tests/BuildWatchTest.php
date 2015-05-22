<?php

namespace OmeletTests;

use Omelet\Tests\Target\TodoDao;
use Omelet\Tests\Target\SwitchDao;

use Omelet\Watch\ChangeWatcher;
use Omelet\Watch\WatchMode;

class BuildWatchTest extends \PHPUnit_Framework_TestCase {
    const daoRoot = __DIR__ . '/fixtures/exports';
    /**
     * @test
     */
    public function test_no_update() {

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());
        $this->clearHistory($w->getHistoryPath());
        $this->assertFalse($w->historyModified());
        
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());
        $this->clearHistory($w->getHistoryPath());
        $this->assertFalse($w->historyModified());
        
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());
        $this->clearHistory($w->getHistoryPath());
        $this->assertFalse($w->historyModified());
    }
    
    private function modifyDao() {
        
        $daoFile = __DIR__ . '/Target/SwitchDao';
        if (file_exists("{$daoFile}_1a.php")) {
            copy("{$daoFile}_2.php", "{$daoFile}.php");
            rename("{$daoFile}_1a.php", "{$daoFile}_1.php");
            rename("{$daoFile}_2.php", "{$daoFile}_2a.php");
        }
        else {
            copy("{$daoFile}_1.php", "{$daoFile}.php");
            rename("{$daoFile}_1.php", "{$daoFile}_1a.php");
            rename("{$daoFile}_2a.php", "{$daoFile}_2.php");
        }
    }
    
    private function clearHistory($file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_always_mode() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));
        
        $this->modifyDao();
        
        $this->assertTrue($w->outdated($path));
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_once_mode() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());
        $this->clearHistory($w->getHistoryPath());
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));
        
//        $this->modifyDao();
        
//        $this->assertTrue($w->outdated($path));
        
    }
}
