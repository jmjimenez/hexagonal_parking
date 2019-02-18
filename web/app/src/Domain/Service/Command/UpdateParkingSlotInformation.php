<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\ParkingSlotNumberAlreadyExists;

class UpdateParkingSlotInformation
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param int $parkingSlotId
     * @param string $number
     * @param string $description
     * @return bool
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        int $parkingSlotId,
        string $number,
        string $description
    ) : bool
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('operation not allowed');
        }

        /** @var ParkingSlot $parkingSlot */
        $parkingSlot = $parking->getParkingSlotByUuid($parkingSlotId);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        if ($number != $parkingSlot->number()) {
            if ($parking->getParkingSlotByNumber($number) instanceof ParkingSlot) {
                throw new ParkingSlotNumberAlreadyExists('parking number already exists');
            }
        }

        return $parkingSlot->updateInformation($number, $description);
    }
}