<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class GetParkingInformationForUserAndPeriodTest extends TestBase
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

        $parkingUuid = $this->parking->uuid();
        $parkingSlotUuid = $this->parkingSlotOne->uuid();
        $userUuid = $this->userOne->uuid();

        $exclusive = true;

        $this->parkingSlotOne
            ->assignToUserForPeriod($this->userOne, $this->assignFromDate, $this->assignToDate, $exclusive);
        $this->parkingSlotOne
            ->markAsFreeFromUserAndPeriod($this->userOne, $this->freeFromDate, $this->freeToDate);
        $this->parkingSlotOne
            ->reserveToUserForPeriod($this->userOne, $this->reserveFromDate, $this->reserveToDate);
        $this->parkingRepository
            ->save($this->parking);

        $params = [
            'userUuid' => $userUuid,
            'parkingUuid' => $parkingUuid,
            'parkingSlotUuid' => $parkingSlotUuid,
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
