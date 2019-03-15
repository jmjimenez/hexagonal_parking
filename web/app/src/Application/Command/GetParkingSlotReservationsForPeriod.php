<?php

namespace Jmj\Parking\Application\Command;

use DateTimeImmutable;

class GetParkingSlotReservationsForPeriod
{
    /** @var string  */
    private $loggedInUserUuiid;

    /** @var string  */
    private $parkingUuid;

    /** @var string  */
    private $parkingSlotUuid;

    /** @var DateTimeImmutable  */
    private $fromDate;

    /** @var DateTimeImmutable  */
    private $toDate;

    public function __construct(
        string $loggedInUserUuid,
        string $parkingUuid,
        string $parkingSlotUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $this->parkingUuid = $parkingUuid;
        $this->loggedInUserUuiid = $loggedInUserUuid;
        $this->parkingSlotUuid = $parkingSlotUuid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    /**
     * @return string
     */
    public function loggedInUserUuid(): string
    {
        return $this->loggedInUserUuiid;
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
}
