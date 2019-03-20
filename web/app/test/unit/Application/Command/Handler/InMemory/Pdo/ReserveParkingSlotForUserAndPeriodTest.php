<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\ReserveParkingSlotForUserAndPeriod
    as ReserveParkingSlotForUserAndPeriodPayload;
use Jmj\Parking\Application\Command\Handler\ReserveParkingSlotForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class ReserveParkingSlotForUserAndPeriodTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;
    use NormalizeDate;
    use AssertSqlStatements;

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

        $checkFromDate = new DateTimeImmutable('+1 days');
        $checkToDate = new DateTimeImmutable('+8 days');

        $reserveFromDate = new DateTimeImmutable('+3 days');
        $reserveToDate = new DateTimeImmutable('+5 days');

        $this->parkingRepository->save($this->parking);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new ReserveParkingSlotForUserAndPeriodPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid(),
            $this->parking->uuid(),
            $this->parkingSlotOne->uuid(),
            $reserveFromDate,
            $reserveToDate
        );

        $command = new ReserveParkingSlotForUserAndPeriod(
            $this->parkingRepository,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_RESERVED ], $this->recordedEventNames);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($this->parkingSlotOne->uuid());

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date) use (
                $parkingSlotFound,
                $reserveFromDate,
                $reserveToDate
            ) {
                $this->assertEquals(
                    $this->dateInRange($date, $reserveFromDate, $reserveToDate),
                    !$parkingSlotFound->isFreeForDate($date)
                );
            }
        );

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
    }
}
