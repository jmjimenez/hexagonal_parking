<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class RemoveAssignmentFromParkingSlotForUserAndDate
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param int $parkingSlotId
     * @param User $user
     * @param DateTime $fromDate
     * @return bool
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        int $parkingSlotId,
        User $user,
        DateTime $fromDate
    ) : bool
    {
        if (!$parking->isAdministeredByUser($loggedInUser) && ($user->uuid() !== $loggedInUser->uuid())) {
            throw new NotAuthorizedOperation('Operation not allowed');
        }

        if (!$parking->isUserAssigned($user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        /** @var ParkingSlot $parkingSlot */
        $parkingSlot = $parking->getParkingSlotByUuid($parkingSlotId);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('Parking Slot not found');
        }

        return $parkingSlot->removeAssigment($user, $fromDate);
    }
}