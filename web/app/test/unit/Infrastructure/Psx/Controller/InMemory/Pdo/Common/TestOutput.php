<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

class TestOutput
{
    /**
     * @var string
     */
    protected $output = null;

    public function setOutput(string $output)
    {
        $this->output = $output;
    }

    public function output(): string
    {
        return $this->output === null ? '[none output]' : $this->output;
    }
}
