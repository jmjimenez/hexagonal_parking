<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserInvalid;

class UserLogin extends Common\BaseCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param  User   $user
     * @param  string $password
     * @throws ParkingException
     */
    public function execute(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws UserInvalid
     */
    protected function process()
    {
        if (!$this->user->authenticate($this->password)) {
            throw new UserInvalid();
        }
    }
}
