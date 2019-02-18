<?php

namespace Jmj\Parking\Domain\Value;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;

class Reservation
{
    /** @var ParkingSlot  */
    private $parkingSlot;

    /** @var User */
    private $user;

    /** @var DateTimeImmutable */
    private $date;

    /**
     * Reservation constructor.
     * @param ParkingSlot $parkingSlot
     * @param User $user
     * @param DateTimeImmutable $day
     */
    public function __construct(ParkingSlot $parkingSlot, User $user, DateTimeImmutable $day)
    {
        $this->parkingSlot = $parkingSlot;
        $this->user = $user;
        $this->date = $day;
    }

    public function ParkingSlot(): ParkingSlot
    {
        return $this->parkingSlot;
    }

    public function user() : User
    {
        return $this->user;
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }
}