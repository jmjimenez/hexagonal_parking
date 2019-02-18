<?php

namespace Jmj\Parking\Application\Command;

use DateTime;

class GetParkingSlotsReservationsForDate
{
    /** @var int  */
    private $userId;

    /** @var int  */
    private $parkingId;

    /** @var DateTime  */
    private $date;

    /**
     * AdministratorCreatesParkingSlot constructor.
     * @param int $userId
     * @param int $parkingId
     * @param DateTime $date
     */
    public function __construct(
        int $userId,
        int $parkingId,
        DateTime $date
    ) {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function userId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function parkingId(): int
    {
        return $this->parkingId;
    }

    /**
     * @return DateTime
     */
    public function date(): DateTime
    {
        return $this->date;
    }
}