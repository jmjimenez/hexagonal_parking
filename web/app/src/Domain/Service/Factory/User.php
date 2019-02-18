<?php

namespace Jmj\Parking\Domain\Service\Factory;

use Jmj\Parking\Domain\Aggregate\User as UserAggregate;

interface User
{
    public function create(string $userName): UserAggregate;
}