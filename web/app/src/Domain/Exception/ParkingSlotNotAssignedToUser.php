<?php

namespace Jmj\Parking\Domain\Exception;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\User;

class ParkingSlotNotAssignedToUser extends \Exception
{
    /** @var DateTimeImmutable */
    private $day;

    /** @var User */
    private $user;

    /**
     * ParkingSlotNotAssignedToUser constructor.
     * @param DateTimeImmutable $day
     * @param User $user
     */
    public function __construct(DateTimeImmutable $day, User $user)
    {
        $this->day = $day;
        $this->user = $user;

        parent::__construct('Parking is not assigned to user', 0, null);
    }
}