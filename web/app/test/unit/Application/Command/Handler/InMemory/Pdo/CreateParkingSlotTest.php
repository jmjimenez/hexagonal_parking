<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\CreateParkingSlot as CreateParkingSlotPayload;
use Jmj\Parking\Application\Command\Handler\CreateParkingSlot;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class CreateParkingSlotTest extends TestCase
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

        $parkingSlotNumber = '22';
        $parkingSlotDescription = 'New Parking Slot Test';

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new CreateParkingSlotPayload(
            $this->userAdmin->uuid(),
            $this->parking->uuid(),
            $parkingSlotNumber,
            $parkingSlotDescription
        );

        $command = new CreateParkingSlot(
            $this->userRepository,
            $this->parkingRepository
        );
        $newParkingSlot = $command->execute($payload);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_CREATED, Parking::EVENT_PARKING_SLOT_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByNumber($parkingSlotNumber);

        $this->assertInstanceOf(ParkingSlot::class, $parkingSlotFound);
        $this->assertEquals($parkingSlotDescription, $parkingSlotFound->description());
        $this->assertEquals($newParkingSlot->uuid(), $parkingSlotFound->uuid());

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
    }
}
