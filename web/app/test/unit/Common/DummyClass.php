<?php

namespace Jmj\Test\Unit\Common;

class DummyClass
{
    /** @var string */
    protected $name;

    /** @var DummyClass[] */
    protected $children = [];

    /**
     * DummyClass constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param DummyClass $child
     */
    public function addChild(DummyClass $child)
    {
        $this->children[] = $child;
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * @return DummyClass[]
     */
    public function children()
    {
        return $this->children;
    }
}
