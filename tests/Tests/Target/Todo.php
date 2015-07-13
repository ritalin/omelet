<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Column;
use Omelet\Annotation\Alias;
use Omelet\Annotation\Entity;

class Todo
{
    /**
     * @Column(name="todo_id")
     *
     * @var integer
     */
    public $id;

    /**
     * @Column(name="content")
     * @Alias(name="content", alias={"text", "memo"})
     *
     * @var string
     */
    public $todo;

    /**
     * @var \DateTime
     */
    public $created;

    /**
     * @Column(default="0")
     *
     * @var Hidden
     */
    public $hidden;

    /**
     * @Column(name="creator_id", optFields={"creator_name"})
     * @Alias(name="creator_id", alias="maintener_id")
     * @Alias(name="creator_name", alias="maintener_name")
     *
     * @var Editor
     */
    public $creator;

    /**
     * @param calable(Todo -> Void) fn
     */
    public function __construct(callable $fn = null)
    {
        if ($fn !== null) {
            $fn($this);
        }
    }

    public static function __set_state($values)
    {
        return new Todo(function ($obj) use ($values) {
            $obj->id = $values['id'];
            $obj->todo = $values['todo'];
            $obj->created = $values['created'];
            $obj->hidden = $values['hidden'];
        });
    }
}
