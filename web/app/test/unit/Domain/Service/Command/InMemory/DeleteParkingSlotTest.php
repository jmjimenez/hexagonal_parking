<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\DeleteParkingSlot;
use Jmj\Parking\Common\EventsRecorder;
use PHPUnit\Framework\TestCase;

class DeleteParkingSlotTest extends TestCase
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
     */
    public function testExecute()
    {
        $this->createTestCase();
        $parkingSlotUuid = $this->parkingSlotOne->uuid();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new DeleteParkingSlot();
        $command->execute($this->loggedInUser, $this->parking, $parkingSlotUuid);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_DELETED, Parking::EVENT_PARKING_SLOT_DELETED_FROM_PARKING ],
            $this->recordedEventNames
        );

        $parkingSlot = $this->parking->getParkingSlotByUuid($parkingSlotUuid);
        $this->assertNull($parkingSlot);
    }
}
