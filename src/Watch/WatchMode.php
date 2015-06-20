<?php

namespace Omelet\Watch;

final class WatchMode
{
    /**
     * Always building dao implementation
     */
    public static function Always()
    {
        return self::getInstance(1);
    }

    /**
     * build dao implementation whenever sql/interface is changed
     */
    public static function Whenever()
    {
        return self::getInstance(2);
    }

    /**
     * Once building dao implementation
     */
    public static function Once()
    {
        return self::getInstance(3);
    }

    private static function getInstance($id)
    {
        if (! isset(self::$enum[$id])) {
            self::$enum[$id] = new WatchMode($id);
        }

        return self::$enum[$id];
    }

    private static $enum = [];

    /**
     * @var int
     */
    private $mode;

    private function __construct($mode)
    {
        $this->mode = $mode;
    }
}
