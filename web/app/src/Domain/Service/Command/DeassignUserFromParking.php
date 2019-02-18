<?php

namespace Jmj\Parking\Domain\Service\Command;


use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class DeassignUserFromParking
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param User $user
     * @return bool
     * @throws NotAuthorizedOperation
     * @throws UserNotAssigned
     */
    public function execute(User $loggedInUser, Parking $parking, User $user) : bool
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        if (!$parking->isUserAssigned($user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        return $parking->removeUser($user);
    }
}