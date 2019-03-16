<?php

namespace Jmj\Parking\Application\Command;

class GetUserInformation
{
    /** @var string  */
    private $loggedUserUuid;

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
        $this->loggedUserUuid = $loggedUserUuid;
        $this->userUuid = $userUuid;
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
}
