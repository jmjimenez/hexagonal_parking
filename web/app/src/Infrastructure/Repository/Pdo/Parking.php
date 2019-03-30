<?php

namespace Jmj\Parking\Infrastructure\Repository\Pdo;

use Exception;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\PdoObjectRepository;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;
use Jmj\Parking\Domain\Repository\Parking as DomainParkingRepository;

class Parking extends PdoObjectRepository implements DomainParkingRepository
{
    /** @var string  */
    private $tableName = 'Parking';

    /**
     * @param string $tableName
     * @param PdoProxy $pdoProxy
     */
    public function __construct(string $tableName, PdoProxy $pdoProxy)
    {
        $this->tableName = $tableName;

        parent::__construct($pdoProxy);
    }

    /**
     * @param string $uuid
     * @return DomainParking|null
     * @throws PdoExecuteError
     */
    public function findByUuid(string $uuid): ?DomainParking
    {
        /** @var DomainParking $parking */
        $parking = parent::findObjectByUuid($uuid);

        return $parking;
    }

    /**
     * {@inheritdoc}
     * @param DomainParking $parking
     * @throws Exception
     */
    public function save(DomainParking $parking) : int
    {
        return parent::saveObject($parking);
    }

    /**
     * @param DomainParking $parking
     * @return int
     * @throws Exception
     */
    public function delete(DomainParking $parking): int
    {
        return parent::deleteObject($parking);
    }

    /**
     * @return string
     */
    protected function tableName() : string
    {
        return $this->tableName;
    }
}
