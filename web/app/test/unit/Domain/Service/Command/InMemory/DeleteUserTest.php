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
use Jmj\Parking\Domain\Service\Command\DeleteUser;
use PHPUnit\Framework\TestCase;

class DeleteUserTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingException
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testExecute()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new DeleteUser();
        $command->execute($this->loggedInUser, $this->userOne);

        $this->assertEquals(
            [ User::EVENT_USER_DELETED ],
            $this->recordedEventNames
        );
    }
}
