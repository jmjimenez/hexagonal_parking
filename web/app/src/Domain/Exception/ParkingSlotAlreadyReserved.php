<?php

namespace Jmj\Parking\Domain\Exception;

use DateTimeInterface;
use Jmj\Parking\Domain\Aggregate\User;

class ParkingSlotAlreadyReserved extends \Exception
{
    /**
     * @var DateTimeInterface
     */
    private $day;

    /**
     * @var User
     */
    private $user;

    /**
     * ParkingSlotAlreadyReserved constructor.
     *
     * @param DateTimeInterface $day
     * @param User              $user
     */
    public function __construct(DateTimeInterface $day, User $user)
    {
        $this->day = $day;
        $this->user = $user;

        parent::__construct('Parking is already reserved', 0, null);
    }
}
