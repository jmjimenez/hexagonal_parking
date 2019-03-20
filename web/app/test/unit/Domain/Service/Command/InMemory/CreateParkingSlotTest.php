<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\CreateParkingSlot;
use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class CreateParkingSlotTest extends TestCase
{
    use DataSamplesGenerator;
    use DomainEventsRegister;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testExecuteWhenUserIsAdministrator()
    {
        $number = '101';
        $description = 'Parking 101';

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new CreateParkingSlot();
        $parkingSlot = $command->execute($this->loggedInUser, $this->parking, $number, $description);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_CREATED, Parking::EVENT_PARKING_SLOT_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFromRepository = $parking->getParkingSlotByNumber($number);

        $this->assertInstanceOf(ParkingSlot::class, $parkingSlotFromRepository);
        $this->assertEquals($parkingSlot->uuid(), $parkingSlotFromRepository->uuid());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testExecuteWhenUserIsParkingAdministrator()
    {
        $number = '101';
        $description = 'Parking 101';

        $this->createTestCase();

        $user = new User('newUser', 'newuser@test.com', 'password', false);
        $this->parking->addUser($user, true);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new CreateParkingSlot();
        $parkingSlot = $command->execute($user, $this->parking, $number, $description);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_CREATED, Parking::EVENT_PARKING_SLOT_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFromRepository = $parking->getParkingSlotByNumber($number);

        $this->assertInstanceOf(ParkingSlot::class, $parkingSlotFromRepository);
        $this->assertEquals($parkingSlot->uuid(), $parkingSlotFromRepository->uuid());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     *
     * @expectedException \Jmj\Parking\Domain\Exception\ParkingException
     */
    public function testExecuteErrorWhenUserIsNotAdministrator()
    {
        $parkingSlotNumber = '101';
        $parkingSlotDescription = 'Parking Slot 101';

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new CreateParkingSlot();
        $command->execute($this->userOne, $this->parking, $parkingSlotNumber, $parkingSlotDescription);
    }
}
