<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use Jmj\Parking\Application\Command\CreateUserForParking as CreateUserForParkingPayload;
use Jmj\Parking\Application\Command\Handler\CreateUserForParking;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Infrastructure\Service\Factory\InMemory\User as InMemoryUserFactory;
use PHPUnit\Framework\TestCase;

class CreateUserForParkingTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;
    use Common\AssertSqlStatements;

    /**
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws \Exception
     */
    public function testExecute()
    {
        $this->createTestCase();

        $userName = 'New user';
        $userEmail = 'newuser@test.com';
        $userPassword = 'newUserPassword';
        $isAdministrator = false;
        $isParkingAdministrator = false;

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new CreateUserForParkingPayload(
            $this->userAdmin->uuid(),
            $this->parking->uuid(),
            $userName,
            $userEmail,
            $userPassword,
            $isAdministrator,
            $isParkingAdministrator
        );

        $command = new CreateUserForParking(
            $this->userRepository,
            new InMemoryUserFactory(),
            $this->parkingRepository
        );
        $newUser = $command->execute($payload);

        $this->assertEquals(
            [ User::EVENT_USER_CREATED, Parking::EVENT_USER_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );

        $userFound = $this->userRepository->findByEmail($userEmail);

        $this->assertInstanceOf(User::class, $userFound);
        $this->assertEquals($userName, $userFound->name());
        $this->assertEquals($userEmail, $userFound->email());
        $this->assertTrue($userFound->checkPassword($userPassword));
        $this->assertEquals($isAdministrator, $userFound->isAdministrator());
        $this->assertEquals($isParkingAdministrator, $this->parking->isAdministeredByUser($userFound));
        $this->assertEquals($newUser->uuid(), $userFound->uuid());

        $this->assertEquals(2, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'Parking', ['uuid' => $this->parking->uuid()]);
        $this->assertInsert($this->recordedSqlStatements[1], 'User', [ 'uuid' => $newUser->uuid() ]);
    }
}
