<?php

namespace Jmj\Parking\Application\Command;

class DeleteParkingSlot
{
    /** @var string  */
    private $loggedInUserUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var string  */
    private $parkingSlotUuid;

    /**
     * @param string $loggedUserUuid
     * @param string $parkingUuid
     * @param string $parkingSlotUuid
     */
    public function __construct(
        string $loggedUserUuid,
        string $parkingUuid,
        string $parkingSlotUuid
    ) {
        $this->loggedInUserUuid = $loggedUserUuid;
        $this->parkingUuid = $parkingUuid;
        $this->parkingSlotUuid = $parkingSlotUuid;
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
}
