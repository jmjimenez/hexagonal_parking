<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\AssignParkingSlotToUserForPeriod as AssignParkingSlotToUserForPeriodPayload;
use Jmj\Parking\Application\Command\Handler\AssignAdministratorRightsToUserForParking;
use Jmj\Parking\Application\Command\Handler\AssignParkingSlotToUserForPeriod;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingException;
use PHPUnit\Framework\TestCase;

class AssignParkingSlotToUserForPeriodTest extends TestCase
{
    use DomainEventsRegister;
    use DataSamplesGenerator;
    use NormalizeDate;

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
        //TODO: test wrong paths for all commands
        $this->createTestCase();

        $checkFromDate = new DateTimeImmutable('+1 days');
        $checkToDate = new DateTimeImmutable('+8 days');

        $assignFromDate = new DateTimeImmutable('+3 days');
        $assignToDate = new DateTimeImmutable('+5 days');
        $exclusive = true;

        $this->configureDomainEventsBroker();

        $this->startRecordingEvents();

        $payload = new AssignParkingSlotToUserForPeriodPayload(
            $this->userAdmin->uuid(),
            $this->userOne->uuid(),
            $this->parking->uuid(),
            $this->parkingSlotOne->uuid(),
            $assignFromDate,
            $assignToDate,
            $exclusive
        );

        $command = new AssignParkingSlotToUserForPeriod(
            $this->parkingRepository,
            $this->userRepository
        );
        $command->execute($payload);

        $this->assertEquals([ ParkingSlot::EVENT_PARKING_SLOT_ASSIGNED ], $this->recordedEventNames);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotNumber = $this->parkingSlotOne->number();

        $dateProcessor = new DateRangeProcessor();

        $dateProcessor->process(
            $checkFromDate,
            $checkToDate,
            function (DateTimeImmutable $date)
            use ($parkingFound, $parkingSlotNumber, $checkFromDate, $checkToDate, $assignFromDate, $assignToDate) {
                $assignments = $parkingFound->getParkingSlotsAssignmentsForDate($date);

                if ($this->dateInRange($date, $assignFromDate, $assignToDate)) {
                    $this->assertEquals(1, count($assignments[$parkingSlotNumber]));
                } else {
                    $this->assertEquals(0, count($assignments[$parkingSlotNumber]));
                }
            }
        );
    }
}

