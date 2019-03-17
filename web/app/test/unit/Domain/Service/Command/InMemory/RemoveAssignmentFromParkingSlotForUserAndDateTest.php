<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\RemoveAssignmentFromParkingSlotForUserAndDate;
use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class RemoveAssignmentFromParkingSlotForUserAndDateTest extends TestCase
{
    use DomainEventsRegister;
    use NormalizeDate;
    use DataSamplesGenerator;

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
        $assignFromDate =  new DateTimeImmutable();
        $assignToDate =  new DateTimeImmutable('+20 days');

        $removeAssigmentFromDate = new DateTimeImmutable('+15 days');

        $this->createTestCase();
        $this->assignParkingSlotOneToUserOne($assignFromDate, $assignToDate, true);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new RemoveAssignmentFromParkingSlotForUserAndDate();
        $command->execute(
            $this->loggedInUser,
            $this->parking,
            $this->parkingSlotOne->uuid(),
            $this->userOne,
            $removeAssigmentFromDate
        );

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_ASSIGNMENT_REMOVED ], $this->recordedEventNames);

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlot = $parking->getParkingSlotByUuid($this->parkingSlotOne->uuid());

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $assignFromDate,
            $assignToDate,
            function (DateTimeImmutable $date) use (
                $parkingSlot,
                $assignFromDate,
                $assignToDate,
                $removeAssigmentFromDate
            ) {
                if ($this->dateInRange($date, $assignFromDate, $this->decrementDate($removeAssigmentFromDate, 1))) {
                    $this->assertFalse($parkingSlot->isFreeForDate($date));
                } else {
                    $this->assertTrue($parkingSlot->isFreeForDate($date));
                }
            }
        );
    }
}
