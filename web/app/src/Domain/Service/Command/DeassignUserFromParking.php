<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class DeassignUserFromParking extends Common\BaseCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @param  User    $loggedInUser
     * @param  Parking $parking
     * @param  User    $user
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, Parking $parking, User $user)
    {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->user = $user;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws UserNotAssigned
     */
    protected function process()
    {
        $this->checkAdministrationRights();

        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        $this->parking->removeUser($this->user);
    }
}
