<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Aggregate\BaseAggregate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking as InMemoryParking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\ParkingSlot as InMemoryParkingSlot;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User as InMemoryUser;
use Jmj\Parking\Infrastructure\Repository\Pdo\Parking as ParkingPdoRepository;
use Jmj\Parking\Infrastructure\Repository\Pdo\User as UserPdoRepository;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;

trait DataSamplesGenerator
{
    /** @var ParkingPdoRepository */
    protected $parkingRepository;

    /** @var UserPdoRepository */
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
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws \Exception
     */
    protected function createTestCase()
    {
        $pdoProxy = new PdoProxy();
        $pdoProxy->connectToSqlite(':memory:');

        $this->parkingRepository = new ParkingPdoRepository('Parking', $pdoProxy);
        $this->parkingRepository->initializeRepository();

        $this->userRepository = new UserPdoRepository('User', $pdoProxy);
        $this->userRepository->initializeRepository();

        $this->userAdmin = new InMemoryUser('Admin', 'admin@test.com', 'adminpasswd', true);
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
     *
     */
    private function configureDomainEventsBroker()
    {
        $domainEventBroker = SynchronousEventsBroker::getInstance();
        BaseAggregate::setDomainEventBroker($domainEventBroker);
        $domainEventBroker->resetSubscriptions();
    }
}