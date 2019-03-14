<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;

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
}
