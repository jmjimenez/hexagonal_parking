<?php

namespace Jmj\Parking\Application\Command;

class CreateParkingSlot
{
    /** @var int  */
    private $administratorId;

    /** @var int  */
    private $parkingId;

    /** @var string  */
    private $parkingNumber;

    /** @var string  */
    private $parkingDescription;

    /**
     * AdministratorCreatesParkingSlot constructor.
     * @param int $administratorId
     * @param int $parkingId
     * @param string $parkingNumber
     * @param string $parkingDescription
     */
    public function __construct(
        int $administratorId,
        int $parkingId,
        string $parkingNumber,
        string $parkingDescription
    ) {
        $this->administratorId = $administratorId;
        $this->parkingId = $parkingId;
        $this->parkingNumber = $parkingNumber;
        $this->parkingDescription = $parkingDescription;
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
    public function parkingId(): int
    {
        return $this->parkingId;
    }

    /**
     * @return string
     */
    public function parkingNumber(): string
    {
        return $this->parkingNumber;
    }

    /**
     * @return string
     */
    public function parkingDescription(): string
    {
        return $this->parkingDescription;
    }
}