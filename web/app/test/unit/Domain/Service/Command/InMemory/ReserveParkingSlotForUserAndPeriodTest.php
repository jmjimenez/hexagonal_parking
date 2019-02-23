<?php

namespace Jmj\Test\Unit\Domain\Service\Command\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Service\Command\ReserveParkingSlotForUserAndPeriod;
use Jmj\Parking\Infrastructure\Aggregate\Event\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class ReserveParkingSlotForUserAndPeriodTest extends TestCase
{
    use DomainEventsRegister;
    use NormalizeDate;
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
        $checkFromDate =  new DateTimeImmutable('+1 days');
        $checkToDate =  new DateTimeImmutable('+20 days');

        $reserveFromDate =  new DateTimeImmutable('+5 days');
        $reserveToDate =  new DateTimeImmutable('+10 days');

        $this->createTestCase();

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();
        $command = new ReserveParkingSlotForUserAndPeriod();
        $command->execute(
            $this->parking,
            $this->loggedInUser,
            $this->parkingSlotOne->uuid(),
            $reserveFromDate,
            $reserveToDate
        );

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_RESERVED ], $this->recordedEventNames);

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function(DateTimeImmutable $date) use ($checkFromDate, $checkToDate, $reserveFromDate, $reserveToDate) {
                if ($this->dateInRange($date, $reserveFromDate, $reserveToDate)) {
                    $this->assertFalse($this->parkingSlotOne->isFreeForDate($date));
                } else{
                    $this->assertTrue($this->parkingSlotOne->isFreeForDate($date));
                }
            }
        );
    }
}

