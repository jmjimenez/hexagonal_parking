<?php

namespace Jmj\Parking\Common\Pdo;

use Exception;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\Exception\DifferentObjectVersions;
use Jmj\Parking\Domain\Aggregate\Common\BaseAggregate;
use Jmj\Parking\Common\Pdo\Exception\ObjectNotFound;
use Jmj\Parking\Common\Pdo\Exception\UpdateError;

abstract class PdoObjectRepository
{
    /** @var int[]  */
    protected $versions = [];

    /** @var PdoProxy  */
    protected $pdoProxy;

    abstract protected function tableName() : string;

    /**
     * @param PdoProxy $pdo
     */
    public function __construct(PdoProxy $pdo)
    {
        $this->pdoProxy = $pdo;
    }

    /**
     * @throws PdoExecuteError
     */
    public function initializeRepository()
    {
        $this->pdoProxy->createTable($this->tableName(), $this->fieldsList() + $this->indexesList());
    }

    protected function fieldsList() : array
    {
        return [
            '`uuid` VARCHAR(30) NOT NULL',
            '`object` TEXT  NOT NULL',
            '`class` VARCHAR(60) NOT NULL',
            '`version` INT  NOT NULL',
        ];
    }

    protected function indexesList() : array
    {
        return [
            'PRIMARY KEY (`uuid`)',
        ];
    }

    /**
     * @param BaseAggregate $object
     * @return int
     * @throws Exception
     */
    public function saveObject(BaseAggregate $object): int
    {
        $this->pdoProxy->startTransaction();

        try {
            $record = $this->findRecordByUuid($object->uuid());

            if (!$record) {
                $records = $this->insertObject($object);
            } else {
                $records = $this->updateObject($object);
            }

            if ($records === 0) {
                throw new UpdateError();
            }
        } catch (Exception $e) {
            $this->pdoProxy->rollbackTransaction();
            throw $e;
        }

        $this->pdoProxy->commitTransaction();

        return $records;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function deleteObject(BaseAggregate $object) : int
    {
        $this->pdoProxy->startTransaction();

        try {
            $record = $this->findRecordByUuid($object->uuid());

            if (count($record) == 0) {
                throw new ObjectNotFound();
            }

            $params = [
                ':uuid' => $object->uuid(),
                ':version' => $this->versions[$object->uuid()]
            ];

            $records = $this->pdoProxy->execute(
                "DELETE FROM {$this->tableName()} WHERE `uuid` = :uuid AND `version` = :version",
                $params
            );
            if ($records === 0) {
                throw new UpdateError();
            }
        } catch (Exception $e) {
            $this->pdoProxy->rollbackTransaction();
            throw $e;
        }

        $this->pdoProxy->commitTransaction();

        return $records;
    }

    /**
     * @param string $uuid
     * @return BaseAggregate|null
     * @throws PdoExecuteError
     */
    public function findObjectByUuid(string $uuid): ?BaseAggregate
    {
        $record = $this->findRecordByUuid($uuid);

        if ($record == false) {
            return null;
        }

        $object = unserialize($record['object']);

        $this->versions[$object->uuid()] = $record['version'];

        return $object;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return BaseAggregate|null
     * @throws PdoExecuteError
     */
    public function findObjectBySql(string $sql, array $params): ?BaseAggregate
    {
        $record = $this->pdoProxy->fetchOne($sql, $params);

        if ($record == false) {
            return null;
        }

        $object = unserialize($record['object']);

        $this->versions[$object->uuid()] = $record['version'];

        return $object;
    }

    /**
     * @param BaseAggregate $object
     * @return int
     * @throws Exception
     */
    protected function insertObject(BaseAggregate $object)
    {
        $rowCount = $this->pdoProxy->execute(
            $this->insertSql(),
            $this->insertParams($object)
        );

        if ($rowCount != 0) {
            $this->versions[$object->uuid()] = 1;
        }

        return $rowCount;
    }

    protected function insertParams(BaseAggregate $object): array
    {
        return [
            ':object' => serialize($object),
            ':class' => get_class($object),
            ':uuid' => $object->uuid(),
            ':version' => 1,
        ];
    }

    protected function insertSql() : string
    {
        return
            "INSERT INTO {$this->tableName()} 
                (`version`, `object`, `class`, `uuid`)
            VALUES
                (:version, :object, :class, :uuid)";
    }

    /**
     * @param BaseAggregate $object
     * @return int
     * @throws Exception
     */
    protected function updateObject(BaseAggregate $object) : int
    {
        if (isset($this->versions[$object->uuid()])) {
            $this->checkVersion($object->uuid());
        }

        $rowCount = $this->pdoProxy->execute($this->updateSql(), $this->updateParams($object));

        if ($rowCount != 0) {
            $this->versions[$object->uuid()]++;
        }

        return $rowCount;
    }

    protected function updateParams(BaseAggregate $object)
    {
        return [
            ':object' => serialize($object),
            ':uuid' => $object->uuid(),
            ':version' => $this->versions[$object->uuid()],
        ];
    }

    protected function updateSql() : string
    {
        return
            "UPDATE {$this->tableName()} SET
                `version` = `version` + 1, 
                `object` = :object
            WHERE `uuid` = :uuid
            AND `version` = :version";
    }

    /**
     * @param string $uuid
     * @return array|bool
     * @throws PdoExecuteError
     */
    protected function findRecordByUuid(string $uuid)
    {
        return $this->pdoProxy->fetchOne(
            "SELECT * FROM `{$this->tableName()}` WHERE `uuid` = :uuid",
            [ ':uuid' => $uuid ]
        );
    }

    /**
     * @param string $uuid
     * @throws DifferentObjectVersions
     * @throws PdoExecuteError
     */
    protected function checkVersion(string $uuid)
    {
        $record = $this->findRecordByUuid($uuid);

        if ($record['version'] != $this->versions[$uuid]) {
            throw new DifferentObjectVersions();
        }
    }
}
