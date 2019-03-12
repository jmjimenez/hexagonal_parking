<?php

namespace Jmj\Parking\Application\Command;

class DeleteParking
{
    /** @var string  */
    private $loggedInUserUuid;

    /** @var string  */
    private $parkingUuid;

    /**
     * @param string $loggedUserUuid
     * @param string $parkingUuid
     */
    public function __construct(
        string $loggedUserUuid,
        string $parkingUuid
    ) {
        $this->loggedInUserUuid = $loggedUserUuid;
        $this->parkingUuid = $parkingUuid;
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
}
