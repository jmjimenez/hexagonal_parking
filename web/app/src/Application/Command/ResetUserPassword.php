<?php

namespace Jmj\Parking\Application\Command;

class ResetUserPassword
{
    /** @var string  */
    private $userEmail;

    /** @var string  */
    private $passwordToken;

    /** @var string  */
    private $userPassword;

    /**
     * @param string $userEmail
     * @param string $passwordToken
     * @param string $userPassword
     */
    public function __construct(
        string $userEmail,
        string $passwordToken,
        string $userPassword
    ) {
        $this->userEmail = $userEmail;
        $this->passwordToken = $passwordToken;
        $this->userPassword = $userPassword;
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
    public function passwordToken(): string
    {
        return $this->passwordToken;
    }

    /**
     * @return string
     */
    public function userPassword(): string
    {
        return $this->userPassword;
    }
}
