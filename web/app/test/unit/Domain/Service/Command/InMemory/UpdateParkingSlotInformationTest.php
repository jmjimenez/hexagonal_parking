<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\UpdateParkingSlotInformation;
use Jmj\Parking\Common\EventsRecorder;
use PHPUnit\Framework\TestCase;

class UpdateParkingSlotInformationTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ParkingException
     * @throws \Exception
     */
    public function testExecute()
    {
        $number = '3';
        $description = 'Parking Slot 3';

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new UpdateParkingSlotInformation();
        $command->execute(
            $this->loggedInUser,
            $this->parking,
            $this->parkingSlotOne->uuid(),
            $number,
            $description
        );

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_INFORMATION_UPDATED ], $this->recordedEventNames);

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlot = $parking->getParkingSlotByUuid($this->parkingSlotOne->uuid());

        $this->assertEquals($number, $parkingSlot->number());
        $this->assertEquals($description, $parkingSlot->description());
    }
}
