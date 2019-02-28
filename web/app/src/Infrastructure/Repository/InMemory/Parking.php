<?php

namespace Jmj\Parking\Infrastructure\Repository\InMemory;

use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;
use Jmj\Parking\Domain\Repository\Parking as DomainParkingRepository;

class Parking implements DomainParkingRepository
{
    /** @var DomainParking[] */
    protected $parkings = [];

    /**
     * @inheritdoc
     */
    public function findByUuid(string $uuid): ?DomainParking
    {
        foreach ($this->parkings as $parking) {
            if ($parking->uuid() === $uuid) {
                return $parking;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function save(DomainParking $parking) : int
    {
        $this->parkings[$parking->uuid()] = $parking;

        return 1;
    }

    /**
     * @inheritdoc
     */
    public function delete(DomainParking $parking) : int
    {
        if (isset($this->parkings[$parking->uuid()])) {
            unset($this->parkings[$parking->uuid()]);

            return 1;
        }

        return 0;
    }
}
