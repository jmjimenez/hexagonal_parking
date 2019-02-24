<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\AssignParkingSlotToUserForPeriod;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class AssignParkingSlotToUserForPeriodTest extends TestCase
{
    use DataSamplesGenerator;
    use DomainEventsRegister;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ParkingException
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserNameAlreadyExists
     * @throws \Exception
     */
    public function testExecute()
    {
        //TODO: implement wrong path (like assigning one already assigned parking slot)
        $this->createTestCase();

        $fromDate = new DateTimeImmutable('+1 days');
        $toDate = new DateTimeImmutable('+3 days');

        $exclusive = true;

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new AssignParkingSlotToUserForPeriod($this->parkingRepository);
        $command->execute(
            $this->loggedInUser,
            $this->userOne,
            $this->parking,
            $this->parkingSlotOne->uuid(),
            $fromDate,
            $toDate,
            $exclusive
        );

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_ASSIGNED ], $this->recordedEventNames);

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlot = $parking->getParkingSlotByUuid($this->parkingSlotOne->uuid());

        foreach ($parkingSlot->getAssignmentsForPeriod($fromDate, $toDate) as $assignment) {
            $this->assertEquals($this->userOne, $assignment->user());
            $this->assertEquals($this->parkingSlotOne, $assignment->ParkingSlot());
            $this->assertEquals($exclusive, $assignment->isExclusive());
            $this->assertLessThanOrEqual($assignment->date()->format('Ymd'), $fromDate->format('Ymd'));
            $this->assertGreaterThanOrEqual($assignment->date()->format('Ymd'), $toDate->format('Ymd'));
        }
    }
}

