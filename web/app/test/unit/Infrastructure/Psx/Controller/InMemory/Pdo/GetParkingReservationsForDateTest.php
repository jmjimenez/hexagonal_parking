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

    protected $checkFromDate;
    protected $checkToDate;
    protected $assignFromDate;
    protected $assignToDate;
    protected $freeFromDate;
    protected $freeToDate;
    protected $reserveFromDate;
    protected $reserveToDate;
    protected $parkingUuid;
    protected $parkingSlotUuid;
    protected $userUuid;

    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $this->checkFromDate = new DateTimeImmutable('+1 days');
        $this->checkToDate = new DateTimeImmutable('+25 days');

        $this->assignFromDate = new DateTimeImmutable('+3 days');
        $this->assignToDate = new DateTimeImmutable('+13 days');

        $this->freeFromDate = new DateTimeImmutable('+5 days');
        $this->freeToDate = new DateTimeImmutable('+10 days');

        $this->reserveFromDate = new DateTimeImmutable('+19 days');
        $this->reserveToDate = new DateTimeImmutable('+22 days');

        $this->parkingUuid = $this->parking->uuid();
        $this->parkingSlotUuid = $this->parkingSlotOne->uuid();
        $this->userUuid = $this->userOne->uuid();

        $exclusive = true;

        $this->parkingSlotOne
            ->assignToUserForPeriod($this->userOne, $this->assignFromDate, $this->assignToDate, $exclusive);
        $this->parkingSlotOne
            ->markAsFreeFromUserAndPeriod($this->userOne, $this->freeFromDate, $this->freeToDate);
        $this->parkingSlotOne
            ->reserveToUserForPeriod($this->userOne, $this->reserveFromDate, $this->reserveToDate);
        $this->parkingRepository
            ->save($this->parking);

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
