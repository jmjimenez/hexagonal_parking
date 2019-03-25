<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\DeleteParkingSlot as DeleteParkingSlotPayload;
use Jmj\Parking\Application\Command\Handler\DeleteParkingSlot;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class DeleteParkingSlotTest extends TestCase
{
    use EventsRecorder;
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

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new DeleteParkingSlotPayload(
            $this->userAdmin->uuid(),
            $this->parking->uuid(),
            $this->parkingSlotOne->uuid()
        );

        $command = new DeleteParkingSlot(
            $this->userRepository,
            $this->parkingRepository
        );
        $command->execute($payload);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_DELETED, Parking::EVENT_PARKING_SLOT_DELETED_FROM_PARKING ],
            $this->recordedEventNames
        );

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($this->parkingSlotOne->uuid());
        $this->assertNull($parkingSlotFound);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
    }
}
