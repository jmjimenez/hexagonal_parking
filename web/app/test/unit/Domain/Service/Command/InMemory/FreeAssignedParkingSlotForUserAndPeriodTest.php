<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\FreeAssignedParkingSlotForUserAndPeriod;
use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class FreeAssignedParkingSlotForUserAndPeriodTest extends TestCase
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
     * @throws \Exception
     */
    public function testExecute()
    {
        $assignFromDate =  new DateTimeImmutable();
        $assignToDate =  new DateTimeImmutable('+20 days');

        $this->createTestCase();
        $this->assignParkingSlotOneToUserOne($assignFromDate, $assignToDate, true);

        $freeFromDate = new DateTimeImmutable('+3 days');
        $freeToDate = new DateTimeImmutable('+5 days');

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new FreeAssignedParkingSlotForUserAndPeriod();
        $command->execute(
            $this->loggedInUser,
            $this->parking,
            $this->userOne,
            $this->parkingSlotOne->uuid(),
            $freeFromDate,
            $freeToDate
        );

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_MARKED_AS_FREE ], $this->recordedEventNames);

        $parking = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlot = $parking->getParkingSlotByUuid($this->parkingSlotOne->uuid());

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $assignFromDate,
            $assignToDate,
            function (\DateTimeImmutable $date) use ($parkingSlot, $freeFromDate, $freeToDate) {
                if ($date->format('Y-m-d') >= $freeFromDate->format('Y-m-d')
                    && $date->format('Y-m-d') <= $freeToDate->format('Y-m-d')) {
                    $this->assertTrue($parkingSlot->isFreeForDate($date));
                } else {
                    $this->assertFalse($parkingSlot->isFreeForDate($date));
                }
            }
        );
    }
}
