<?php

namespace Jmj\Parking\Application\Command;

class DeassignUserFromParking
{
    /** @var string  */
    private $loggedUserUuid;

    /** @var string  */
    private $userUuid;

    /** @var string  */
    private $parkingUuid;

    /**
     * @param string $loggedUserUuid
     * @param string $userUuid
     * @param string $parkingUuid
     */
    public function __construct(
        string $loggedUserUuid,
        string $userUuid,
        string $parkingUuid
    ) {
        $this->loggedUserUuid = $loggedUserUuid;
        $this->userUuid = $userUuid;
        $this->parkingUuid = $parkingUuid;
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
}
