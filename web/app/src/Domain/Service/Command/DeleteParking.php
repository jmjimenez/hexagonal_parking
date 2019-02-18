<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;

class DeleteParking
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @return bool
     * @throws NotAuthorizedOperation
     */
    public function execute(User $loggedInUser, Parking $parking) : bool
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        return $parking->delete();
    }
}