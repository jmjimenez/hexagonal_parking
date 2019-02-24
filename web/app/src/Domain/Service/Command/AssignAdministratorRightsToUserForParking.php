<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class AssignAdministratorRightsToUserForParking extends ParkingBaseCommand
{
    /** @var User */
    protected $loggedInUser;

    /** @var User */
    protected $user;

    /** @var Parking */
    protected $parking;

    /** @var ParkingRepositoryInterface  */
    protected $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param User $loggedInUser
     * @param User $user
     * @param Parking $parking
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
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)) {
            throw new NotAuthorizedOperation('User is not administrator');
        }

        $this->parking->addAdministrator($this->user);

        $this->parkingRepository->save($this->parking);
    }
}

