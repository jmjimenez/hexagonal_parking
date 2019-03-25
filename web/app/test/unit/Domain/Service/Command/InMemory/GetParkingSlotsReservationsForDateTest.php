<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\GetParkingReservationsForDate;
use Jmj\Parking\Common\EventsRecorder;
use PHPUnit\Framework\TestCase;

class GetParkingSlotsReservationsForDateTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testExecute()
    {
        $checkFromDate = new DateTimeImmutable('+3 days');
        $checkToDate = new DateTimeImmutable('+18 days');

        $reserveOneFromDate =  new DateTimeImmutable('+8 days');
        $reserveOneToDate =  new DateTimeImmutable('+15 days');

        $reserveTwoFromDate =  new DateTimeImmutable('+14 days');
        $reserveTwoToDate =  new DateTimeImmutable('+16 days');

        $this->createTestCase();
        $this->reserveParkingSlotOneForUserOne($reserveOneFromDate, $reserveOneToDate);
        $this->reserveParkingSlotTwoForUserTwo($reserveTwoFromDate, $reserveTwoToDate);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new GetParkingReservationsForDate();

        $dateProcesor = new DateRangeProcessor();

        $dateProcesor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date) use (
                $command,
                $reserveOneFromDate,
                $reserveOneToDate,
                $reserveTwoFromDate,
                $reserveTwoToDate
            ) {
                $expectedReservations = 0;

                if ($this->dateInRange($date, $reserveOneFromDate, $reserveOneToDate)) {
                    $expectedReservations++;
                }

                if ($this->dateInRange($date, $reserveTwoFromDate, $reserveTwoToDate)) {
                    $expectedReservations++;
                }

                $parkingSlotReservations = $command->execute(
                    $this->loggedInUser,
                    $this->parking,
                    $date
                );

                $this->assertEquals($expectedReservations, count($parkingSlotReservations));
            }
        );
    }
}
