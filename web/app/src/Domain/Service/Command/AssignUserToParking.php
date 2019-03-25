<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;

class AssignUserToParking extends Common\BaseCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $isAdministrator;

    /**
     * @param  User    $loggedInUser
     * @param  User    $user
     * @param  Parking $parking
     * @param  bool    $isAdministrator
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, User $user, Parking $parking, bool $isAdministrator)
    {
        $this->loggedInUser = $loggedInUser;
        $this->user = $user;
        $this->parking = $parking;
        $this->isAdministrator = $isAdministrator;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws UserNameAlreadyExists
     */
    protected function process()
    {
        $this->checkAdministrationRights();

        $this->parking->addUser($this->user, $this->isAdministrator);
    }
}
