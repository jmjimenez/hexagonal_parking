<?php

namespace Jmj\Parking\Domain\Aggregate;

use DateTimeImmutable;
use Exception;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Common\NormalizeDate;
use Jmj\Parking\Domain\Exception\ParkingSlotAlreadyAssigned;
use Jmj\Parking\Domain\Exception\ParkingSlotAlreadyReserved;
use Jmj\Parking\Domain\Exception\ParkingSlotDescriptionInvalid;
use Jmj\Parking\Domain\Exception\ParkingSlotNotAssignedToUser;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberInvalid;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;

abstract class ParkingSlot extends BaseAggregate
{
    use NormalizeDate;

    const EVENT_PARKING_SLOT_CREATED = 'ParkingSlotCreated';
    const EVENT_PARKING_SLOT_ASSIGNED = 'ParkingSlotAssigned';
    const EVENT_PARKING_SLOT_ASSIGNMENT_REMOVED = 'ParkingSlotAssignmentRemoved';
    const EVENT_PARKING_SLOT_MARKED_AS_FREE = 'ParkingSlotMarkedAsFree';
    const EVENT_PARKING_SLOT_RESERVED = 'ParkingSlotReserved';
    const EVENT_PARKING_SLOT_INFORMATION_UPDATED = 'ParkingSlotInformationUpdated';
    const EVENT_PARKING_SLOT_DELETED = 'ParkingSlotDeleted';
    const EVENT_USER_REMOVED_FROM_PARKING_SLOT = 'UserRemovedFromParkingSlot';

    /**
     * @var Parking
     */
    private $parking;

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $description;

    /**
     * @param  User              $user
     * @param  DateTimeImmutable $date
     * @return bool
     */
    abstract protected function _isFreeForUserAndDay(User $user, DateTimeImmutable $date): bool;

    //TODO: create new method removeFreeMarkForUserAndPeriod

    /**
     * @param User              $user
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     */
    abstract protected function _markAsFreeFromUserAndPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    );

    /**
     * @param User $user
     */
    abstract protected function _removeFreeNotificationsForUser(User $user);

    /**
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @return Assignment[]
     */
    abstract protected function _getAssignmentsForPeriod(DateTimeImmutable $fromDate, DateTimeImmutable $toDate);

    /**
     * @param User              $user
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @param bool              $exclusive
     */
    abstract protected function _assignToUserForPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate,
        bool $exclusive
    );

    /**
     * @param User              $user
     * @param DateTimeImmutable $date
     */
    abstract protected function _removeAssignment(User $user, DateTimeImmutable $date);

    /**
     * @param User $user
     */
    abstract protected function _removeAssignmensForUser(User $user);

    /**
     * @param User              $user
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     */
    abstract protected function _reserveToUserForPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    );

    /**
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @return Reservation[]
     */
    abstract protected function _getReservationsForPeriod(DateTimeImmutable $fromDate, DateTimeImmutable $toDate);

    /**
     * @param User $user
     */
    abstract protected function _removeReservationsForUser(User $user);

    /**
     *
     */
    abstract protected function _delete();

    /**
     * @param  Parking $parking
     * @param  string  $number
     * @param  string  $description
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function __construct(Parking $parking, string $number, string $description)
    {
        parent::__construct();

        $this->setParking($parking);
        $this->setNumber($number);
        $this->setDescription($description);

        $this->publishEvent(self::EVENT_PARKING_SLOT_CREATED);
    }

    /**
     * @return string
     */
    public function number() : string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function description() : string
    {
        return $this->description;
    }

    /**
     * @param  string $number
     * @param  string $description
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    public function updateInformation(string $number, string $description)
    {
        $currentNumber = $this->number;
        $currentDescription = $this->description;

        try {
            $this->setNumber($number);
        } catch (ParkingSlotNumberInvalid $e) {
            $this->number = $currentNumber;
            $this->description = $currentDescription;

            throw $e;
        }

        try {
            $this->setDescription($description);
        } catch (ParkingSlotDescriptionInvalid $e) {
            $this->number = $currentNumber;
            $this->description = $currentDescription;

            throw $e;
        }

        $this->publishEvent(self::EVENT_PARKING_SLOT_INFORMATION_UPDATED);
    }

    /**
     * @param  User              $user
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @param  bool              $exclusive
     * @return bool
     * @throws InvalidDateRange
     * @throws Exception
     */
    public function assignToUserForPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate,
        bool $exclusive
    ) :bool {
        if ($fromDate > $toDate) {
            throw new InvalidDateRange();
        }
        //TODO: if there is an assigment BUT there is also a free mark then the state is broken
        //TODO: create a new method to remove a free mark
        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($user, $exclusive) {
                $this->checkAssignToUserForDay($user, $exclusive, $date);
            }
        );

        $this->_assignToUserForPeriod($user, $fromDate, $toDate, $exclusive);

        $this->publishEvent(
            self::EVENT_PARKING_SLOT_ASSIGNED,
            [
                'user' => $user,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'exclusive' => $exclusive
            ]
        );

        return true;
    }

    /**
     * @param User              $user
     * @param DateTimeImmutable $date
     */
    public function removeAssigment(User $user, DateTimeImmutable $date)
    {
        $this->_removeAssignment($user, $date);

        $this->publishEvent(
            self::EVENT_PARKING_SLOT_ASSIGNMENT_REMOVED,
            [
                'user' => $user,
                'date' => $date
            ]
        );
    }

    /**
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @return Assignment[]
     */
    public function getAssignmentsForPeriod(DateTimeImmutable $fromDate, DateTimeImmutable $toDate)
    {
        //TODO: right now if an assigment is marked as free, it is not returned it may be misleading
        //      perhaps it should return both the real assigment and the free marks
        $regularAssigments = $this->_getAssignmentsForPeriod($fromDate, $toDate);

        $definiteAssignments = [];

        /**
         * @var Assignment $assigment
         */
        foreach ($regularAssigments as $assigment) {
            if (!$this->_isFreeForUserAndDay($assigment->user(), $assigment->date())) {
                $definiteAssignments[] = $assigment;
            }
        }

        return $definiteAssignments;
    }

    /**
     * @param  User              $user
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @throws Exception
     */
    public function markAsFreeFromUserAndPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($user) {
                $this->checkMarkAsFreeFromUserAndDay($user, $date);
            }
        );

        $this->_markAsFreeFromUserAndPeriod($user, $fromDate, $toDate);

        $this->publishEvent(
            self::EVENT_PARKING_SLOT_MARKED_AS_FREE,
            [
                'user' => $user,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ]
        );
    }

    /**
     * @param  User              $user
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @throws Exception
     */
    public function reserveToUserForPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use ($user) {
                $this->checkReserveToUserForDay($user, $date);
            }
        );

        $this->_reserveToUserForPeriod($user, $fromDate, $toDate);

        $this->publishEvent(
            self::EVENT_PARKING_SLOT_RESERVED,
            [
                'user' => $user,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ]
        );
    }

    /**
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @return Reservation[]
     */
    public function getReservationsForPeriod(
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        return $this->_getReservationsForPeriod($fromDate, $toDate);
    }

    /**
     *
     */
    public function delete()
    {
        //TODO: it would be nice to remove all assigments and reservations this way users will be updated
        $this->_delete();

        $this->publishEvent(self::EVENT_PARKING_SLOT_DELETED);
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->_removeReservationsForUser($user);
        $this->_removeAssignmensForUser($user);
        $this->_removeFreeNotificationsForUser($user);

        //TODO: this event should only be triggered if the user has been really removed from parking slot
        $this->publishEvent(self::EVENT_USER_REMOVED_FROM_PARKING_SLOT, $user);
    }

    /**
     * @param  DateTimeImmutable $date
     * @return bool
     */
    public function isFreeForDate(DateTimeImmutable $date) : bool
    {
        $assignments = $this->_getAssignmentsForPeriod($date, $date);

        foreach ($assignments as $assignment) {
            if (!$this->_isFreeForUserAndDay($assignment->user(), $date)) {
                return false;
            }
        }

        $reservations = $this->getReservationsForPeriod($date, $date);

        if (count($reservations) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getClassName() : string
    {
        return __CLASS__;
    }

    /**
     * @param  User              $user
     * @param  bool              $exclusive
     * @param  DateTimeImmutable $date
     * @throws ParkingSlotAlreadyAssigned
     * @throws ParkingSlotAlreadyReserved
     */
    private function checkAssignToUserForDay(User $user, bool $exclusive, DateTimeImmutable $date)
    {
        $assignments = $this->_getAssignmentsForPeriod($date, $date);

        foreach ($assignments as $assignment) {
            if ($assignment->user()->uuid() == $user->uuid()) {
                continue;
            }

            if ($this->_isFreeForUserAndDay($assignment->user(), $date)) {
                continue;
            }

            if ($assignment->isExclusive() || $exclusive) {
                throw new ParkingSlotAlreadyAssigned($date, $assignment->user(), $assignment->isExclusive());
            }
        }

        $reservations = $this->getReservationsForPeriod($date, $date);

        foreach ($reservations as $reservation) {
            if ($reservation->user()->uuid() != $user->uuid()) {
                throw new ParkingSlotAlreadyReserved($date, $reservations[0]->user());
            }
        }
    }

    /**
     * @param  User              $user
     * @param  DateTimeImmutable $date
     * @throws ParkingSlotNotAssignedToUser
     */
    private function checkMarkAsFreeFromUserAndDay(User $user, DateTimeImmutable $date)
    {
        $assignments = $this->_getAssignmentsForPeriod($date, $date);
        $assignedToUser = false;

        foreach ($assignments as $assignment) {
            if ($assignment->user()->uuid() == $user->uuid()) {
                $assignedToUser = true;
                continue;
            }
        }

        if (!$assignedToUser) {
            throw new ParkingSlotNotAssignedToUser($date, $user);
        }
    }

    /**
     * @param  User              $user
     * @param  DateTimeImmutable $date
     * @throws ParkingSlotAlreadyAssigned
     * @throws ParkingSlotAlreadyReserved
     */
    private function checkReserveToUserForDay(User $user, DateTimeImmutable $date)
    {
        $assignments = $this->_getAssignmentsForPeriod($date, $date);

        foreach ($assignments as $assignment) {
            if ($this->_isFreeForUserAndDay($assignment->user(), $date)) {
                continue;
            }

            throw new ParkingSlotAlreadyAssigned($date, $assignment->user(), $assignment->isExclusive());
        }

        $reservations = $this->getReservationsForPeriod($date, $date);

        foreach ($reservations as $reservation) {
            if ($reservation->user()->uuid() != $user->uuid()) {
                throw new ParkingSlotAlreadyReserved($date, $reservations[0]->user());
            }
        }
    }

    /**
     * @param Parking $parking
     */
    private function setParking(Parking $parking)
    {
        $this->parking = $parking;
    }

    /**
     * @param  string $number
     * @throws ParkingSlotNumberInvalid
     */
    private function setNumber(string $number)
    {
        if ($number == '') {
            throw new ParkingSlotNumberInvalid();
        }

        $this->number = $number;
    }

    /**
     * @param  string $description
     * @throws ParkingSlotDescriptionInvalid
     */
    private function setDescription(string $description)
    {
        if ($description == '') {
            throw new ParkingSlotDescriptionInvalid();
        }

        $this->description = $description;
    }
}
