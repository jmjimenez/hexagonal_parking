<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;

class DeleteParkingSlot extends ParkingBaseCommand
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
     * @var string
     */
    protected $parkingSlotUuid;

    /**
     * @param  User    $loggedInUser
     * @param  Parking $parking
     * @param  string  $parkingSlotUuid
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, Parking $parking, string $parkingSlotUuid)
    {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->parkingSlotUuid = $parkingSlotUuid;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     */
    protected function process()
    {
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)
            && !$this->loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        $this->parking->deleteParkingSlotByUuid($this->parkingSlotUuid);
    }
}
