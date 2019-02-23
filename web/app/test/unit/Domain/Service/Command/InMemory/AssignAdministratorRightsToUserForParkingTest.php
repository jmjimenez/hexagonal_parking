<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\AssignAdministratorRightsToUserForParking;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class AssignAdministratorRightsToUserForParkingTest extends TestCase
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
        $command = new AssignAdministratorRightsToUserForParking();
        $command->execute($this->loggedInUser, $this->userOne, $this->parking);

        $this->assertEquals(true, $this->parking->isAdministeredByUser($this->userOne));
        $this->assertEquals([ Parking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING ], $this->recordedEventNames);
    }
}

