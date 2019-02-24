<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class DeassignUserFromParking extends ParkingBaseCommand
{
    /** @var User */
    protected $loggedInUser;

    /** @var Parking */
    protected $parking;

    /** @var User */
    protected $user;

    /** @var ParkingRepositoryInterface  */
    protected $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param User $user
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
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        $this->parking->removeUser($this->user);

        $this->parkingRepository->save($this->parking);
    }
}