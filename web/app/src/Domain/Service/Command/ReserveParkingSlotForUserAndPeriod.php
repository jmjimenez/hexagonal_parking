<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class ReserveParkingSlotForUserAndPeriod
{
    /**
     * @param Parking $parking
     * @param User $user
     * @param int $parkingSlotId
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return bool
     * @throws UserNotAssigned
     * @throws ParkingSlotNotFound
     */
    public function execute(
        Parking $parking,
        User $user,
        int $parkingSlotId,
        DateTime $fromDate,
        DateTime $toDate
    ) : bool {
        if (!$parking->isUserAssigned($user)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $parkingSlot = $parking->getParkingSlotByUuid($parkingSlotId);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        return $parkingSlot->reserveToUserForPeriod($user, $fromDate, $toDate);
    }
}