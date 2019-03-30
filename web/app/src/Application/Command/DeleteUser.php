<?php

namespace Jmj\Parking\Application\Command;

class DeleteUser
{
    /** @var string  */
    private $loggedInUserUuid;

    /** @var string  */
    private $userUuid;

    /**
     * @param string $loggedUserUuid
     * @param string $userUuid
     */
    public function __construct(
        string $loggedUserUuid,
        string $userUuid
    ) {
        $this->loggedInUserUuid = $loggedUserUuid;
        $this->userUuid = $userUuid;
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
    public function userUuid(): string
    {
        return $this->userUuid;
    }
}
