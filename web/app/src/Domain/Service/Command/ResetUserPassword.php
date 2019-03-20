<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;

class ResetUserPassword extends BaseCommand
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
     * @var string
     */
    protected $passwordToken;

    /**
     * @param  User   $user
     * @param  string $password
     * @param  string $passwordToken
     * @throws ParkingException
     */
    public function execute(User $user, string $password, string $passwordToken)
    {
        $this->user = $user;
        $this->password = $password;
        $this->passwordToken = $passwordToken;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws \Exception
     */
    protected function process()
    {
        $this->user->resetPassword($this->password, $this->passwordToken);
    }
}
