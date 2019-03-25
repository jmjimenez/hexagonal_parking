<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Service\Command\UserLogin;
use PHPUnit\Framework\TestCase;

class UserLoginTest extends TestCase
{
    use Common\DataSamplesGenerator;
    use EventsRecorder;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ParkingException
     */
    public function testExecute()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new UserLogin();
        $command->execute(
            $this->loggedInUser,
            sprintf('%spassword', $this->loggedInUser->name())
        );

        $this->assertEquals([ User::EVENT_USER_AUTHENTICATED ], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testExecuteErrorWhenInvalidPassword()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->expectException(ParkingException::class);
        $this->expectExceptionCode(14);

        $this->startRecordingEvents();
        $command = new UserLogin();
        $command->execute(
            $this->loggedInUser,
            sprintf('%spasswordinvalid', $this->loggedInUser->name())
        );

        $this->assertEquals([ User::EVENT_USER_AUTHENTICATION_ERROR ], $this->recordedEventNames);
    }
}
