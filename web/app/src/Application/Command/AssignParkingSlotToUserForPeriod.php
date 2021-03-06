<?php

namespace Jmj\Parking\Application\Command;

use DateTimeImmutable;

class AssignParkingSlotToUserForPeriod
{
    /** @var string  */
    private $loggedUserUuid;

    /** @var string  */
    private $userUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var string  */
    private $parkingSlotUuid;

    /** @var DateTimeImmutable  */
    private $fromDate;

    /** @var DateTimeImmutable  */
    private $toDate;

    /** @var bool  */
    private $exclusive;

    /**
     * @param string $loggedUserUuid
     * @param string $userUuid
     * @param string $parkingUuid
     * @param string $parkingSlotUuid
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @param bool $exclusive
     */
    public function __construct(
        string $loggedUserUuid,
        string $userUuid,
        string $parkingUuid,
        string $parkingSlotUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate,
        bool $exclusive
    ) {
        $this->loggedUserUuid = $loggedUserUuid;
        $this->userUuid = $userUuid;
        $this->parkingUuid = $parkingUuid;
        $this->parkingSlotUuid = $parkingSlotUuid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->exclusive = $exclusive;
    }

    /**
     * @return string
     */
    public function loggedUserUuid(): string
    {
        return $this->loggedUserUuid;
    }

    /**
     * @return string
     */
    public function userUuid(): string
    {
        return $this->userUuid;
    }

    /**
     * @return string
     */
    public function parkingUuid(): string
    {
        return $this->parkingUuid;
    }

    /**
     * @return string
     */
    public function parkingSlotUuid(): string
    {
        return $this->parkingSlotUuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function fromDate(): DateTimeImmutable
    {
        return $this->fromDate;
    }

    /**
     * @return DateTimeImmutable
     */
    public function toDate(): DateTimeImmutable
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
