<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\ParkingSlotNumberAlreadyExists;

class CreateParkingSlot
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param string $parkingSlotNumber
     * @param string $parkingSlotDescription
     * @return ParkingSlot
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        string $parkingSlotNumber,
        string $parkingSlotDescription
    ) : ParkingSlot
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        $parkingSlot = $parking->getParkingSlotByNumber($parkingSlotNumber);

        if ($parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNumberAlreadyExists('parking slot number already exists');
        }

        return $parking->createParkingSlot($parkingSlotNumber, $parkingSlotDescription);
    }
}