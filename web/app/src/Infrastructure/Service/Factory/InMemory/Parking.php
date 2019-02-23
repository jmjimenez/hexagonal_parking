<?php

namespace Jmj\Parking\Infrastructure\Service\Factory\InMemory;

use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Service\Factory\Parking as DomainParkingFactory;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking as InMemoryParking;

class Parking implements DomainParkingFactory
{
    /**
     * @inheritdoc
     * @throws ExceptionGeneratingUuid
     */
    public function create(string $description) : DomainParking
    {
        return new InMemoryParking($description);
    }
}