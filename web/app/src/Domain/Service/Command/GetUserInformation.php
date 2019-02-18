<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;

class GetUserInformation
{
    /**
     * @param User $loggedInUser
     * @param User $user
     * @return array
     * @throws NotAuthorizedOperation
     */
    public function execute(User $loggedInUser, User $user): array
    {
        if (!$loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User does not have rights');
        }

        return $user->getInformation();
    }
}