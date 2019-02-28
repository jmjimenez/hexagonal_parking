<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\AssignUserToParking as AssignUserToParkingPayload;
use Jmj\Parking\Application\Command\Handler\AssignUserToParking;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use PHPUnit\Framework\TestCase;

class AssignUserToParkingTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingNotFound
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws UserNotFound
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testExecute()
    {
        //TODO: test wrong paths for all commands
        $this->createTestCase();

        $newUser = new User('New user', 'newuser@test.com', 'newuserpassword', false);
        $isAdministrator = true;
        $this->userRepository->save($newUser);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new AssignUserToParkingPayload(
            $this->userAdmin->uuid(),
            $newUser->uuid(),
            $this->parking->uuid(),
            $isAdministrator
        );

        $command = new AssignUserToParking(
            $this->parkingRepository,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals(
            [ Parking::EVENT_USER_ADDED_TO_PARKING, Parking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $this->assertEquals($isAdministrator, $parkingFound->isAdministeredByUser($newUser));
    }
}

