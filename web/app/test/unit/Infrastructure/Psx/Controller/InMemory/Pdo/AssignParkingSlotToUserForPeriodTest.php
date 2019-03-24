<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class AssignParkingSlotToUserForPeriodTest extends TestBase
{
    use NormalizeDate;

    /**
     * @throws \Jmj\Parking\Common\Exception\PdoExecuteError
     * @throws \Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid
     * @throws \Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserEmailInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserNameAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserNameInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserPasswordInvalid
     * @throws \Exception
     */
    public function testOnPost()
    {
        $this->createTestContainer();

        $this->createTestCase(
            $this->container->get('PdoProxy'),
            $this->container->get('UserRepository'),
            $this->container->get('ParkingRepository')
        );

        $checkFromDate = new DateTimeImmutable('+1 days');
        $checkToDate = new DateTimeImmutable('+15 days');

        $assignFromDate = new DateTimeImmutable('+3 days');
        $assignToDate = new DateTimeImmutable('+13 days');

        $parkingSlotUuid = $this->parkingSlotOne->uuid();

        $exclusive = true;

        $params = [
            'userUuid' => $this->userOne->uuid(),
            'parkingUuid' => $this->parking->uuid(),
            'parkingSlotUuid' => $parkingSlotUuid,
            'fromDate' => $assignFromDate->format('Y-m-d'),
            'toDate' => $assignToDate->format('Y-m-d'),
            'exclusive' => $exclusive
        ];

        $request = new TestRequest(
            'POST',
            '/assignparkingslottouserforperiod',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'parkings', ['uuid' => $this->parking->uuid()]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($parkingSlotUuid);

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date) use (
                $parkingSlotFound,
                $checkFromDate,
                $checkToDate,
                $assignFromDate,
                $assignToDate
            ) {
                if ($this->dateInRange($date, $assignFromDate, $assignToDate)) {
                    $this->assertFalse($parkingSlotFound->isFreeForDate($date));
                } else {
                    $this->assertTrue($parkingSlotFound->isFreeForDate($date));
                }
            }
        );
    }
}
