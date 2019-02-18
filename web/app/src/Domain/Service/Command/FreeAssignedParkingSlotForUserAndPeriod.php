<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class FreeAssignedParkingSlotForUserAndPeriod
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param User $user
     * @param int $parkingSlotId
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return bool
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        User $user,
        int $parkingSlotId,
        DateTime $fromDate,
        DateTime $toDate
    ) : bool
    {
        if (!$parking->isUserAssigned($user)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        if (!$parking->isAdministeredByUser($loggedInUser) && $loggedInUser->uuid() != $user->uuid()) {
            throw new NotAuthorizedOperation('cannot perform this operation');
        }

        $parkingSlot = $parking->getParkingSlotByUuid($parkingSlotId);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        return $parkingSlot->markAsFreeFromUserAndPeriod($user, $fromDate, $toDate);
    }
}