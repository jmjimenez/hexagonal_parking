<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class GetParkingSlotReservationsForPeriodTest extends TestBase
{
    use NormalizeDate;

    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $this->generateParkingSlotOneReservations();

        $params = [
            'parkingUuid' => $this->parkingUuid,
            'parkingSlotUuid' => $this->parkingSlotUuid,
            'fromDate' => $this->checkFromDate->format('Y-m-d'),
            'toDate' => $this->checkToDate->format('Y-m-d'),
        ];

        $request = new TestRequest(
            'POST',
            '/getparkingslotreservationsforperiod',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $this->assertResponseCount($output, 1);

        $this->assertResponse($output, 'result', function (array $result) {
            $this->assertResult($result);
        });
    }

    /**
     * @param array $result
     * @throws \Exception
     */
    protected function assertResult(array $result): void
    {
        foreach ($result as $reservation) {
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
