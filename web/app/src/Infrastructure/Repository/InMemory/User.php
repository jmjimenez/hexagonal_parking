<?php

namespace Jmj\Parking\Infrastructure\Repository\InMemory;

use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Repository\User as DomainUserRepository;

class User implements DomainUserRepository
{
    /** @var DomainUser[] */
    protected $users = [];

    /**
     * @inheritdoc
     */
    public function findByUuid(int $uuid) : ?DomainUser
    {
        foreach ($this->users as $user) {
            if ($user->uuid() === $uuid) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findUserByName(string $name) : ?DomainUser
    {
        foreach ($this->users as $user) {
            if ($user->name() === $name) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findUserByEmail(string $email) : ?DomainUser
    {
        foreach ($this->users as $user) {
            if ($user->email() === $email) {
                return $user;
            }
        }

        return null;
    }
}