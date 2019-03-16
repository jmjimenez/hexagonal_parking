<?php

namespace Jmj\Parking\Application\Command;

use DateTimeImmutable;

class GetParkingReservationsForDate
{
    /** @var string  */
    private $userUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var DateTimeImmutable  */
    private $date;

    /**
     * @param string $userUuid
     * @param string $parkingUuid
     * @param DateTimeImmutable $date
     */
    public function __construct(
        string $userUuid,
        string $parkingUuid,
        DateTimeImmutable $date
    ) {
        $this->userUuid = $userUuid;
        $this->parkingUuid = $parkingUuid;
        $this->date = $date;
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
    public function date(): DateTimeImmutable
    {
        return $this->date;
    }
}
