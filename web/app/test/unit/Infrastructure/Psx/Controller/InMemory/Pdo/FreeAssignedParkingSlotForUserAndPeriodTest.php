<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class FreeAssignedParkingSlotForUserAndPeriodTest extends TestBase
{
    use NormalizeDate;

    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $checkFromDate = new DateTimeImmutable('+1 days');
        $checkToDate = new DateTimeImmutable('+20 days');

        $assignFromDate = new DateTimeImmutable('+3 days');
        $assignToDate = new DateTimeImmutable('+13 days');

        $freeFromDate = new DateTimeImmutable('+5 days');
        $freeToDate = new DateTimeImmutable('+10 days');

        $parkingUuid = $this->parking->uuid();
        $parkingSlotUuid = $this->parkingSlotOne->uuid();
        $userUuid = $this->userOne->uuid();

        $exclusive = true;

        $this->parkingSlotOne->assignToUserForPeriod($this->userOne, $assignFromDate, $assignToDate, $exclusive);
        $this->parkingRepository->save($this->parking);

        $params = [
            'userUuid' => $userUuid,
            'parkingUuid' => $parkingUuid,
            'parkingSlotUuid' => $parkingSlotUuid,
            'fromDate' => $freeFromDate->format('Y-m-d'),
            'toDate' => $freeToDate->format('Y-m-d'),
        ];

        $request = new TestRequest(
            'POST',
            '/freeassignedparkingslotforuserandperiod',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'parkings', ['uuid' => $this->parking->uuid()]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($parkingUuid);
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($parkingSlotUuid);

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date) use (
                $parkingSlotFound,
                $assignFromDate,
                $assignToDate,
                $freeFromDate,
                $freeToDate
            ) {
                if ($this->dateInRange($date, $freeFromDate, $freeToDate)) {
                    $this->assertTrue($parkingSlotFound->isFreeForDate($date));
                } elseif ($this->dateInRange($date, $assignFromDate, $assignToDate)) {
                    $this->assertFalse($parkingSlotFound->isFreeForDate($date));
                } else {
                    $this->assertTrue($parkingSlotFound->isFreeForDate($date));
                }
            }
        );
    }
}
