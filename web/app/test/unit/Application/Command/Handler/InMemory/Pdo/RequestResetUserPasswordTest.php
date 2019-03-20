<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\RequestResetUserPassword as RequestResetUserPasswordPayload;
use Jmj\Parking\Application\Command\Handler\RequestResetUserPassword;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use PHPUnit\Framework\TestCase;

class RequestResetUserPasswordTest extends TestCase
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

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $command = new RequestResetUserPassword($this->userRepository);

        $payload = new RequestResetUserPasswordPayload($this->userOne->email());

        $resetPassword = $command->execute($payload);

        $this->assertEquals([ User::EVENT_USER_PASSWORD_RESET_REQUESTED ], $this->recordedEventNames);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'User', ['uuid' => $this->userOne->uuid()]);

        $this->assertEquals(3, count($resetPassword));
        $this->assertEquals($this->userOne->email(), $resetPassword['email']);
        $this->assertTrue(isset($resetPassword['token']));
        $this->assertGreaterThanOrEqual(
            new DateTimeImmutable(),
            new DateTimeImmutable($resetPassword['expirationDate'])
        );
    }
}
