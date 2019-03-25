<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\GetUserInformation;
use Jmj\Parking\Common\EventsRecorder;
use PHPUnit\Framework\TestCase;

class GetUserInformationTest extends TestCase
{
    use EventsRecorder;
    use Common\DataSamplesGenerator;
    use NormalizeDate;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testExecute()
    {
        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new GetUserInformation();

        $userInformation = $command->execute($this->loggedInUser, $this->userOne);

        $expectedResult = [
            'uuid' => $this->userOne->uuid(),
            'name' => $this->userOne->name(),
            'email' => $this->userOne->email(),
            'isAdministrator' => $this->userOne->isAdministrator(),
        ];

        $this->assertEquals($expectedResult, $userInformation);
    }
}
