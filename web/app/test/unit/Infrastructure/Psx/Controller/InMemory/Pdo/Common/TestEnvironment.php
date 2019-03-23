<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use PSX\Framework\Environment\Environment;

class TestEnvironment extends Environment
{
    /**
     * {@inheritdoc}
     */
    public function serve()
    {
        $dispatch = $this->container->get('dispatch');
        $config   = $this->container->get('config');

        TestBootstrap::setupEnvironment($config);

        return $this->engine->serve($dispatch, $config);
    }
}
