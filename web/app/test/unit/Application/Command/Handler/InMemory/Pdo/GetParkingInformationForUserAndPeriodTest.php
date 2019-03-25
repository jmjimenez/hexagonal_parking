<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\GetParkingInformationForUserAndPeriod
    as GetParkingInformationForUserAndPeriodPayload;
use Jmj\Parking\Application\Command\Handler\GetParkingInformationForUserAndPeriod;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class GetParkingInformationForUserAndPeriodTest extends TestCase
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

        $checkFromDate = new DateTimeImmutable('+1 days');
        $checkToDate = new DateTimeImmutable('+15 days');

        $assignFromDate = new DateTimeImmutable('+3 days');
        $assignToDate = new DateTimeImmutable('+8 days');
        $exclusive = true;

        $freeFromDate = new DateTimeImmutable('+4 days');
        $freeToDate = new DateTimeImmutable('+4 days');

        $reserveFromDate = new DateTimeImmutable('+10 days');
        $reserveToDate = new DateTimeImmutable('+13 days');

        $this->parkingSlotOne->assignToUserForPeriod($this->userOne, $assignFromDate, $assignToDate, $exclusive);
        $this->parkingSlotOne->markAsFreeFromUserAndPeriod($this->userOne, $freeFromDate, $freeToDate);
        $this->parkingSlotOne->reserveToUserForPeriod($this->userOne, $reserveFromDate, $reserveToDate);
        $this->parkingRepository->save($this->parking);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new GetParkingInformationForUserAndPeriodPayload(
            $this->userOne->uuid(),
            $this->parking->uuid(),
            $checkFromDate,
            $checkToDate
        );

        $command = new GetParkingInformationForUserAndPeriod(
            $this->parkingRepository,
            $this->userRepository
        );
        $parkingInformation = $command->execute($payload);

        $this->assertEquals([ ], $this->recordedEventNames);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $this->assertEquals(2, count($parkingInformation));
        $this->assertTrue(isset($parkingInformation['assignments']));
        $this->assertTrue(isset($parkingInformation['reservations']));
        $this->assertEquals(5, count($parkingInformation['assignments']));
        $this->assertEquals(4, count($parkingInformation['reservations']));
    }
}
