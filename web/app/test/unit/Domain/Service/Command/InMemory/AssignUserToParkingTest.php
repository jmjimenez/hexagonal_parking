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
use Jmj\Parking\Domain\Service\Command\AssignUserToParking;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class AssignUserToParkingTest extends TestCase
{
    use DataSamplesGenerator;
    use DomainEventsRegister;

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
        //TODO: implement wrong path (like assigning one already assigned parking slot)
        $this->createTestCase();

        $user = $this->createUser('user3', false);
        $isAdministrator = false;

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new AssignUserToParking($this->parkingRepository);
        $command->execute(
            $this->loggedInUser,
            $user,
            $this->parking,
            $isAdministrator
        );

        $this->assertEquals([ Parking::EVENT_USER_ADDED_TO_PARKING ], $this->recordedEventNames);

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());

        $this->assertTrue($parking->isUserAssigned($user));
        $this->assertFalse($parking->isAdministeredByUser($user));
    }
}

