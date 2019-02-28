<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\GetParkingInformationForUserAndPeriod;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class GetParkingInformationForUserAndPeriodTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;
    use NormalizeDate;

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
        $fromDate = new DateTimeImmutable();
        $toDate = new DateTimeImmutable('+30 days');

        $assignFromDate =  new DateTimeImmutable('+3 days');
        $assignToDate =  new DateTimeImmutable('+20 days');

        $freeFromDate = new DateTimeImmutable('+5 days');
        $freeToDate = new DateTimeImmutable('+10 days');

        $this->createTestCase();
        $this->assignParkingSlotOneToUserOne($assignFromDate, $assignToDate, true);
        $this->freeParkingSlot($freeFromDate, $freeToDate);

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new GetParkingInformationForUserAndPeriod();
        $parkingInformation = $command->execute($this->parking, $this->userOne, $fromDate, $toDate);

        $this->assertEquals(0, count($parkingInformation['reservations']));

        /** @var Assignment $assigment */
        foreach ($parkingInformation['assignments'] as $assigment) {
            $this->assertTrue(
                $this->dateInRange($assigment->date(), $assignFromDate, $this->decrementDate($freeFromDate, 1))
                || $this->dateInRange($assigment->date(), $this->incrementDate($freeToDate, 1), $assignToDate)
            );
        }
    }
}
