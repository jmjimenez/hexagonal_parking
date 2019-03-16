<?php

namespace Jmj\Parking\Infrastructure\Aggregate\InMemory;

use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot as DomainParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;

class ParkingSlot extends DomainParkingSlot
{
    /** @var array */
    protected $assignments;

    /** @var array */
    protected $reservations;

    /** @var array */
    protected $freeNotifications;

    public function __construct(Parking $parking, string $number, string $description)
    {
        $this->assignments = [];
        $this->reservations = [];
        $this->freeNotifications = [];

        parent::__construct($parking, $number, $description);
    }


    /**
     * @inheritdoc
     */
    protected function _isFreeForUserAndDay(User $user, DateTimeImmutable $date) : bool
    {
        foreach ($this->freeNotifications as $freeNotification) {
            if ($this->dateGreaterThanOrEqual($date, $freeNotification['fromDate'])
                && $this->dateLessThanOrEqual($date, $freeNotification['toDate'])
                && $user == $freeNotification['user']
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _markAsFreeFromUserAndPeriod(User $user, DateTimeImmutable $fromDate, DateTimeImmutable $toDate)
    {
        $this->freeNotifications[] = [
            'user' => $user,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function _removeFreeNotificationsForUser(User $user)
    {
        foreach ($this->freeNotifications as $index => $freeNotification) {
            if ($freeNotification['user'] == $user) {
                unset($this->freeNotifications[$index]);
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function _getAssignmentsForPeriod(DateTimeImmutable $fromDate, DateTimeImmutable $toDate) : array
    {
        $assignments = [];

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process($fromDate, $toDate, function ($date) use (&$assignments) {
            foreach ($this->assignments as $assignment) {
                /** @noinspection PhpUndefinedMethodInspection */
                if ($this->dateLessThanOrEqual($assignment['fromDate'], $date)
                    && $this->dateGreaterThanOrEqual($assignment['toDate'], $date)) {
                    $assignments[] = new Assignment($this, $assignment['user'], $date, $assignment['exclusive']);
                }
            }
        });

        return $assignments;
    }

    /**
     * @inheritdoc
     */
    protected function _assignToUserForPeriod(
        User $user,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate,
        bool $exclusive
    ) {
        $this->assignments[] = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'user' => $user,
            'exclusive' => $exclusive,
        ];
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function _removeAssignment(User $user, DateTimeImmutable $date)
    {
        foreach ($this->assignments as $index => $assignment) {
            if ($assignment['user'] != $user) {
                continue;
            }

            if ($this->dateGreaterThanOrEqual($assignment['fromDate'], $date)
                && $this->dateGreaterThanOrEqual($assignment['toDate'], $date)) {
                unset($this->assignments[$index]);
                continue;
            }

            if ($this->dateLessThanOrEqual($assignment['fromDate'], $date)
                && $this->dateGreaterThanOrEqual($assignment['toDate'], $date)) {
                $this->assignments[$index]['toDate'] = $this->decrementDate($date, 1);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function _removeAssignmensForUser(User $user)
    {
        foreach ($this->assignments as $index => $assignment) {
            if ($assignment['user'] == $user) {
                unset($this->assignments[$index]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function _reserveToUserForPeriod(User $user, DateTimeImmutable $fromDate, DateTimeImmutable $toDate)
    {
        $this->reservations[] = [
            'user' => $user,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function _getReservationsForPeriod(DateTimeImmutable $fromDate, DateTimeImmutable $toDate) : array
    {
        $reservations = [];

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process($fromDate, $toDate, function ($date) use (&$reservations) {
            foreach ($this->reservations as $reservation) {
                if ($this->dateLessThanOrEqual($reservation['fromDate'], $date)
                    && $this->dateGreaterThanOrEqual($reservation['toDate'], $date)) {
                    $reservations[] = new Reservation($this, $reservation['user'], $date);
                }
            }
        });

        return $reservations;
    }

    /**
     * @inheritdoc
     */
    protected function _removeReservationsForUser(User $user)
    {
        foreach ($this->reservations as $index => $reservation) {
            if ($reservation['user'] == $user) {
                unset($this->reservations[$index]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function _delete()
    {
    }
}
