<?php

namespace Jmj\Parking\Application\Command;

class UpdateParkingSlotInformation
{
    /** @var string  */
    private $loggedInUserUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var string  */
    private $parkingSlotUuid;

    /** @var string  */
    private $number;

    /** @var string  */
    private $description;

    /**
     * @param string $loggedInUserUuid
     * @param string $parkingUuid
     * @param string $parkingSlotUuid
     * @param string $number
     * @param string $description
     */
    public function __construct(
        string $loggedInUserUuid,
        string $parkingUuid,
        string $parkingSlotUuid,
        string $number,
        string $description
    ) {
        $this->loggedInUserUuid = $loggedInUserUuid;
        $this->parkingUuid = $parkingUuid;
        $this->parkingSlotUuid = $parkingSlotUuid;
        $this->number = $number;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function loggedInUserUuid(): string
    {
        return $this->loggedInUserUuid;
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
     * @return string
     */
    public function number(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }
}
