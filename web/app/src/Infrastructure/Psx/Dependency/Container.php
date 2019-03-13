<?php

namespace Jmj\Parking\Infrastructure\Psx\Dependency;

use Jmj\Parking\Application\Command\Handler\CreateUserForParking;
use Jmj\Parking\Application\Command\Handler\DeleteParkingSlot;
use Jmj\Parking\Application\Command\Handler\CreateParkingSlot;
use Jmj\Parking\Application\Command\Handler\AssignAdministratorRightsToUserForParking;
use Jmj\Parking\Application\Command\Handler\AssignUserToParking;
use Jmj\Parking\Application\Command\Handler\CreateParking;
use Jmj\Parking\Application\Command\Handler\DeleteParking;
use Jmj\Parking\Application\Command\Handler\UpdateParkingSlotInformation;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Infrastructure\Repository\Pdo\User as PdoUserRepository;
use Jmj\Parking\Infrastructure\Repository\Pdo\Parking as PdoParkingRepository;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\Parking as ParkingFactory;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\User as UserFactory;
use PSX\Framework\Dependency\DefaultContainer;

class Container extends DefaultContainer
{
    /**
     * @return PdoUserRepository
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getUserRepository() : PdoUserRepository
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
    public function getParkingRepository() : PdoParkingRepository
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
    public function getPdoProxy() : PdoProxy
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

    /**
     * @return AssignAdministratorRightsToUserForParking
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getAssignAdministratorRightsToUserForParkingCommandHandler()
        : AssignAdministratorRightsToUserForParking
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new AssignAdministratorRightsToUserForParking(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return AssignUserToParking
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getAssignUserToParkingCommandHandler() : AssignUserToParking
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new AssignUserToParking(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return CreateParking
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getCreateParkingCommandHandler() : CreateParking
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new CreateParking(
            $this->getUserRepository(),
            $this->getParkingFactory(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return CreateParkingSlot
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getCreateParkingSlotCommandHandler() : CreateParkingSlot
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new CreateParkingSlot(
            $this->getUserRepository(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return DeleteParking
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getDeleteParkingCommandHandler() : DeleteParking
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new DeleteParking(
            $this->getUserRepository(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return DeleteParkingSlot
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getDeleteParkingSlotCommandHandler() : DeleteParkingSlot
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new DeleteParkingSlot(
            $this->getUserRepository(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return CreateUserForParking
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getCreateUserForParkingCommandHandler() : CreateUserForParking
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new CreateUserForParking(
            $this->getUserRepository(),
            $this->getUserFactory(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return UpdateParkingSlotInformation
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getUpdateParkingSlotInformationCommandHandler() : UpdateParkingSlotInformation
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new UpdateParkingSlotInformation(
            $this->getUserRepository(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return ParkingFactory
     */
    public function getParkingFactory() : ParkingFactory
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new ParkingFactory();

        return $command;
    }

    /**
     * @return UserFactory
     */
    public function getUserFactory() : UserFactory
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new UserFactory();

        return $command;
    }
}
