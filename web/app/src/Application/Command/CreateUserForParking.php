<?php

namespace Jmj\Parking\Application\Command;

class CreateUserForParking
{
    /** @var string  */
    private $loggedInUserUuid;

    /** @var string  */
    private $parkingUuid;

    /** @var string  */
    private $userName;

    /** @var string  */
    private $userEmail;

    /** @var string  */
    private $userPassword;

    /** @var bool  */
    private $isAdministrator;

    /** @var bool  */
    private $isAdministratorForParking;

    /**
     * @param string $loggedInUserUuid
     * @param string $parkingUuid
     * @param string $userName
     * @param string $userEmail
     * @param string $userPassword
     * @param bool $isAdministrator
     * @param bool $isAdministratorForParking
     */
    public function __construct(
        string $loggedInUserUuid,
        string $parkingUuid,
        string $userName,
        string $userEmail,
        string $userPassword,
        bool $isAdministrator,
        bool $isAdministratorForParking
    ) {
        $this->loggedInUserUuid = $loggedInUserUuid;
        $this->parkingUuid = $parkingUuid;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->userPassword = $userPassword;
        $this->isAdministrator = $isAdministrator;
        $this->isAdministratorForParking = $isAdministratorForParking;
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
    public function userName(): string
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function userEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * @return string
     */
    public function userPassword(): string
    {
        return $this->userPassword;
    }

    /**
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->isAdministrator;
    }

    /**
     * @return bool
     */
    public function isAdministratorForParking(): bool
    {
        return $this->isAdministratorForParking;
    }
}
