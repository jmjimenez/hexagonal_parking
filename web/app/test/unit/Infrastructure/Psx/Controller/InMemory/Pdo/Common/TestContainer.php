<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Infrastructure\Psx\Dependency\Container;

class TestContainer extends Container
{
    /**
     * @return PdoProxy
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getPdoProxy() : PdoProxy
    {
        $pdoProxy = new PdoProxy();
        $pdoProxy->connectToSqlite(':memory:');

        return $pdoProxy;
    }
}
