<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\ResetUserPassword as ResetUserPasswordPayload;
use Jmj\Parking\Application\Command\Handler\ResetUserPassword;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use PHPUnit\Framework\TestCase;

class ResetUserPasswordTest extends TestCase
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

        $passwordToken = 'passwordToken';
        $passwordTokenExpirationDate = new DateTimeImmutable('+3 days');
        $newUserPassword = 'newUserPassword';

        $this->userOne->requestResetPassword($passwordToken, $passwordTokenExpirationDate);
        $this->userRepository->save($this->userOne);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $command = new ResetUserPassword($this->userRepository);

        $payload = new ResetUserPasswordPayload($this->userOne->email(), $passwordToken, $newUserPassword);
        $command->execute($payload);

        $this->assertEquals([ User::EVENT_USER_PASSWORD_RESETTED ], $this->recordedEventNames);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'User', ['uuid' => $this->userOne->uuid()]);

        $userFound = $this->userRepository->findByUuid($this->userOne->uuid());

        $this->assertTrue($userFound->checkPassword($newUserPassword));
    }
}
