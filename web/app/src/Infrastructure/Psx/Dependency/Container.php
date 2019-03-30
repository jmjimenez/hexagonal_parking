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
use Jmj\Parking\Application\Command\Handler\ResetUserPassword;
use Jmj\Parking\Application\Command\Handler\UpdateParkingSlotInformation;
use Jmj\Parking\Application\Command\Handler\GetParkingInformationForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\GetParkingSlotReservationsForPeriod;
use Jmj\Parking\Application\Command\Handler\UserLogin;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Infrastructure\Repository\Pdo\User as PdoUserRepository;
use Jmj\Parking\Infrastructure\Repository\Pdo\Parking as PdoParkingRepository;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\Parking as ParkingFactory;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\User as UserFactory;
use PSX\Framework\Dependency\DefaultContainer;

class Container extends DefaultContainer
{
    //TODO: this class can be refactored to read dependencies from a conf file
    //TODO: I might fill the parent::factories array with all these methods

    /**
     * @return PdoUserRepository
     */
    public function getUserRepository() : PdoUserRepository
    {
        return new PdoUserRepository('users', $this->get('PdoProxy'));
    }

    /**
     * @return PdoParkingRepository
     */
    public function getParkingRepository() : PdoParkingRepository
    {
        return new PdoParkingRepository('parkings', $this->get('PdoProxy'));
    }

    /**
     * @return PdoProxy
     * @throws PdoConnectionError
     */
    public function getPdoProxy() : PdoProxy
    {
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
     */
    public function getAssignAdministratorRightsToUserForParkingCommandHandler()
        : AssignAdministratorRightsToUserForParking
    {
        return new AssignAdministratorRightsToUserForParking(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return AssignUserToParking
     */
    public function getAssignUserToParkingCommandHandler() : AssignUserToParking
    {
        return new AssignUserToParking(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return DeassignUserFromParking
     */
    public function getDeassignUserFromParkingCommandHandler() : DeassignUserFromParking
    {
        return new DeassignUserFromParking(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return CreateParking
     */
    public function getCreateParkingCommandHandler() : CreateParking
    {
        return new CreateParking(
            $this->get('UserRepository'),
            $this->get('ParkingFactory'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return CreateParkingSlot
     */
    public function getCreateParkingSlotCommandHandler() : CreateParkingSlot
    {
        return new CreateParkingSlot(
            $this->get('UserRepository'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return DeleteParking
     */
    public function getDeleteParkingCommandHandler() : DeleteParking
    {
        return new DeleteParking(
            $this->get('UserRepository'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return DeleteParkingSlot
     */
    public function getDeleteParkingSlotCommandHandler() : DeleteParkingSlot
    {
        return new DeleteParkingSlot(
            $this->get('UserRepository'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return CreateUserForParking
     */
    public function getCreateUserForParkingCommandHandler() : CreateUserForParking
    {
        return new CreateUserForParking(
            $this->get('UserRepository'),
            $this->getUserFactory(),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return UpdateParkingSlotInformation
     */
    public function getUpdateParkingSlotInformationCommandHandler() : UpdateParkingSlotInformation
    {
        return new UpdateParkingSlotInformation(
            $this->get('UserRepository'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return AssignParkingSlotToUserForPeriod
     */
    public function getAssignParkingSlotToUserForPeriodCommandHandler() : AssignParkingSlotToUserForPeriod
    {
        return new AssignParkingSlotToUserForPeriod(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return FreeAssignedParkingSlotForUserAndPeriod
     */
    public function getFreeAssignedParkingSlotForUserAndPeriodCommandHandler() : FreeAssignedParkingSlotForUserAndPeriod
    {
        return new FreeAssignedParkingSlotForUserAndPeriod(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return GetParkingInformationForUserAndPeriod
     */
    public function getGetParkingInformationForUserAndPeriodCommandHandler() : GetParkingInformationForUserAndPeriod
    {
        return new GetParkingInformationForUserAndPeriod(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return GetParkingSlotReservationsForPeriod
     */
    public function getGetParkingSlotReservationsForPeriodCommandHandler() : GetParkingSlotReservationsForPeriod
    {
        return new GetParkingSlotReservationsForPeriod(
            $this->get('UserRepository'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return GetParkingReservationsForDate
     */
    public function getGetParkingReservationsForDateCommandHandler() : GetParkingReservationsForDate
    {
        return new GetParkingReservationsForDate(
            $this->get('UserRepository'),
            $this->get('ParkingRepository')
        );
    }

    /**
     * @return RemoveAssignmentFromParkingSlotForUserAndDate
     */
    public function getRemoveAssignmentFromParkingSlotFromUserAndDateCommandHandler()
    : RemoveAssignmentFromParkingSlotForUserAndDate
    {
        return new RemoveAssignmentFromParkingSlotForUserAndDate(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return GetUserInformation
     */
    public function getGetUserInformationCommandHandler() : GetUserInformation
    {
        return new GetUserInformation(
            $this->get('UserRepository')
        );
    }

    /**
     * @return ReserveParkingSlotForUserAndPeriod
     */
    public function getReserveParkingSlotForUserAndPeriodCommandHandler() : ReserveParkingSlotForUserAndPeriod
    {
        return new ReserveParkingSlotForUserAndPeriod(
            $this->get('ParkingRepository'),
            $this->get('UserRepository')
        );
    }

    /**
     * @return RequestResetUserPassword
     */
    public function getRequestResetUserPasswordCommandHandler() : RequestResetUserPassword
    {
        return new RequestResetUserPassword(
            $this->get('UserRepository')
        );
    }

    /**
     * @return ResetUserPassword
     */
    public function getResetUserPasswordCommandHandler() : ResetUserPassword
    {
        return new ResetUserPassword(
            $this->get('UserRepository')
        );
    }

    public function getUserLoginCommandHandler(): UserLogin
    {
        $jwtConfig = $this->getConfig()->get('parking_jwt');

        return new UserLogin(
            $this->get('UserRepository'),
            $jwtConfig['secret'],
            $jwtConfig['algorithm']
        );
    }

    /**
     * @return ParkingFactory
     */
    public function getParkingFactory() : ParkingFactory
    {
        return new ParkingFactory();
    }

    /**
     * @return UserFactory
     */
    public function getUserFactory() : UserFactory
    {
        return new UserFactory();
    }
}
