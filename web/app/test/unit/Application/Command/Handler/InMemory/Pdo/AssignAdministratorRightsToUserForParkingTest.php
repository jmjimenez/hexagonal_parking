<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\AssignAdministratorRightsToUserForParking
    as AssignAdministratorRightsToUserForParkingPayload;
use Jmj\Parking\Application\Command\Handler\AssignAdministratorRightsToUserForParking;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class AssignAdministratorRightsToUserForParkingTest extends TestCase
{
    use EventsRecorder;
    use DataSamplesGenerator;
    use AssertSqlStatements;

    /**
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     */
    public function testExecute()
    {
        //TODO: test wrong paths for all commands
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new AssignAdministratorRightsToUserForParkingPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid(),
            $this->parking->uuid()
        );

        $command = new AssignAdministratorRightsToUserForParking(
            $this->parkingRepository,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals([ Parking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING ], $this->recordedEventNames);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $userFound = $this->userRepository->findByUuid($this->userOne->uuid());
        $this->assertTrue($parkingFound->isAdministeredByUser($userFound));

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
    }
}
