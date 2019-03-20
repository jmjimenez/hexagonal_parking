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
    protected $loggedInUser;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Parking
     */
    protected $parking;

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
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)
            && !$this->loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User is not administrator');
        }

        $this->parking->addAdministrator($this->user);
    }
}
