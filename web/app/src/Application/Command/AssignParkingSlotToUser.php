<?php

namespace Jmj\Parking\Application\Command;

use DateTime;

class AssignParkingSlotToUser
{
    /** @var int  */
    private $administratorId;

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

    /** @var bool  */
    private $exclusive;

    /**
     * AdministratorCreatesParkingSlot constructor.
     * @param int $administratorId
     * @param int $userId
     * @param int $parkingId
     * @param int $parkingSlotId
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param bool $exclusive
     */
    public function __construct(
        int $administratorId,
        int $userId,
        int $parkingId,
        int $parkingSlotId,
        DateTime $fromDate,
        DateTime $toDate,
        bool $exclusive
    ) {
        $this->administratorId = $administratorId;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->parkingSlotId = $parkingSlotId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->exclusive = $exclusive;
    }

    /**
     * @return int
     */
    public function administratorId(): int
    {
        return $this->administratorId;
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

    /**
     * @return bool
     */
    public function exclusive(): bool
    {
        return $this->exclusive;
    }
}