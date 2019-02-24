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
use Jmj\Parking\Domain\Service\Command\DeleteParking;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class DeleteParkingTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;

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

        $parkingUuid = $this->parking->uuid();

        $user = $this->createUser('administrator', true);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new DeleteParking($this->parkingRepository);
        $command->execute($user, $this->parking);

        $this->assertEquals(
            [
                ParkingSlot::EVENT_PARKING_SLOT_DELETED,
                ParkingSlot::EVENT_PARKING_SLOT_DELETED,
                Parking::EVENT_PARKING_DELETED
            ],
            $this->recordedEventNames
        );

        $parking = $this->parkingRepository->findByUuid($parkingUuid);

        $this->assertNull($parking);
    }
}

