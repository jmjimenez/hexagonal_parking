<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\CreateParking as CreateParkingPayload;
use Jmj\Parking\Application\Command\Handler\CreateParking;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\Parking as InMemoryParkingFactory;
use PHPUnit\Framework\TestCase;

class CreateParkingTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use Common\AssertSqlStatements;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
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

        $newParkingDescription = 'New Parking Test';
        $payload = new CreateParkingPayload($this->userAdmin->uuid(), $newParkingDescription);

        $command = new CreateParking(
            $this->pdoProxy,
            $this->userRepository,
            new InMemoryParkingFactory(),
            $this->parkingRepository
        );
        $newParking = $command->execute($payload);

        $this->assertEquals([ Parking::EVENT_PARKING_CREATED ], $this->recordedEventNames);

        $parkingFound = $this->parkingRepository->findByUuid($newParking->uuid());
        $this->assertEquals($newParkingDescription, $parkingFound->description());

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertInsert(
            $this->recordedSqlStatements[0],
            'Parking',
            ['version' => '1', 'uuid' => $newParking->uuid(), 'class' => Parking::class]
        );
    }
}
