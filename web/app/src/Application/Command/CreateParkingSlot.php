<?php

namespace Jmj\Parking\Application\Command;

class CreateParkingSlot
{
    /** @var string  */
    private $loggedInUserUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var string  */
    private $parkingNumber;

    /** @var string  */
    private $parkingDescription;

    /**
     * AdministratorCreatesParkingSlot constructor.
     * @param string $loggedInUserUuid
     * @param string $parkingUuid
     * @param string $parkingNumber
     * @param string $parkingDescription
     */
    public function __construct(
        string $loggedInUserUuid,
        string $parkingUuid,
        string $parkingNumber,
        string $parkingDescription
    ) {
        $this->loggedInUserUuid = $loggedInUserUuid;
        $this->parkingUuid = $parkingUuid;
        $this->parkingNumber = $parkingNumber;
        $this->parkingDescription = $parkingDescription;
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
    public function parkingSlotNumber(): string
    {
        return $this->parkingNumber;
    }

    /**
     * @return string
     */
    public function parkingSlotDescription(): string
    {
        return $this->parkingDescription;
    }
}
