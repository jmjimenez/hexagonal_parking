<?php

namespace Jmj\Parking\Infrastructure\Psx\Dependency;

use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Infrastructure\Repository\Pdo\User as PdoUserRepository;
use Jmj\Parking\Infrastructure\Repository\Pdo\Parking as PdoParkingRepository;
use PSX\Framework\Dependency\DefaultContainer;

class Container extends DefaultContainer
{
    /**
     * @return PdoUserRepository
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getUserRepository()
    {
        static $repository = null;

        if ($repository !== null) {
            return $repository;
        }

        return $repository = new PdoUserRepository('users', $this->getPdoProxy());
    }

    /**
     * @return PdoParkingRepository
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getParkingRepository()
    {
        static $repository = null;

        if ($repository !== null) {
            return $repository;
        }

        return $repository = new PdoParkingRepository('parkings', $this->getPdoProxy());
    }

    /**
     * @return PdoProxy
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getPdoProxy()
    {
        static $pdoProxy = null;

        if ($pdoProxy !== null) {
            return $pdoProxy;
        }

        $parkingDbConf = $this->getConfig()->get('parking_db_conf');

        $pdoProxy = new PdoProxy();
        $pdoProxy->connectToMysql(
            $parkingDbConf['host'],
            $parkingDbConf['user'],
            $parkingDbConf['password'],
            $parkingDbConf['dbname']
        );

        return $pdoProxy;
    }
}
