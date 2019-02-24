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
    public function save(DomainParking $parking)
    {
        $this->parkings[$parking->uuid()] = $parking;
    }

    /**
     * @inheritdoc
     */
    public function delete(DomainParking $parking)
    {
        if (isset($this->parkings[$parking->uuid()])) {
            unset($this->parkings[$parking->uuid()]);
        }
    }
}