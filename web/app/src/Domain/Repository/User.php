<?php

namespace Jmj\Parking\Domain\Repository;

use Jmj\Parking\Domain\Aggregate\User as DomainUser;

interface User
{
    public function findByUuid(int $uuid): ?DomainUser;

    public function findUserByName(string $name): ?DomainUser;

    public function findUserByEmail(string $email) : ?DomainUser;
}