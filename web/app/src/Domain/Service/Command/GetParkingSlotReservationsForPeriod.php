<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class GetParkingSlotReservationsForPeriod
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param int $parkingSlotId
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return array
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        int $parkingSlotId,
        DateTime $fromDate,
        DateTime $toDate
    ) : array
    {
        if (!$parking->isUserAssigned($loggedInUser)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $parkingSlot = $parking->getParkingSlotByUuid($parkingSlotId);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }
        return $parkingSlot->getReservationsForPeriod($fromDate, $toDate);
    }
}