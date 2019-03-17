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
use Jmj\Parking\Domain\Service\Command\CreateParking;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\Parking as InMemoryParkingFactory;
use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class CreateParkingTest extends TestCase
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
        $description = 'ParkingCreationTest';

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new CreateParking(new InMemoryParkingFactory());
        $parking = $command->execute($this->loggedInUser, $description);

        $this->assertEquals([ Parking::EVENT_PARKING_CREATED ], $this->recordedEventNames);

        $this->assertInstanceOf(Parking::class, $parking);
        $this->assertEquals($parking->description(), $description);
    }
}
