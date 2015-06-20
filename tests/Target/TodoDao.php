<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Delete;
use Omelet\Annotation\Insert;
use Omelet\Annotation\Select;
use Omelet\Annotation\Update;

use Omelet\Annotation\ParamAlt;

/**
 * @Dao
 */
interface TodoDao
{
    /**
     * @Select
     */
    public function listAll();
    /**
     * @Select
     */
    public function listById($id);
    /**
     * @Select
     *
     * @param \DateTime from
     * @param \DateTime to
     */
    public function listByPub(\DateTime $from, \DateTime $to);

    /**
     * @Insert
     *
     * @param string[] fields
     */
    public function insert(array $fields);

    /**
     * @Update
     *
     * @param string[] fields
     */
    public function update(array $fields);

    /**
     * @Delete
     *
     * @param int id
     */
    public function delete($id);
}
