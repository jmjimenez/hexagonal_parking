<?php

namespace Jmj\Parking\Infrastructure\Repository\Pdo;

use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\PdoObjectRepository;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Aggregate\BaseAggregate;
use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Repository\User as DomainUserRepository;

class User extends PdoObjectRepository implements DomainUserRepository
{
    /** @var string  */
    private $tableName = 'User';

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
     * @return DomainUser|null
     * @throws PdoExecuteError
     */
    public function findByUuid(string $uuid): ?DomainUser
    {
        /** @var DomainUser $user */
        $user = parent::findObjectByUuid($uuid);

        return $user;
    }

    /**
     * @inheritdoc
     * @param DomainUser $user
     * @throws \Exception
     */
    public function save(DomainUser $user) : int
    {
        return parent::saveObject($user);
    }

    /**
     * @param DomainUser $user
     * @return int
     * @throws \Exception
     */
    public function delete(DomainUser $user): int
    {
        return parent::deleteObject($user);
    }

    /**
     * @param string $name
     * @return DomainUser|null
     * @throws PdoExecuteError
     */
    public function findByName(string $name): ?DomainUser
    {
        /** @var DomainUser $user */
        $user = $this->findObjectBySql(
            "SELECT * FROM {$this->tableName()} WHERE `name` = :username LIMIT 1",
            [ ':username' => $name ]
        );

        return $user;
    }

    /**
     * @param string $email
     * @return DomainUser|null
     * @throws PdoExecuteError
     */
    public function findByEmail(string $email): ?DomainUser
    {
        /** @var DomainUser $user */
        $user = $this->findObjectBySql(
            "SELECT * FROM {$this->tableName()} WHERE `email` = :email LIMIT 1",
            [ ':email' => $email ]
        );

        return $user;
    }

    protected function fieldsList(): array
    {
        $userFields = [
            '`name` VARCHAR(60) NOT NULL',
            '`email` VARCHAR(60) NOT NULL',
        ];

        return array_merge(parent::fieldsList(), $userFields);
    }

    protected function indexesList() : array
    {
        $userIndexes = [
            'UNIQUE `name`',
            'UNIQUE `email`',
        ];

        return array_merge(parent::indexesList(), $userIndexes);
    }

    protected function insertParams(BaseAggregate $object): array
    {
        /** @var DomainUser $user */
        $user = $object;

        $userParams = [
            ':username' => $user->name(),
            ':email' => $user->email(),
        ];

        return parent::insertParams($object) + $userParams;
    }

    protected function insertSql() : string
    {
        return
            "INSERT INTO {$this->tableName()} 
                (`version`, `object`, `class`, `uuid`, `name`, `email`)
            VALUES
                (:version, :object, :class, :uuid, :username, :email)";
    }

    protected function updateParams(BaseAggregate $object)
    {
        /** @var DomainUser $user */
        $user = $object;

        $userParams = [
            ':username' => $user->name(),
            ':email' => $user->email(),
        ];

        return parent::updateParams($object) + $userParams;
    }

    protected function updateSql() : string
    {
        return
            "UPDATE {$this->tableName()} SET
                `version` = `version` + 1, 
                `object` = :object,
                `name` = :username,
                `email` = :email
            WHERE `uuid` = :uuid
            AND `version` = :version";
    }

    /**
     * @return string
     */
    protected function tableName() : string
    {
        return $this->tableName;
    }
}
