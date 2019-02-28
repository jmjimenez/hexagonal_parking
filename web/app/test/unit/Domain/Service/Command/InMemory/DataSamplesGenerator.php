<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Domain\Aggregate\BaseAggregate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;
use Jmj\Parking\Domain\Repository\User as UserRepositoryInterface;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\ParkingSlot;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use Jmj\Parking\Infrastructure\Repository\InMemory\Parking as InMemoryParkingRepository;
use Jmj\Parking\Infrastructure\Repository\InMemory\User as InMemoryUserRepository;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;

trait DataSamplesGenerator
{
    /** @var User */
    private $userOne;

    /** @var User */
    private $userTwo;

    /** @var User */
    private $loggedInUser;

    /** @var Parking */
    private $parking;

    /** @var ParkingSlot */
    private $parkingSlotOne;

    /** @var ParkingSlot */
    private $parkingSlotTwo;

    /** @var ParkingRepositoryInterface */
    private $parkingRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ParkingSlotNumberAlreadyExists
     */
    private function createTestCase()
    {
        $this->parkingRepository = new InMemoryParkingRepository();
        $this->userRepository = new InMemoryUserRepository();

        $this->parking = $this->createParking('Parking Test');

        $this->loggedInUser = $this->createUser('useradministrator', true);
        $this->userOne = $this->createUser('userone', false);
        $this->userTwo = $this->createUser('usertwo', false);

        $this->parking->addUser($this->loggedInUser, true);
        $this->parking->addUser($this->userOne, false);
        $this->parking->addUser($this->userTwo, false);

        $this->parkingSlotOne = $this->parking->createParkingSlot('1', 'Parking Slot 1');
        $this->parkingSlotTwo = $this->parking->createParkingSlot('2', 'Parking Slot 2');
    }

    /**
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @param bool $exclusive
     * @throws InvalidDateRange
     */
    private function assignParkingSlotOneToUserOne(
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate,
        bool $exclusive
    ) {
        $this->parkingSlotOne->assignToUserForPeriod($this->userOne, $fromDate, $toDate, $exclusive);
    }

    /**
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @throws \Exception
     */
    private function freeParkingSlot(DateTimeImmutable $fromDate, DateTimeImmutable $toDate)
    {
        $this->parkingSlotOne->markAsFreeFromUserAndPeriod($this->userOne, $fromDate, $toDate);
    }

    /**
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @throws \Exception
     */
    private function reserveParkingSlotOneForUserOne(DateTimeImmutable $fromDate, DateTimeImmutable $toDate)
    {
        $this->parkingSlotOne->reserveToUserForPeriod($this->userOne, $fromDate, $toDate);
    }

    /**
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @throws \Exception
     */
    private function reserveParkingSlotTwoForUserTwo(DateTimeImmutable $fromDate, DateTimeImmutable $toDate)
    {
        $this->parkingSlotTwo->reserveToUserForPeriod($this->userTwo, $fromDate, $toDate);
    }

    /**
     *
     */
    private function configureDomainEventsBroker()
    {
        $domainEventBroker = SynchronousEventsBroker::getInstance();
        BaseAggregate::setDomainEventBroker($domainEventBroker);
        $domainEventBroker->resetSubscriptions();
    }

    /**
     * @param string $userName
     * @param bool $isAdministrator
     * @return User
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    private function createUser(string $userName, bool $isAdministrator) : User
    {
        $userEmail = sprintf('%s@test.com', $userName);
        $password = sprintf('%spassword', $userName);

        $user = new User($userName, $userEmail, $password, $isAdministrator);
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @param string $description
     * @return Parking
     * @throws ExceptionGeneratingUuid
     */
    private function createParking(string $description): Parking
    {
        $parking = new Parking($description);
        $this->parkingRepository->save($parking);

        return $parking;
    }
}
