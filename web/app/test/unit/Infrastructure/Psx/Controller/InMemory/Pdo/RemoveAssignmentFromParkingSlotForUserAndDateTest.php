<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class RemoveAssignmentFromParkingSlotForUserAndDateTest extends TestBase
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

        $removeAssignmentFromDate = new DateTimeImmutable('+5 days');

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
            'date' => $removeAssignmentFromDate->format('Y-m-d'),
        ];

        $request = new TestRequest(
            'POST',
            '/removeassignmentfromparkingslotforuseranddate',
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
                $removeAssignmentFromDate
            ) {
                if ($this->dateInRange($date, $assignFromDate, $assignToDate)) {
                    if ($this->dateGreaterThanOrEqual($date, $removeAssignmentFromDate)) {
                        $this->assertTrue($parkingSlotFound->isFreeForDate($date));
                    } else {
                        $this->assertFalse($parkingSlotFound->isFreeForDate($date));
                    }
                } else {
                    $this->assertTrue($parkingSlotFound->isFreeForDate($date));
                }
            }
        );
    }
}
