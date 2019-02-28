<?php

namespace Jmj\Parking\Application\Command;

class AssignUserToParking
{
    /** @var string  */
    private $loggedUserUuid;

    /** @var string  */
    private $userUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var bool  */
    private $isAdministrator;

    /**
     * AdministratorCreatesParkingSlot constructor.
     * @param string $loggedUserUuid
     * @param string $userUuid
     * @param string $parkingUuid
     * @param bool $isAdministrator
     */
    public function __construct(
        string $loggedUserUuid,
        string $userUuid,
        string $parkingUuid,
        bool $isAdministrator
    ) {
        $this->loggedUserUuid = $loggedUserUuid;
        $this->userUuid = $userUuid;
        $this->parkingUuid = $parkingUuid;
        $this->isAdministrator = $isAdministrator;
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
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->isAdministrator;
    }

}