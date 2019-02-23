<?php

namespace Jmj\Parking\Domain\Service\Factory;

use Jmj\Parking\Domain\Aggregate\Parking as ParkingAggregate;

interface Parking
{
    /**
     * @param string $description
     * @return ParkingAggregate
     */
    public function create(string $description): ParkingAggregate;
}