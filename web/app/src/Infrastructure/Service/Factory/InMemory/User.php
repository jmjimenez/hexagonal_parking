<?php

namespace Jmj\Parking\Infrastructure\Service\Factory\InMemory;

use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Service\Factory\User as DomainUserFactory;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User as InMemoryUser;

class User implements DomainUserFactory
{
    /**
     * @inheritdoc
     */
    public function create(string $name, string $email, string $password, bool $isAdministrator) : DomainUser
    {
        return new InMemoryUser($name, $email, $password, $isAdministrator);
    }
}