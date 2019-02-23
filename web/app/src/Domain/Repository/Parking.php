<?php

namespace Jmj\Parking\Domain\Repository;

use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;

interface Parking
{
    public function findByUuid(string $uuid): ?DomainParking;
}