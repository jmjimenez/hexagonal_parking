<?php

namespace Jmj\Parking\Domain\Service\Factory;

use Jmj\Parking\Domain\Aggregate\Parking as ParkingAggregate;
use Jmj\Parking\Domain\Aggregate\User;

interface Parking
{
    /**
     * @param User $administrator
     * @param string $parkingName
     * @return ParkingAggregate
     */
    public function create(User $administrator, string $parkingName): ParkingAggregate;
}