<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\DeleteUser as DeleteUserPayload;
use Jmj\Parking\Application\Command\Handler\DeleteUser;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class DeleteUserTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;
    use Common\AssertSqlStatements;

    /**
     * @throws UserNotFound
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     */
    public function testExecute()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new DeleteUserPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid()
        );

        $command = new DeleteUser(
            $this->pdoProxy,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals(
            [ User::EVENT_USER_DELETED ],
            $this->recordedEventNames
        );

        $userFound = $this->userRepository->findByUuid($this->userOne->uuid());
        $this->assertNull($userFound);

        $this->assertEquals(1, count($this->recordedSqlStatements));

        $this->assertDelete(
            $this->recordedSqlStatements[0],
            'User',
            ['uuid' => $this->userOne->uuid(), 'version' => '1' ]
        );
    }
}
