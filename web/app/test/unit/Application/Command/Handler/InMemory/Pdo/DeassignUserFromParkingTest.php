<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\DeassignUserFromParking as DeassignUserFromParkingPayload;
use Jmj\Parking\Application\Command\Handler\DeassignUserFromParking;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class DeassignUserFromParkingTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use Common\AssertSqlStatements;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingNotFound
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws UserNotFound
     * @throws \Exception
     */
    public function testExecute()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new DeassignUserFromParkingPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid(),
            $this->parking->uuid()
        );

        $command = new DeassignUserFromParking(
            $this->pdoProxy,
            $this->parkingRepository,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals(
            [ ParkingSlot::EVENT_USER_REMOVED_FROM_PARKING_SLOT, Parking::EVENT_USER_REMOVED_FROM_PARKING ],
            $this->recordedEventNames
        );

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $userFound = $parkingFound->getUserByUuid($this->userOne->uuid());
        $this->assertNull($userFound);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
    }
}
