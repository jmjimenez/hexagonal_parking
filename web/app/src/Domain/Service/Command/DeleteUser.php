<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;

class DeleteUser extends Common\BaseCommand
{
    /** @var User */
    protected $user;

    /**
     * @param User $loggedInUser
     * @param User $user
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, User $user)
    {
        $this->loggedInUser = $loggedInUser;
        $this->user = $user;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     */
    protected function process()
    {
        if (!$this->loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        $this->user->delete();
    }
}
