<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;

//TODO: rename to BaseHandler
class ParkingBaseHandler
{
    /**
     * @param User|null $user
     * @throws UserNotFound
     */
    protected function validateUser(?User $user)
    {
        if (!$user instanceof User) {
            throw new UserNotFound();
        }
    }

    /**
     * @param Parking|null $parking
     * @throws ParkingNotFound
     */
    protected function validateParking(?Parking $parking)
    {
        if (!$parking instanceof Parking) {
            throw new ParkingNotFound();
        }
    }

    protected function reservationToArray(Reservation $reservation): array
    {
        return [
            'parkingUuid' => $reservation->parkingSlot()->parking()->uuid(),
            'parkingSlotUuid' => $reservation->parkingSlot()->uuid(),
            'userUuid' => $reservation->user()->uuid(),
            'date' => $reservation->date()->format('Y-m-d'),
        ];
    }

    protected function assignmentToArray(Assignment $assignment): array
    {
        return [
            'parkingUuid' => $assignment->parkingSlot()->parking()->uuid(),
            'parkingSlotUuid' => $assignment->parkingSlot()->uuid(),
            'userUuid' => $assignment->user()->uuid(),
            'date' => $assignment->date()->format('Y-m-d'),
            'exclusive' => $assignment->isExclusive() ? 'true' : 'false',
        ];
    }
}
