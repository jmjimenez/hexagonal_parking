<?php

namespace Jmj\Parking\Domain\Repository;

use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;

interface Parking
{
    /**
     * @param string $uuid
     * @return DomainParking|null
     */
    public function findByUuid(string $uuid): ?DomainParking;

    /**
     * @param DomainParking $parking
     * @return mixed
     */
    public function save(DomainParking $parking);

    /**
     * @param DomainParking $parking
     * @return mixed
     */
    public function delete(DomainParking $parking);
}