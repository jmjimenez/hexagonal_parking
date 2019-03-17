<?php

namespace Jmj\Parking\Application\Command;

class RequestResetUserPassword
{
    /** @var string  */
    private $userEmail;

    /**
     * @param string $userEmail
     */
    public function __construct(
        string $userEmail
    ) {
        $this->userEmail = $userEmail;
    }

    /**
     * @return string
     */
    public function userEmail(): string
    {
        return $this->userEmail;
    }
}
