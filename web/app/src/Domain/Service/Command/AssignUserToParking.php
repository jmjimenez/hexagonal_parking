<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;

class AssignUserToParking
{
    /**
     * @param User $loggedInUser
     * @param User $user
     * @param Parking $parking
     * @param bool $isAdministrator
     * @return User
     * @throws NotAuthorizedOperation
     */
    public function execute(User $loggedInUser, User $user, Parking $parking, bool $isAdministrator) : User
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('User is not administrator');
        }

        return $parking->assignUser($user, $isAdministrator);
    }
}