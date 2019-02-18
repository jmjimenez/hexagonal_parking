<?php

namespace Jmj\Parking\Application\Repository;

use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;

interface Parking
{
    public function findById(int $repositoryId): ?DomainParking;
}