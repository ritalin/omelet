<?php

namespace Omelet\Tests;

use Omelet\Annotation\SequenceHint;
use Omelet\Sequence\SqliteSequenceStrategy;
use Omelet\Sequence\DefaultSequenceStrategy;
use Omelet\Sequence\SequenceNameManager;

class SequenceManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_sqlite_strategy()
    {
        $hint = new SequenceHint();

        $strategy = new SqliteSequenceStrategy();
        $this->assertNull($strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_sqlite_strategy_with_table()
    {
        $hint = new SequenceHint();
        $hint->table = 'aaa';

        $strategy = new SqliteSequenceStrategy();
        $this->assertEquals('aaa', $strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_sqlite_strategy_with_table_and_column()
    {
        $hint = new SequenceHint();
        $hint->table = 'aaa';
        $hint->column = 'id';

        $strategy = new SqliteSequenceStrategy();
        $this->assertEquals('aaa', $strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_sqlite_strategy_with_name()
    {
        $hint = new SequenceHint();
        $hint->name = 'aaa_id_seq';

        $strategy = new SqliteSequenceStrategy();
        $this->assertNull($strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_sqlite_strategy_with_all()
    {
        $hint = new SequenceHint();
        $hint->table = 'aaa';
        $hint->column = 'id';
        $hint->name = 'aaa_id_seq';

        $strategy = new SqliteSequenceStrategy();
        $this->assertEquals('aaa', $strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_default_strategy()
    {
        $hint = new SequenceHint();

        $strategy = new DefaultSequenceStrategy();
        $this->assertNull($strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_default_strategy_with_table()
    {
        $hint = new SequenceHint();
        $hint->table = 'aaa';

        $strategy = new DefaultSequenceStrategy();
        $this->assertNull($strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_default_strategy_with_column()
    {
        $hint = new SequenceHint();
        $hint->column = 'id';

        $strategy = new DefaultSequenceStrategy();
        $this->assertNull($strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_default_strategy_with_table_and_column()
    {
        $hint = new SequenceHint();
        $hint->table = 'aaa';
        $hint->column = 'id';

        $strategy = new DefaultSequenceStrategy();
        $this->assertEquals('aaa_id_seq', $strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_default_strategy_with_name()
    {
        $hint = new SequenceHint();
        $hint->name = 'seq_aaa';

        $strategy = new DefaultSequenceStrategy();
        $this->assertEquals('seq_aaa', $strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_default_strategy_with_all()
    {
        $hint = new SequenceHint();
        $hint->table = 'aaa';
        $hint->column = 'id';
        $hint->name = 'seq_aaa';

        $strategy = new DefaultSequenceStrategy();
        $this->assertEquals('seq_aaa', $strategy->resolve($hint));
    }

    /**
     * @test
     */
    public function test_select_strategy()
    {
        $mgr = new SequenceNameManager();

        $this->assertInstanceOf(DefaultSequenceStrategy::class, $mgr->findStrategy(null));
        $this->assertInstanceOf(DefaultSequenceStrategy::class, $mgr->findStrategy(''));
        $this->assertInstanceOf(DefaultSequenceStrategy::class, $mgr->findStrategy('unknown_driver'));
        $this->assertInstanceOf(DefaultSequenceStrategy::class, $mgr->findStrategy('oci8'));
        $this->assertInstanceOf(DefaultSequenceStrategy::class, $mgr->findStrategy('pdo_pgsql'));
        $this->assertInstanceOf(SqliteSequenceStrategy::class, $mgr->findStrategy('pdo_sqlite'));
    }
}
