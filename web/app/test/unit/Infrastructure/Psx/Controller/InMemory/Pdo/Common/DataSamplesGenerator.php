<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking as InMemoryParking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\ParkingSlot as InMemoryParkingSlot;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User as InMemoryUser;

trait DataSamplesGenerator
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var InMemoryParking */
    protected $parking;

    /** @var InMemoryParkingSlot */
    protected $parkingSlotOne;

    /** @var InMemoryUser */
    protected $userOne;

    /** @var InMemoryUser */
    protected $userAdmin;

    /**
     * @param PdoProxy $pdoProxy
     * @param UserRepository | \Jmj\Parking\Common\Pdo\PdoObjectRepository $userRepository
     * @param ParkingRepository | \Jmj\Parking\Common\Pdo\PdoObjectRepository $parkingRepository
     * @throws \Jmj\Parking\Common\Exception\PdoExecuteError
     * @throws \Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid
     * @throws \Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserEmailInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserNameAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserNameInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserPasswordInvalid
     */
    protected function createTestCase(
        PdoProxy $pdoProxy,
        UserRepository $userRepository,
        ParkingRepository $parkingRepository
    ) {
        $this->configureSqlEventsBroker($pdoProxy);

        $this->parkingRepository = $parkingRepository;
        $this->parkingRepository->initializeRepository();

        $this->userRepository = $userRepository;
        $this->userRepository->initializeRepository();

        $this->userAdmin = new InMemoryUser('Admin', 'admin@test.com', $this->getUserPassword(), true);
        $this->userRepository->save($this->userAdmin);

        $this->userOne = new InMemoryUser('User One', 'userone@test.com', 'userpasswd', false);
        $this->userRepository->save($this->userOne);

        $this->parking = new InMemoryParking('parking');
        $this->parking->addUser($this->userAdmin, true);
        $this->parking->addUser($this->userOne);
        $this->parkingSlotOne = $this->parking->createParkingSlot('1', 'Parking Slot 1');
        $this->parkingRepository->save($this->parking);
    }

    /**
     * @param PdoProxy $pdoProxy
     */
    protected function configureSqlEventsBroker(PdoProxy $pdoProxy)
    {
        $eventsBroker = SynchronousEventsBroker::getInstance();
        $pdoProxy->setEventsBroker($eventsBroker);
    }

    protected function getUserPassword()
    {
        return 'adminpasswd';
    }
}
