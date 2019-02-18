<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTime;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\UserNotAssigned;

class GetParkingInformationForUserAndPeriod
{
    /**
     * @param Parking $parking
     * @param User $user
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return array
     * @throws UserNotAssigned
     */
    public function execute(Parking $parking, User $user, DateTime $fromDate, DateTime $toDate) : array
    {
        if (!$parking->isUserAssigned($user)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        return $parking->getUserInformation($user, $fromDate, $toDate);
    }
}