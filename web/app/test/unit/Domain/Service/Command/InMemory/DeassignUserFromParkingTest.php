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
use Jmj\Parking\Domain\Service\Command\DeassignUserFromParking;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class DeassignUserFromParkingTest extends TestCase
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

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new DeassignUserFromParking();
        $command->execute($this->loggedInUser, $this->parking, $this->userOne);

        $this->assertEquals(false, $this->parking->isUserAssigned($this->userOne));
        $this->assertEquals(
            [
                ParkingSlot::EVENT_USER_REMOVED_FROM_PARKING_SLOT,
                ParkingSlot::EVENT_USER_REMOVED_FROM_PARKING_SLOT,
                Parking::EVENT_USER_REMOVED_FROM_PARKING
            ],
            $this->recordedEventNames
        );
    }
}

