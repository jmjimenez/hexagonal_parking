<?php

namespace Jmj\Parking\Domain\Repository;

use Jmj\Parking\Domain\Aggregate\User as DomainUser;

interface User
{
    public function findByUuid(string $uuid): ?DomainUser;

    public function findByName(string $name): ?DomainUser;

    public function findByEmail(string $email) : ?DomainUser;

    public function save(DomainUser $user);
}