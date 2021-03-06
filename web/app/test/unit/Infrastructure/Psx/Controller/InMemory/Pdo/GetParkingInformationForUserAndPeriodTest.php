<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class GetParkingInformationForUserAndPeriodTest extends TestBase
{
    use NormalizeDate;

    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $this->generateParkingSlotOneReservations();

        $params = [
            'userUuid' => $this->userUuid,
            'parkingUuid' => $this->parkingUuid,
            'parkingSlotUuid' => $this->parkingSlotUuid,
            'fromDate' => $this->checkFromDate->format('Y-m-d'),
            'toDate' => $this->checkToDate->format('Y-m-d'),
        ];

        $request = new TestRequest(
            'POST',
            '/getparkinginformationforuserandperiod',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $this->assertResponseCount($output, 1);
        $this->assertResponse(
            $output,
            'result',
            function ($result) {
                $this->assertResult($result);
            }
        );
    }

    /**
     * @param array $result
     * @throws \Exception
     */
    public function assertResult(array $result) : void
    {
        $this->assertEquals(2, count($result));
        $this->assertTrue(isset($result['assignments']));
        $this->assertTrue(isset($result['reservations']));

        foreach ($result['assignments'] as $assignment) {
            $this->assertTrue(
                $this->dateInRange(
                    new DateTimeImmutable($assignment['date']),
                    $this->assignFromDate,
                    $this->assignToDate
                )
                && !$this->dateInRange(
                    new DateTimeImmutable($assignment['date']),
                    $this->freeFromDate,
                    $this->freeToDate
                )
            );
        }

        foreach ($result['reservations'] as $reservation) {
            $this->assertTrue(
                $this->dateInRange(
                    new DateTimeImmutable($reservation['date']),
                    $this->reserveFromDate,
                    $this->reserveToDate
                )
            );
        }
    }
}
