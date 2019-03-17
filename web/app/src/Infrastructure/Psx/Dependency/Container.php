<?php

namespace Jmj\Parking\Infrastructure\Psx\Dependency;

use Jmj\Parking\Application\Command\Handler\AssignParkingSlotToUserForPeriod;
use Jmj\Parking\Application\Command\Handler\CreateUserForParking;
use Jmj\Parking\Application\Command\Handler\DeleteParkingSlot;
use Jmj\Parking\Application\Command\Handler\CreateParkingSlot;
use Jmj\Parking\Application\Command\Handler\AssignAdministratorRightsToUserForParking;
use Jmj\Parking\Application\Command\Handler\AssignUserToParking;
use Jmj\Parking\Application\Command\Handler\DeassignUserFromParking;
use Jmj\Parking\Application\Command\Handler\CreateParking;
use Jmj\Parking\Application\Command\Handler\DeleteParking;
use Jmj\Parking\Application\Command\Handler\FreeAssignedParkingSlotForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\GetParkingReservationsForDate;
use Jmj\Parking\Application\Command\Handler\GetUserInformation;
use Jmj\Parking\Application\Command\Handler\RemoveAssignmentFromParkingSlotForUserAndDate;
use Jmj\Parking\Application\Command\Handler\RequestResetUserPassword;
use Jmj\Parking\Application\Command\Handler\ReserveParkingSlotForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\UpdateParkingSlotInformation;
use Jmj\Parking\Application\Command\Handler\GetParkingInformationForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\GetParkingSlotReservationsForPeriod;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Infrastructure\Repository\Pdo\User as PdoUserRepository;
use Jmj\Parking\Infrastructure\Repository\Pdo\Parking as PdoParkingRepository;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\Parking as ParkingFactory;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\User as UserFactory;
use PSX\Framework\Dependency\DefaultContainer;

class Container extends DefaultContainer
{
    //TODO: this class can be refactored to read dependencies from a conf file

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
     * @return DeassignUserFromParking
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getDeassignUserFromParkingCommandHandler() : DeassignUserFromParking
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new DeassignUserFromParking(
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
     * @return AssignParkingSlotToUserForPeriod
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getAssignParkingSlotToUserForPeriodCommandHandler() : AssignParkingSlotToUserForPeriod
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new AssignParkingSlotToUserForPeriod(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return FreeAssignedParkingSlotForUserAndPeriod
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getFreeAssignedParkingSlotForUserAndPeriodCommandHandler() : FreeAssignedParkingSlotForUserAndPeriod
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new FreeAssignedParkingSlotForUserAndPeriod(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return GetParkingInformationForUserAndPeriod
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getGetParkingInformationForUserAndPeriodCommandHandler() : GetParkingInformationForUserAndPeriod
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new GetParkingInformationForUserAndPeriod(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return GetParkingSlotReservationsForPeriod
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getGetParkingSlotReservationsForPeriodCommandHandler() : GetParkingSlotReservationsForPeriod
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new GetParkingSlotReservationsForPeriod(
            $this->getUserRepository(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return GetParkingReservationsForDate
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getGetParkingReservationsForDateCommandHandler() : GetParkingReservationsForDate
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new GetParkingReservationsForDate(
            $this->getUserRepository(),
            $this->getParkingRepository()
        );

        return $command;
    }

    /**
     * @return RemoveAssignmentFromParkingSlotForUserAndDate
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getRemoveAssignmentFromParkingSlotFromUserAndDateCommandHandler()
    : RemoveAssignmentFromParkingSlotForUserAndDate
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new RemoveAssignmentFromParkingSlotForUserAndDate(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return GetUserInformation
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getGetUserInformationCommandHandler() : GetUserInformation
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new GetUserInformation(
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return ReserveParkingSlotForUserAndPeriod
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getReserveParkingSlotForUserAndPeriodCommandHandler() : ReserveParkingSlotForUserAndPeriod
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new ReserveParkingSlotForUserAndPeriod(
            $this->getParkingRepository(),
            $this->getUserRepository()
        );

        return $command;
    }

    /**
     * @return RequestResetUserPassword
     * @throws \Jmj\Parking\Common\Exception\PdoConnectionError
     */
    public function getRequestResetUserPasswordCommandHandler() : RequestResetUserPassword
    {
        static $command = null;

        if ($command !== null) {
            return $command;
        }

        $command = new RequestResetUserPassword(
            $this->getUserRepository()
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
