<?php

namespace Jmj\Parking\Domain\Exception;

use DateTime;
use DateTimeInterface;
use Exception;
use Jmj\Parking\Domain\Aggregate\User;

class ParkingSlotAlreadyAssigned extends Exception
{
    /**
     * @var DateTime
     */
    private $day;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $exclusive;

    /**
     * @param DateTimeInterface $day
     * @param User              $user
     * @param bool              $exclusive
     */
    public function __construct(DateTimeInterface $day, User $user, bool $exclusive)
    {
        $this->day = $day;
        $this->user = $user;
        $this->exclusive = $exclusive;

        parent::__construct('Parking is already assigned', 0, null);
    }
}
