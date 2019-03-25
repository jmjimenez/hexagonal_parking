<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\UpdateParkingSlotInformation as UpdateParkingSlotInformationPayload;
use Jmj\Parking\Application\Command\Handler\UpdateParkingSlotInformation;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class UpdateParkingSlotInformationTest extends TestCase
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

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $parkingSlotNumber = '999';
        $parkingSlotDescription = 'Parking Slot 999';

        $payload = new UpdateParkingSlotInformationPayload(
            $this->userAdmin->uuid(),
            $this->parking->uuid(),
            $this->parkingSlotOne->uuid(),
            $parkingSlotNumber,
            $parkingSlotDescription
        );

        $command = new UpdateParkingSlotInformation(
            $this->userRepository,
            $this->parkingRepository
        );
        $command->execute($payload);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_INFORMATION_UPDATED ],
            $this->recordedEventNames
        );

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($this->parkingSlotOne->uuid());
        $this->assertEquals($parkingSlotDescription, $parkingSlotFound->description());
        $this->assertEquals($parkingSlotNumber, $parkingSlotFound->number());
    }
}
