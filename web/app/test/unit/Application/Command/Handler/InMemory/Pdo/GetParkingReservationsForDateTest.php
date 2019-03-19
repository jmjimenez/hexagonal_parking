<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\GetParkingReservationsForDate
    as GetParkingReservationsForDatePayload;
use Jmj\Parking\Application\Command\Handler\GetParkingReservationsForDate;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use PHPUnit\Framework\TestCase;

class GetParkingReservationsForDateTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;
    use NormalizeDate;
    use AssertSqlStatements;

    /**
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws \Exception
     */
    public function testExecute()
    {
        $this->createTestCase();

        $checkFromDate = new DateTimeImmutable('+9 days');
        $checkToDate = new DateTimeImmutable('+15 days');

        $reserveFromDate = new DateTimeImmutable('+10 days');
        $reserveToDate = new DateTimeImmutable('+13 days');

        $this->parkingSlotOne->reserveToUserForPeriod($this->userOne, $reserveFromDate, $reserveToDate);
        $this->parkingRepository->save($this->parking);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $command = new GetParkingReservationsForDate(
            $this->userRepository,
            $this->parkingRepository
        );

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date) use (
                $reserveFromDate,
                $reserveToDate,
                $command
            ) {
                $payload = new GetParkingReservationsForDatePayload(
                    $this->userOne->uuid(),
                    $this->parking->uuid(),
                    $date
                );

                $parkingReservations = $command->execute($payload);

                $this->assertEquals([ ], $this->recordedEventNames);

                $this->assertEquals(0, count($this->recordedSqlStatements));

                $this->assertEquals(
                    $this->dateInRange($date, $reserveFromDate, $reserveToDate) ? 1 : 0,
                    count($parkingReservations)
                );
            }
        );
    }
}
