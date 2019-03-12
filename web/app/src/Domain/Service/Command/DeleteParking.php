<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;

class DeleteParking extends ParkingBaseCommand
{
    /**
     * @var User
     */
    protected $loggedInUser;

    /**
     * @var Parking
     */
    protected $parking;

    /**
     * @param  User    $loggedInUser
     * @param  Parking $parking
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, Parking $parking)
    {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;

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

        $this->parking->delete();
    }
}
