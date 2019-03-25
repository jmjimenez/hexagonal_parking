<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\FreeAssignedParkingSlotForUserAndPeriod
    as FreeAssignedParkingSlotForUserAndPeriodPayload;
use Jmj\Parking\Application\Command\Handler\FreeAssignedParkingSlotForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class FreeAssignedParkingSlotForUserAndPeriodTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;
    use Common\AssertSqlStatements;

    /**
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws \Exception
     */
    public function testExecute()
    {
        $this->createTestCase();

        $assignFromDate = new DateTimeImmutable('+1 days');
        $assignToDate = new DateTimeImmutable('+8 days');
        $exclusive = true;

        $freeFromDate = new DateTimeImmutable('+3 days');
        $freeToDate = new DateTimeImmutable('+5 days');

        $this->parkingSlotOne->assignToUserForPeriod($this->userOne, $assignFromDate, $assignToDate, $exclusive);
        $this->parkingRepository->save($this->parking);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new FreeAssignedParkingSlotForUserAndPeriodPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid(),
            $this->parking->uuid(),
            $this->parkingSlotOne->uuid(),
            $freeFromDate,
            $freeToDate
        );

        $command = new FreeAssignedParkingSlotForUserAndPeriod(
            $this->parkingRepository,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_MARKED_AS_FREE ], $this->recordedEventNames);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($this->parkingSlotOne->uuid());

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $assignFromDate,
            $assignToDate,
            function (DateTimeImmutable $date) use (
                $parkingSlotFound,
                $assignFromDate,
                $assignToDate,
                $freeFromDate,
                $freeToDate
            ) {
                if ($this->dateInRange($date, $freeFromDate, $freeToDate)) {
                    $this->assertTrue($parkingSlotFound->isFreeForDate($date));
                } else {
                    $this->assertFalse($parkingSlotFound->isFreeForDate($date));
                }
            }
        );

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
    }
}
