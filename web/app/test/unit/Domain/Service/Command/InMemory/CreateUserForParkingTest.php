<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\CreateUserForParking;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\User as InMemoryUserFactory;
use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class CreateUserForParkingTest extends TestCase
{
    use DataSamplesGenerator;
    use DomainEventsRegister;

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
        $userName = 'newuser';
        $userEmail = 'newuser@test.com';
        $userPassword = 'userpassword';
        $userIsAdministrator = false;
        $userIsAdministratorForParking = true;

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $command = new CreateUserForParking($this->userRepository, new InMemoryUserFactory());
        $user = $command->execute(
            $this->loggedInUser,
            $this->parking,
            $userName,
            $userEmail,
            $userPassword,
            $userIsAdministrator,
            $userIsAdministratorForParking
        );

        $this->assertEquals(
            [
                User::EVENT_USER_CREATED,
                Parking::EVENT_USER_ADDED_TO_PARKING,
                Parking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING
            ],
            $this->recordedEventNames
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userName, $user->name());
        $this->assertEquals($userEmail, $user->email());
        $this->assertEquals($userIsAdministrator, $user->isAdministrator());
        $this->assertEquals($this->parking->isAdministeredByUser($user), $userIsAdministratorForParking);
    }
}
