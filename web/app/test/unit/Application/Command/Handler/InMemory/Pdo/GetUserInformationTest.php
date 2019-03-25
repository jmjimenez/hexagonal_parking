<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\GetUserInformation
    as GetUserInformationPayload;
use Jmj\Parking\Application\Command\Handler\GetUserInformation;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use PHPUnit\Framework\TestCase;

class GetUserInformationTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;
    use Common\AssertSqlStatements;

    /**
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws \Exception
     */
    public function testExecute()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $command = new GetUserInformation($this->userRepository);

        $payload = new GetUserInformationPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid()
        );

        $userInformation = $command->execute($payload);

        $this->assertEquals([ ], $this->recordedEventNames);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $this->assertEquals(4, count($userInformation));
        $this->assertEquals($this->userOne->uuid(), $userInformation['uuid']);
        $this->assertEquals($this->userOne->name(), $userInformation['name']);
        $this->assertEquals($this->userOne->email(), $userInformation['email']);
        $this->assertEquals($this->userOne->isAdministrator(), $userInformation['isAdministrator']);
    }
}
