<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\ResetPasswordForUser;
use Jmj\Parking\Infrastructure\Aggregate\Event\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class ResetPasswordForUserTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ParkingException
     * @throws \Exception
     */
    public function testExecute()
    {
        $passwordToken = 'passwordtoken';
        $passwordTimeout = new DateTimeImmutable('+5 days');
        $password = 'newpassword';

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->userOne->requestResetPassword($passwordToken, $passwordTimeout);

        $this->startRecordingEvents();
        $command = new ResetPasswordForUser();
        $command->execute(
            $this->userOne,
            $password,
            $passwordToken
        );

        $this->assertEquals([ User::EVENT_USER_PASSWORD_RESETTED ], $this->recordedEventNames);
        $this->assertTrue($this->userOne->checkPassword($password));
    }
}

