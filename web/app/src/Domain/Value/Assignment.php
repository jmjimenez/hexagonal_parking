<?php

namespace Jmj\Parking\Domain\Value;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;

class Assignment
{
    /**
     * @var ParkingSlot
     */
    private $parkingSlot;

    /**
     * @var User
     */
    private $user;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var bool
     */
    private $isExclusive;

    /**
     * Assignment constructor.
     *
     * @param  ParkingSlot       $parkingSlot
     * @param  User              $user
     * @param  DateTimeInterface $day
     * @param  bool              $exclusive
     * @throws \Exception
     */
    public function __construct(ParkingSlot $parkingSlot, User $user, DateTimeInterface $day, bool $exclusive)
    {
        $this->parkingSlot = $parkingSlot;
        $this->user = $user;
        $this->date = new DateTimeImmutable($day->format('Y-m-d'));
        $this->isExclusive = $exclusive;
    }

    public function parkingSlot() : ParkingSlot
    {
        return $this->parkingSlot;
    }

    public function user() : User
    {
        return $this->user;
    }

    public function date() : DateTimeImmutable
    {
        return $this->date;
    }

    public function isExclusive() : bool
    {
        return $this->isExclusive;
    }
}
