<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;

class AssignAdministratorRightsToUserForParking extends BaseCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @param  User    $loggedInUser
     * @param  User    $user
     * @param  Parking $parking
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, User $user, Parking $parking)
    {
        $this->loggedInUser = $loggedInUser;
        $this->user = $user;
        $this->parking = $parking;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws UserNameAlreadyExists
     */
    protected function process()
    {
        //TODO: implement phpunit for wrong paths
        $this->checkAdministrationRights();

        $this->parking->addAdministrator($this->user);
    }
}
