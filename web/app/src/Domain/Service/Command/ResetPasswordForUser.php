<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;

class ResetPasswordForUser
{
    /**
     * @param User $loggedInUser
     * @param User $user
     * @return bool
     * @throws NotAuthorizedOperation
     */
    public function execute(User $loggedInUser, User $user)
    {
        if (!$loggedInUser->isAdministrator() && $loggedInUser->uuid() != $user->uuid()) {
            throw new NotAuthorizedOperation('cannot perform this operation');
        }

        return $user->resetPassword();
    }
}