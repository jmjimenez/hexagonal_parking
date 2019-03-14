<?php

namespace Jmj\Parking\Application\Command;

use DateTimeImmutable;

class GetParkingInformationForUserAndPeriod
{
    /** @var string  */
    private $userUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var DateTimeImmutable  */
    private $fromDate;

    /** @var DateTimeImmutable  */
    private $toDate;

    /**
     * @param string $userUuid
     * @param string $parkingUuid
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     */
    public function __construct(
        string $userUuid,
        string $parkingUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $this->userUuid = $userUuid;
        $this->parkingUuid = $parkingUuid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
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
