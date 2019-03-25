<?php

namespace Jmj\Parking\Application\Command;

class UserLogin
{
    /** @var string  */
    private $userEmail;

    /** @var string  */
    private $userPassword;

    /**
     * @param string $userEmail
     * @param string $userPassword
     */
    public function __construct(
        string $userEmail,
        string $userPassword
    ) {
        $this->userEmail = $userEmail;
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
    public function userPassword(): string
    {
        return $this->userPassword;
    }
}
