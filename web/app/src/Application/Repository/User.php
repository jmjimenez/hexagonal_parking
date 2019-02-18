<?php

namespace Jmj\Parking\Infrastructure\Repository;

use Jmj\Parking\Domain\Aggregate\User as DomainUser;

interface User
{
    public function findById(int $userId): ?DomainUser;

    public function findUserByName(string $userName): ?DomainUser;
}