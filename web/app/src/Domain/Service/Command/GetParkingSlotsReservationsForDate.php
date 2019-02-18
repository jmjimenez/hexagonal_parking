<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class GetParkingSlotsReservationsForDate
{
    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param DateTime $date
     * @return array
     * @throws UserNotAssigned
     */
    public function execute(User $loggedInUser, Parking $parking, DateTime $date) : array
    {
        if (!$parking->isUserAssigned($loggedInUser)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        return $parking->getParkingSlotsReservationsForDate($date);
    }
}