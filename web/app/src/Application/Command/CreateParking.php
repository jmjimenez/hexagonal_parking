<?php

namespace Jmj\Parking\Application\Command;

class CreateParking
{
    /** @var string  */
    private $loggedUserUuid;

    /** @var string  */
    private $description;

    /**
     * @param string $loggedUserUuid
     * @param string $description
     */
    public function __construct(
        string $loggedUserUuid,
        string $description
    ) {
        $this->loggedUserUuid = $loggedUserUuid;
        $this->description = $description;
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
    public function description(): string
    {
        return $this->description;
    }
}
