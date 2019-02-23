<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\GetParkingSlotReservationsForPeriod;
use Jmj\Parking\Domain\Value\Reservation;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class GetParkingSlotReservationsForPeriodTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;
    use NormalizeDate;

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
        $fromDate = new DateTimeImmutable();
        $toDate = new DateTimeImmutable('+30 days');

        $assignFromDate =  new DateTimeImmutable('+3 days');
        $assignToDate =  new DateTimeImmutable('+13 days');

        $freeFromDate = new DateTimeImmutable('+5 days');
        $freeToDate = new DateTimeImmutable('+10 days');

        $reserveFromDate =  new DateTimeImmutable('+14 days');
        $reserveToDate =  new DateTimeImmutable('+15 days');

        $this->createTestCase();
        $this->assignParkingSlotOneToUserOne($assignFromDate, $assignToDate, true);
        $this->freeParkingSlot($freeFromDate, $freeToDate);
        $this->reserveParkingSlotOneForUserOne($freeFromDate, $freeToDate);
        $this->reserveParkingSlotOneForUserOne($reserveFromDate, $reserveToDate);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new GetParkingSlotReservationsForPeriod();
        $parkingSlotReservations = $command->execute(
            $this->userOne,
            $this->parking,
            $this->parkingSlotOne->uuid(),
            $fromDate,
            $toDate
        );

        $this->assertEquals(8, count($parkingSlotReservations));

        /** @var Reservation $reservation */
        foreach ($parkingSlotReservations as $reservation) {
            $this->assertTrue(
                $this->dateInRange($reservation->date(), $freeFromDate, $freeToDate)
                || $this->dateInRange($reservation->date(), $reserveFromDate, $reserveToDate)
            );
        }

    }
}

