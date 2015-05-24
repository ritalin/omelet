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
    public function test_no_update_always() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());
        $w->clear();
        $this->assertFalse($w->historyModified());
    }
    
    /**
     * @test
     */
    public function test_no_update_once() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());
        $w->clear();
        $this->assertFalse($w->historyModified());
    }

    /**
     * @test
     */
    public function test_no_update_whenever() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());
        $w->clear();
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
    
    /**
     * @test
     */
    public function test_update_dao_with_always_mode() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());
        $w->clear();
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));

        $w->save(false);

        $this->assertTrue($w->outdated($path));
        $this->assertTrue($w->outdated($path));
        
        $this->modifyDao();
        
        $this->assertTrue($w->outdated($path));
        $this->assertTrue($w->outdated($path));

        $w->save(false);
        
        $this->assertTrue($w->outdated($path));
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_once_mode() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());
        $w->clear();
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));

        $w->save(false);

        $this->assertFalse($w->outdated($path));
        $this->assertFalse($w->outdated($path));
        
        $this->modifyDao();

        $this->assertFalse($w->outdated($path));
        $this->assertFalse($w->outdated($path));        

        $w->save(false);

        $this->assertFalse($w->outdated($path));
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_whenever_mode() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());
        $w->clear();
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));

        $w->save(false);

        $this->assertFalse($w->outdated($path));
        $this->assertFalse($w->outdated($path));
        
        $this->modifyDao();

        $this->assertTrue($w->outdated($path));

        $w->save(false);
      
        $this->assertFalse($w->outdated($path));
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_always_mode_permanently() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());
        $w->clear();
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));

        $w->save(true);

        $this->assertTrue($w->outdated($path));

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());

        $this->assertTrue($w->outdated($path));
        
        $this->modifyDao();
        
        $this->assertTrue($w->outdated($path));

        $w->save(true);
        
        $this->assertTrue($w->outdated($path));

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Always());

        $this->assertTrue($w->outdated($path));
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_once_mode_permanently() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());
        $w->clear();
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));

        $w->save(true);

        $this->assertFalse($w->outdated($path));

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());

        $this->assertFalse($w->outdated($path));
        
        $this->modifyDao();

        $this->assertFalse($w->outdated($path));

        $w->save(true);

        $this->assertFalse($w->outdated($path));

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Once());

        $this->assertFalse($w->outdated($path));        
    }
    
    /**
     * @test
     */
    public function test_update_dao_with_whenever_mode_permanently() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());
        $w->clear();
        
        $class = new \ReflectionClass(SwitchDao::class);
        $path = $class->getFileName();
        
        $this->assertTrue($w->outdated($path));

        $w->save(true);

        $this->assertFalse($w->outdated($path));

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());

        $this->assertFalse($w->outdated($path));
        
        $this->modifyDao();

        $this->assertTrue($w->outdated($path));

        $w->save(true);

        $this->assertFalse($w->outdated($path));

        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());

        $this->assertFalse($w->outdated($path));        
    }
    
    private function modifySql($route) {    
        $lockFile = self::daoRoot . '/lockSql';
        if (file_exists("{$lockFile}_1")) {
            $source = "{$route}_2";
            $dest = $route;
            $from = 1;
            $to = 2;
        }
        else {
            $source = "{$route}_1";
            $dest = $route;
            $from = 2;
            $to = 1;
        }

        foreach (glob("{$source}/*.sql", GLOB_NOSORT) as $f) {
            $name = basename($f);
            copy($f, "{$dest}/{$name}");
        }

        if (file_exists("{$lockFile}_{$from}")) {
            rename("{$lockFile}_{$from}", "{$lockFile}_{$to}");
        }
        else {
            touch("{$lockFile}_{$to}");
        }
    }

    /**
     * @test
     */
    public function test_update_sql_with_whenever_mode_permanently() {
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());
        $w->clear();

        $route = __DIR__ . '/fixtures/sql/SwitchDao';
        
        $this->modifySql($route);

        $this->assertTrue($w->sqlOutdated($route));

        $w->save(true);

        $this->assertFalse($w->sqlOutdated($route));
        
        $w = new ChangeWatcher(self::daoRoot, WatchMode::Whenever());

        $this->assertFalse($w->sqlOutdated($route));

        $this->modifySql($route);

        $this->assertTrue($w->sqlOutdated($route));
    }
}
