<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Firebase\JWT\JWT;
use Jmj\Parking\Application\Command\Handler\UserLogin;
use Jmj\Parking\Application\Command\UserLogin as UserLoginPayload;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class UserLoginTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;
    use Common\AssertSqlStatements;

    /** @var string */
    const ALGORITHM = 'HS256';

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

        $tokenSecret = 'tokensecret';

        $command = new UserLogin($this->userRepository, $tokenSecret, self::ALGORITHM);

        $payload = new UserLoginPayload($this->userOne->email(), 'userpasswd');
        $token = $command->execute($payload);

        $this->assertEquals([ User::EVENT_USER_AUTHENTICATED ], $this->recordedEventNames);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $authInfo = JWT::decode($token, $tokenSecret, [self::ALGORITHM]);

        $this->assertEquals($authInfo->email, $this->userOne->email());
        $this->assertTrue($this->userOne->checkPassword($authInfo->password));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws \Jmj\Parking\Application\Command\Handler\Exception\UserNotFound
     */
    public function testExecuteErrorWhenInvalidPassword()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $tokenSecret = 'tokensecret';

        $command = new UserLogin($this->userRepository, $tokenSecret, self::ALGORITHM);

        $payload = new UserLoginPayload($this->userOne->email(), 'userpasswdinvalid');
        $this->expectException(ParkingException::class);
        $this->expectExceptionCode(14);
        $token = $command->execute($payload);

        $this->assertEquals([ User::EVENT_USER_AUTHENTICATION_ERROR ], $this->recordedEventNames);

        $this->assertEquals(0, count($this->recordedSqlStatements));
        $this->assertNull($token);
    }
}
