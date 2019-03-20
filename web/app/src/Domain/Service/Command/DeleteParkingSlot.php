<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;

class DeleteParkingSlot extends BaseCommand
{
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
        $this->checkAdministrationRights();

        $this->parking->deleteParkingSlotByUuid($this->parkingSlotUuid);
    }
}
