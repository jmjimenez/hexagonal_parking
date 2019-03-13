<?php

namespace Jmj\Parking\Application\Command;

use DateTime;

class ReserveParkingSlotToUser
{
    /** @var int  */
    private $userId;

    /** @var int  */
    private $parkingId;

    /** @var int  */
    private $parkingSlotId;

    /** @var DateTime  */
    private $fromDate;

    /** @var DateTime  */
    private $toDate;

    /**
     * @param int $userId
     * @param int $parkingId
     * @param int $parkingSlotId
     * @param DateTime $fromDate
     * @param DateTime $toDate
     */
    public function __construct(
        int $userId,
        int $parkingId,
        int $parkingSlotId,
        DateTime $fromDate,
        DateTime $toDate
    ) {
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->parkingSlotId = $parkingSlotId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
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
     * @return int
     */
    public function parkingSlotId(): int
    {
        return $this->parkingSlotId;
    }

    /**
     * @return DateTime
     */
    public function fromDate(): DateTime
    {
        return $this->fromDate;
    }

    /**
     * @return DateTime
     */
    public function toDate(): DateTime
    {
        return $this->toDate;
    }
}
