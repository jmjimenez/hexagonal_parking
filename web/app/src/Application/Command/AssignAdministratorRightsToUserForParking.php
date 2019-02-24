<?php

namespace Jmj\Parking\Application\Command;

class AssignAdministratorRightsToUserForParking
{
    /** @var string  */
    private $loggedUserUuid;

    /** @var string  */
    private $userUuid;

    /** @var string  */
    private $parkingUuid;

    /**
     * AdministratorCreatesParkingSlot constructor.
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
    public function getLoggedUserUuid(): string
    {
        return $this->loggedUserUuid;
    }

    /**
     * @return string
     */
    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    /**
     * @return string
     */
    public function getParkingUuid(): string
    {
        return $this->parkingUuid;
    }

}