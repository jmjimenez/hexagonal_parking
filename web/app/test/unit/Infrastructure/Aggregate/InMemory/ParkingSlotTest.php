<?php

namespace Jmj\Test\Unit\Infrastructure\Aggregate\InMemory;

use DateTimeImmutable;
use DateTimeInterface;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Common\DomainEventsRegister;
use Jmj\Parking\Common\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotAlreadyAssigned;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotAlreadyReserved;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotDescriptionInvalid;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotAssignedToUser;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNumberInvalid;
use Jmj\Parking\Domain\Aggregate\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Aggregate\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Aggregate\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Aggregate\ParkingSlot as DomainParkingSlot;
use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\ParkingSlot;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use PHPUnit\Framework\TestCase;

class ParkingSlotTest extends TestCase
{
    use DomainEventsRegister;

    /** @var Parking */
    private $parking;

    /** @var string */
    private $parkingSlotNumber;

    /** @var string */
    private $parkingSlotDescription;

    /**
     *
     */
    protected function setUp()
    {
        $this->getEventBroker()->resetSubscriptions();

        $this->parking = new Parking('Parking');
        $this->parkingSlotNumber = '22';
        $this->parkingSlotDescription = 'Parking Slot 22';
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    public function testCreateParkingSlot()
    {
        $this->startRecordingEvents();
        $parkingSlot = $this->createParkingSlot();

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_CREATED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);

        $this->assertInstanceOf(ParkingSlot::class, $parkingSlot);
        $this->assertNotEmpty($parkingSlot->uuid());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    public function testNumber()
    {
        $parkingSlot = $this->createParkingSlot();

        $this->assertEquals($this->parkingSlotNumber, $parkingSlot->number());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    public function testDescription()
    {
        $parkingSlot = $this->createParkingSlot();

        $this->assertEquals($this->parkingSlotDescription, $parkingSlot->description());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    public function testUpdateInformation()
    {
        $newParkingSlotNumber = '23';
        $newParkingSlotDescription = 'Parking Slot 23';

        $parkingSlot = $this->createParkingSlot();

        $this->startRecordingEvents();
        $parkingSlot->updateInformation($newParkingSlotNumber, $newParkingSlotDescription);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_INFORMATION_UPDATED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);

        $this->assertEquals($newParkingSlotNumber, $parkingSlot->number());
        $this->assertEquals($newParkingSlotDescription, $parkingSlot->description());
    }

    /**
     * @throws ParkingSlotNumberInvalid
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     */
    public function testUpdateInformationErrorWhenNumberIsInvalid()
    {
        $newParkingSlotNumber = '';
        $newParkingSlotDescription = 'Parking Slot 23';

        $parkingSlot = $this->createParkingSlot();

        $this->startRecordingEvents();
        $this->expectException(ParkingSlotNumberInvalid::class);
        $parkingSlot->updateInformation($newParkingSlotNumber, $newParkingSlotDescription);

        $this->assertEquals([], $this->recordedEventNames);

        $this->assertEquals($this->parkingSlotNumber, $parkingSlot->number());
        $this->assertEquals($this->parkingSlotDescription, $parkingSlot->description());
    }

    /**
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUpdateInformationErrorWhenDescriptionIsInvalid()
    {
        $newParkingSlotNumber = '23';
        $newParkingSlotDescription = '';

        $parkingSlot = $this->createParkingSlot();

        $this->startRecordingEvents();
        $this->expectException(ParkingSlotDescriptionInvalid::class);
        $parkingSlot->updateInformation($newParkingSlotNumber, $newParkingSlotDescription);

        $this->assertEquals([ ], $this->recordedEventNames);

        $this->assertEquals($this->parkingSlotNumber, $parkingSlot->number());
        $this->assertEquals($this->parkingSlotDescription, $parkingSlot->description());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriod()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $fromDate = new DateTimeImmutable();
        $toDate = new DateTimeImmutable('+7 days');
        $isExclusive = true;

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process($fromDate, $toDate, function ($date) use ($parkingSlot) {
            $assignments = $parkingSlot->getAssignmentsForPeriod($date, $date);

            $this->assertEquals(0, count($assignments));
        });

        $this->startRecordingEvents();
        $parkingSlot->assignToUserForPeriod($user, $fromDate, $toDate, $isExclusive);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_ASSIGNED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals(
            [
                [ 'user' => $user, 'fromDate' => $fromDate, 'toDate' => $toDate, 'exclusive' => $isExclusive ]
            ],
            $this->recordedPayloads
        );

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user, $isExclusive)
            {
                $assignments = $parkingSlot->getAssignmentsForPeriod($date, $date);

                $this->assertEquals(1, count($assignments));
                $this->assertAssigment($assignments[0], $parkingSlot, $user, $date, $isExclusive);
            }
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodWhenParkingSlotIsAlreadyAssignedToSameUser()
    {
        $parkingSlot = $this->createParkingSlot();

        $user = $this->createUser('user1');

        $date1 = new DateTimeImmutable('');
        $date2 = new DateTimeImmutable('+3 days');
        $date3 = new DateTimeImmutable('+7 days');

        $isExclusive = true;

        $parkingSlot->assignToUserForPeriod($user, $date1, $date3, $isExclusive);

        $this->startRecordingEvents();
        $parkingSlot->assignToUserForPeriod($user, $date2, $date3, $isExclusive);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_ASSIGNED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals(
            [
                [ 'user' => $user, 'fromDate' => $date2, 'toDate' => $date3, 'exclusive' => $isExclusive ]
            ],
            $this->recordedPayloads
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodWhenParkingSlotIsAssignedToOtherUserNotExclusive()
    {
        $parkingSlot = $this->createParkingSlot();

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $date1 = new DateTimeImmutable('');
        $date2 = new DateTimeImmutable('+3 days');
        $date3 = new DateTimeImmutable('+7 days');

        $isExclusive = false;

        $parkingSlot->assignToUserForPeriod($user1, $date1, $date3, $isExclusive);

        $this->startRecordingEvents();
        $parkingSlot->assignToUserForPeriod($user2, $date2, $date3, $isExclusive);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_ASSIGNED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals(
            [
                [ 'user' => $user2, 'fromDate' => $date2, 'toDate' => $date3, 'exclusive' => $isExclusive ]
            ],
            $this->recordedPayloads
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodErrorWhenParkingSlotIsAssignedToOtherUserButExclusiveConflicts()
    {
        $parkingSlot = $this->createParkingSlot();

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $date1 = new DateTimeImmutable('');
        $date2 = new DateTimeImmutable('+3 days');
        $date3 = new DateTimeImmutable('+7 days');

        $isExclusive1 = false;
        $isExclusive2 = true;

        $parkingSlot->assignToUserForPeriod($user1, $date1, $date3, $isExclusive1);

        $this->startRecordingEvents();
        $this->expectException(ParkingSlotAlreadyAssigned::class);
        $parkingSlot->assignToUserForPeriod($user2, $date2, $date3, $isExclusive2);

        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodWhenParkingSlotIsAssignedToOtherUserButMarkedAsFree()
    {
        $parkingSlot = $this->createParkingSlot();

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $date1 = new DateTimeImmutable('');
        $date2 = new DateTimeImmutable('+3 days');
        $date3 = new DateTimeImmutable('+7 days');

        $isExclusive = true;

        $parkingSlot->assignToUserForPeriod($user1, $date1, $date3, $isExclusive);
        $parkingSlot->markAsFreeFromUserAndPeriod($user1, $date2, $date3);

        $this->startRecordingEvents();
        $parkingSlot->assignToUserForPeriod($user2, $date2, $date3, $isExclusive);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_ASSIGNED ], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodErrorWhenParkingIsReserved()
    {
        $parkingSlot = $this->createParkingSlot();

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $date1 = new DateTimeImmutable('');
        $date2 = new DateTimeImmutable('+3 days');
        $date3 = new DateTimeImmutable('+7 days');

        $isExclusive = true;

        $parkingSlot->reserveToUserForPeriod($user1, $date1, $date3);

        $this->startRecordingEvents();
        $this->expectException(ParkingSlotAlreadyReserved::class);
        $parkingSlot->assignToUserForPeriod($user2, $date2, $date3, $isExclusive);

        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodErrorWhenParkingSlotIsAlreadyAssigned()
    {
        $parkingSlot = $this->createParkingSlot();

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $date1 = new DateTimeImmutable('');
        $date2 = new DateTimeImmutable('+3 days');
        $date3 = new DateTimeImmutable('+7 days');

        $isExclusive = true;

        $parkingSlot->assignToUserForPeriod($user1, $date1, $date3, $isExclusive);

        $this->expectException(ParkingSlotAlreadyAssigned::class);
        $parkingSlot->assignToUserForPeriod($user2, $date2, $date3, $isExclusive);

        $this->startRecordingEvents();
        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testAssignToUserForPeriodErrorWhenInvalidPeriod()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $fromDate = new DateTimeImmutable('+7 days');
        $toDate = new DateTimeImmutable('');
        $isExclusive = true;

        $this->startRecordingEvents();
        $this->expectException(InvalidDateRange::class);
        $parkingSlot->assignToUserForPeriod($user, $fromDate, $toDate, $isExclusive);
        $this->assertEquals([], $this->recordedEventNames);
        $this->assertEquals([], $parkingSlot->getAssignmentsForPeriod($toDate, $fromDate));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws InvalidDateRange
     * @throws \Exception
     */
    public function testRemoveAssigment()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $fromDate = new DateTimeImmutable();
        $toDate = new DateTimeImmutable('+7 days');
        $isExclusive = true;

        $removeAssignmentDate =  new DateTimeImmutable('+3 day');

        $parkingSlot->assignToUserForPeriod($user, $fromDate, $toDate, $isExclusive);

        $dateRangeProcessor = new DateRangeProcessor();
        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user, $isExclusive)
            {
                $assignments = $parkingSlot->getAssignmentsForPeriod($date, $date);

                $this->assertEquals(1, count($assignments));
                $this->assertAssigment($assignments[0], $parkingSlot, $user, $date, $isExclusive);
            }
        );

        $this->startRecordingEvents();
        $parkingSlot->removeAssigment($user, $removeAssignmentDate);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_ASSIGNMENT_REMOVED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);

        $dateRangeProcessor = new DateRangeProcessor();
        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user, $isExclusive, $removeAssignmentDate)
            {
                $assignments = $parkingSlot->getAssignmentsForPeriod($date, $date);

                if ($this->normalizeDate($date) >= $this->normalizeDate($removeAssignmentDate)) {
                    $this->assertEquals(0, count($assignments));
                } else {
                    $this->assertEquals(1, count($assignments));
                    $this->assertAssigment($assignments[0], $parkingSlot, $user, $date, $isExclusive);
                }
            }
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws InvalidDateRange
     * @throws \Exception
     */
    public function testMarkAsFreeFromUserAndPeriod()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $assignFromDate = new DateTimeImmutable();
        $assignToDate = new DateTimeImmutable('+7 days');
        $isExclusive = true;

        $markAsFreeFromDate = new DateTimeImmutable('+1 day');
        $markAsFreeToDate = new DateTimeImmutable('+2 days');

        $parkingSlot->assignToUserForPeriod($user, $assignFromDate, $assignToDate, $isExclusive);

        $this->startRecordingEvents();
        $parkingSlot->markAsFreeFromUserAndPeriod($user, $markAsFreeFromDate, $markAsFreeToDate);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_MARKED_AS_FREE ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals(
            [
                [ 'user' => $user, 'fromDate' => $markAsFreeFromDate, 'toDate' => $markAsFreeToDate ]
            ],
            $this->recordedPayloads
        );

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process(
            $assignFromDate,
            $assignToDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user, $isExclusive, $markAsFreeFromDate, $markAsFreeToDate)
            {
                $assignments = $parkingSlot->getAssignmentsForPeriod($date, $date);

                if ($this->inRange($date, $markAsFreeFromDate, $markAsFreeToDate)) {
                    $this->assertEquals(0, count($assignments));
                } else {
                    $this->assertEquals(1, count($assignments));
                    $this->assertAssigment($assignments[0], $parkingSlot, $user, $date, $isExclusive);
                }
            }
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testMarkAsFreeFromUserAndPeriodErrorWhenNotAssigned()
    {
        $parkingSlot = $this->createParkingSlot();
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $assignFromDate = new DateTimeImmutable();
        $assignToDate = new DateTimeImmutable('+7 days');
        $isExclusive = true;

        $markAsFreeFromDate = new DateTimeImmutable('+1 day');
        $markAsFreeToDate = new DateTimeImmutable('+3 days');

        $parkingSlot->assignToUserForPeriod($user1, $assignFromDate, $assignToDate, $isExclusive);

        $this->startRecordingEvents();
        $this->expectException(ParkingSlotNotAssignedToUser::class);
        $parkingSlot->markAsFreeFromUserAndPeriod($user2, $markAsFreeFromDate, $markAsFreeToDate);

        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testReserveToUserForPeriod()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $fromDate = new DateTimeImmutable();
        $toDate = new DateTimeImmutable('+7 days');

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process($fromDate, $toDate, function ($date) use ($parkingSlot) {
            $reservations = $parkingSlot->getReservationsForPeriod($date, $date);

            $this->assertEquals(0, count($reservations));
        });

        $this->startRecordingEvents();
        $parkingSlot->reserveToUserForPeriod($user, $fromDate, $toDate);

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_RESERVED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals(
            [
                [ 'user' => $user, 'fromDate' => $fromDate, 'toDate' => $toDate ]
            ],
            $this->recordedPayloads
        );

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user)
            {
                $reservations = $parkingSlot->getReservationsForPeriod($date, $date);

                $this->assertEquals(1, count($reservations));
                $this->assertReservation($reservations[0], $parkingSlot, $user, $date);
            }
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testReserveToUserForPeriodWhenIsMarkedAsFree()
    {
        $parkingSlot = $this->createParkingSlot();
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $assignFromDate = new DateTimeImmutable();
        $assignToDate = new DateTimeImmutable('+7 days');
        $exclusive = true;

        $reserveFromDate = new DateTimeImmutable('+3 days');
        $reserveToDate = new DateTimeImmutable('+4 days');

        $parkingSlot->assignToUserForPeriod($user1, $assignFromDate, $assignToDate, $exclusive);
        $parkingSlot->markAsFreeFromUserAndPeriod($user1, $reserveFromDate, $reserveToDate);

        $this->startRecordingEvents();
        $parkingSlot->reserveToUserForPeriod($user2, $reserveFromDate, $reserveToDate);
        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_RESERVED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals(
            [ [ 'user' => $user2, 'fromDate' => $reserveFromDate, 'toDate' => $reserveToDate ] ],
            $this->recordedPayloads
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testReserveToUserForPeriodErrorWhenParkingIsAlreadyAssigned()
    {
        $parkingSlot = $this->createParkingSlot();
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $assignFromDate = new DateTimeImmutable();
        $assignToDate = new DateTimeImmutable('+7 days');
        $exclusive = true;

        $reserveFromDate = new DateTimeImmutable('+3 days');
        $reserveToDate = new DateTimeImmutable('+9 days');

        $parkingSlot->assignToUserForPeriod($user1, $assignFromDate, $assignToDate, $exclusive);

        $this->expectException(ParkingSlotAlreadyAssigned::class);
        $this->startRecordingEvents();
        $parkingSlot->reserveToUserForPeriod($user2, $reserveFromDate, $reserveToDate);
        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testReserveToUserForPeriodErrorWhenParkingIsAlreadyReserved()
    {
        $parkingSlot = $this->createParkingSlot();
        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $reserve1FromDate = new DateTimeImmutable();
        $reserve1ToDate = new DateTimeImmutable('+7 days');

        $reserve2FromDate = new DateTimeImmutable('+3 days');
        $reserve2ToDate = new DateTimeImmutable('+9 days');

        $parkingSlot->reserveToUserForPeriod($user1, $reserve1FromDate, $reserve1ToDate);

        $this->expectException(ParkingSlotAlreadyReserved::class);
        $this->startRecordingEvents();
        $parkingSlot->reserveToUserForPeriod($user2, $reserve2FromDate, $reserve2ToDate);
        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testRemoveUser()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $fromDate = new DateTimeImmutable();
        $toDate = new DateTimeImmutable('+7 days');

        $parkingSlot->reserveToUserForPeriod($user, $fromDate, $toDate);

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user)
            {
                $reservations = $parkingSlot->getReservationsForPeriod($date, $date);

                $this->assertEquals(1, count($reservations));
            }
        );

        $this->startRecordingEvents();
        $parkingSlot->removeUser($user);

        $this->assertEquals([ DomainParkingSlot::EVENT_USER_REMOVED_FROM_PARKING_SLOT ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
        $this->assertEquals([ $user ], $this->recordedPayloads);

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($parkingSlot, $user)
            {
                $reservations = $parkingSlot->getReservationsForPeriod($date, $date);

                $this->assertEquals(0, count($reservations));
            }
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    public function testDelete()
    {
        $parkingSlot = $this->createParkingSlot();

        $this->startRecordingEvents();
        $parkingSlot->delete();

        $this->assertEquals([ DomainParkingSlot::EVENT_PARKING_SLOT_DELETED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParkingSlot::class ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot ], $this->recordedObjects);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testIsFreeForDate()
    {
        $parkingSlot = $this->createParkingSlot();
        $user = $this->createUser();

        $this->assertTrue($parkingSlot->isFreeForDate(new DateTimeImmutable()));

        $assignFromDate = new DateTimeImmutable('+3 days');
        $assignToDate = new DateTimeImmutable('+6 days');
        $parkingSlot->assignToUserForPeriod($user, $assignFromDate, $assignToDate, true);

        $reserveFromDate = new DateTimeImmutable('+7 days');
        $reserveToDate = new DateTimeImmutable('+12 days');
        $parkingSlot->reserveToUserForPeriod($user, $reserveFromDate, $reserveToDate);

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process(
            new DateTimeImmutable(),
            new DateTimeImmutable('+ 14 days'),
            function (DateTimeImmutable $date)
            use ($parkingSlot, $assignFromDate, $assignToDate, $reserveFromDate, $reserveToDate) {
                if ($this->inRange($date, $assignFromDate, $assignToDate)) {
                    $isFree = false;
                } elseif ($this->inRange($date, $reserveFromDate, $reserveToDate)) {
                    $isFree = false;
                } else {
                    $isFree = true;
                }

                $this->assertEquals($isFree, $parkingSlot->isFreeForDate($date));
            }
        );
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testGetReservationsForPeriod()
    {
        $parkingSlot = $this->createParkingSlot();

        $reservations = [
            [
                'user' => new User('User 1', 'user1@test.com', 'password', false),
                'fromDate' => new DateTimeImmutable('+1 day'),
                'toDate' => new DateTimeImmutable('+5 days'),
            ],
            [
                'user' => new User('User 2', 'user2@test.com', 'password', false),
                'fromDate' => new DateTimeImmutable('+6 days'),
                'toDate' => new DateTimeImmutable('+11 days'),
            ],
        ];

        foreach ($reservations as $reservation) {
            $parkingSlot->reserveToUserForPeriod($reservation['user'], $reservation['fromDate'], $reservation['toDate']);
        }

        $parkingSlotReservations = $parkingSlot->getReservationsForPeriod(
            new DateTimeImmutable(),
            new DateTimeImmutable('+12 days')
        );

        $this->assertEquals(11, count($parkingSlotReservations));

        foreach ($parkingSlotReservations as $reservation) {
            if ($this->inRange($reservation->date(), $reservations[0]['fromDate'], $reservations[0]['toDate'])) {
                $this->assertEquals($reservations[0]['user'], $reservation->user());
            }

            if ($this->inRange($reservation->date(), $reservations[1]['fromDate'], $reservations[1]['toDate'])) {
                $this->assertEquals($reservations[1]['user'], $reservation->user());
            }
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws InvalidDateRange
     * @throws \Exception
     */
    public function testGetAssignmentsForPeriod()
    {
        $parkingSlot = $this->createParkingSlot();

        $assignments = [
            [
                'user' => $this->createUser('User1'),
                'fromDate' => new DateTimeImmutable('+1 day'),
                'toDate' => new DateTimeImmutable('+5 days'),
                'exclusive' => true,
            ],
            [
                'user' => $this->createUser('User2'),
                'fromDate' => new DateTimeImmutable('+6 days'),
                'toDate' => new DateTimeImmutable('+11 days'),
                'exclusive' => true,
            ],
        ];

        foreach ($assignments as $assignment) {
            $parkingSlot->assignToUserForPeriod(
                $assignment['user'],
                $assignment['fromDate'],
                $assignment['toDate'],
                $assignment['exclusive']
            );
        }

        $parkingSlotAssignments = $parkingSlot->getAssignmentsForPeriod(
            new DateTimeImmutable(),
            new DateTimeImmutable('+12 days')
        );

        $this->assertEquals(11, count($parkingSlotAssignments));

        foreach ($parkingSlotAssignments as $assignment) {
            if ($this->inRange($assignment->date(), $assignments[0]['fromDate'], $assignments[0]['toDate'])) {
                $this->assertEquals($assignments[0]['user'], $assignment->user());
            }

            if ($this->inRange($assignment->date(), $assignments[1]['fromDate'], $assignments[1]['toDate'])) {
                $this->assertEquals($assignments[1]['user'], $assignment->user());
            }
        }
    }

    /**
     * @return ParkingSlot
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws ExceptionGeneratingUuid
     */
    protected function createParkingSlot() : ParkingSlot
    {
        return new ParkingSlot($this->parking, $this->parkingSlotNumber, $this->parkingSlotDescription);
    }

    /**
     * @param string $userName
     * @return DomainUser
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    protected function createUser(string $userName = 'User1'): DomainUser
    {
        $name = $userName;
        $email = sprintf('%s@test.com', $userName);
        $password = sprintf('Password%s', $userName);
        $isAdministrator = false;

        return new User($name, $email, $password, $isAdministrator);
    }

    /**
     * @param Assignment $assignment
     * @param ParkingSlot $parkingSlot
     * @param DomainUser $user
     * @param DateTimeInterface $date
     * @param bool $isExclusive
     */
    protected function assertAssigment(
        Assignment $assignment,
        ParkingSlot $parkingSlot,
        DomainUser $user,
        DateTimeInterface $date,
        bool $isExclusive
    ) {
        $this->assertInstanceOf(Assignment::class, $assignment);
        $this->assertEquals($parkingSlot, $assignment->ParkingSlot());
        $this->assertEquals($user, $assignment->user());
        $this->assertEquals($this->normalizeDate($date), $this->normalizeDate($assignment->date()));
        $this->assertEquals($isExclusive, $assignment->isExclusive());
    }

    /**
     * @param Reservation $reservation
     * @param ParkingSlot $parkingSlot
     * @param DomainUser $user
     * @param DateTimeInterface $date
     */
    protected function assertReservation(
        Reservation $reservation,
        ParkingSlot $parkingSlot,
        DomainUser $user,
        DateTimeInterface $date
    ) {
        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals($parkingSlot, $reservation->ParkingSlot());
        $this->assertEquals($user, $reservation->user());
        $this->assertEquals($this->normalizeDate($date), $this->normalizeDate($reservation->date()));
    }

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function normalizeDate(DateTimeInterface $date): string
    {
       return $date->format('Y-m-d');
    }

    /**
     * @param DateTimeInterface $date
     * @param DateTimeInterface $fromDate
     * @param DateTimeInterface $toDate
     * @return bool
     */
    protected function inRange(DateTimeInterface $date, DateTimeInterface $fromDate, DateTimeInterface $toDate): bool
    {
        return $this->normalizeDate($date) >= $this->normalizeDate($fromDate)
            && $this->normalizeDate($date) <= $this->normalizeDate($toDate);
    }
}

