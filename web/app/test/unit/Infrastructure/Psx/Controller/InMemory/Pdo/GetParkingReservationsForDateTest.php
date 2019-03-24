<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class GetParkingReservationsForDateTest extends TestBase
{
    use NormalizeDate;

    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $this->generateParkingSlotOneReservations();

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process($this->checkFromDate, $this->checkToDate, function (DateTimeImmutable $date) {
            $params = [
                'userUuid' => $this->userUuid,
                'parkingUuid' => $this->parkingUuid,
                'parkingSlotUuid' => $this->parkingSlotUuid,
                'date' => $date->format('Y-m-d')
            ];

            $request = new TestRequest(
                'POST',
                '/getparkingreservationsfordate',
                $this->generateAuthorizationKey(),
                json_encode($params)
            );

            $output = $this->executeRequest($request);

            $this->assertEquals(0, count($this->recordedSqlStatements));

            $this->assertResponseCount($output, 1);
            $this->assertResponse(
                $output,
                'result',
                function ($result) use ($date) {
                    $this->assertEquals(
                        $this->dateInRange($date, $this->reserveFromDate, $this->reserveToDate) ? 1 : 0,
                        count($result)
                    );
                }
            );
        });
    }
}
