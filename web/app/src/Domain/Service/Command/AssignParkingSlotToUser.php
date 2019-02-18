<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class AssignParkingSlotToUser
{
    /**
     * @param User $loggedInUser
     * @param User $user
     * @param Parking $parking
     * @param int $parkingSlotId
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param bool $exclusive
     * @return bool
     * @throws NotAuthorizedOperation
     * @throws UserNotAssigned
     * @throws ParkingSlotNotFound
     */
    public function execute(
        User $loggedInUser,
        User $user,
        Parking $parking,
        int $parkingSlotId,
        DateTime $fromDate,
        DateTime $toDate,
        bool $exclusive
    ) : bool
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        if (!$parking->isUserAssigned($user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        /** @var ParkingSlot $parkingSlot */
        $parkingSlot = $parking->getParkingSlotByUuid($parkingSlotId);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        return $parkingSlot->assignToUserForPeriod(
            $user,
            $fromDate,
            $toDate,
            $exclusive
        );
    }
}