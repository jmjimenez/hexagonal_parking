<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Value\Reservation;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class ReserveParkingSlotForUserAndPeriodTest extends TestBase
{
    use NormalizeDate;

    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $checkFromDate = new DateTimeImmutable('+1 days');
        $checkToDate = new DateTimeImmutable('+20 days');

        $reserveFromDate = new DateTimeImmutable('+3 days');
        $reserveToDate = new DateTimeImmutable('+13 days');

        $parkingUuid = $this->parking->uuid();
        $parkingSlotUuid = $this->parkingSlotOne->uuid();
        $userUuid = $this->userOne->uuid();

        $params = [
            'userUuid' => $userUuid,
            'parkingUuid' => $parkingUuid,
            'parkingSlotUuid' => $parkingSlotUuid,
            'fromDate' => $reserveFromDate->format('Y-m-d'),
            'toDate' => $reserveToDate->format('Y-m-d'),
        ];

        $request = new TestRequest(
            'POST',
            '/reserveparkingslotforuserandperiod',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'parkings', ['uuid' => $this->parking->uuid()]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($parkingUuid);

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date) use (
                $parkingFound,
                $reserveFromDate,
                $reserveToDate,
                $parkingSlotUuid,
                $userUuid
            ) {
                $reservations = $parkingFound->getParkingSlotsReservationsForDate($date);

                if ($this->dateInRange($date, $reserveFromDate, $reserveToDate)) {
                    $this->assertEquals(1, count($reservations));
                    /** @var Reservation $reservation */
                    $reservation = array_shift($reservations);
                    $this->assertEquals($parkingSlotUuid, $reservation->parkingSlot()->uuid());
                    $this->assertEquals($userUuid, $reservation->user()->uuid());
                } else {
                    $this->assertEquals(0, count($reservations));
                }
            }
        );
    }
}
