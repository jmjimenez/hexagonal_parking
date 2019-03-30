<?php

namespace Jmj\Parking\Infrastructure\Repository\InMemory;

use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Repository\User as DomainUserRepository;

class User implements DomainUserRepository
{
    /** @var DomainUser[] */
    protected $users = [];

    /**
     * {@inheritdoc}
     */
    public function findByUuid(string $uuid) : ?DomainUser
    {
        foreach ($this->users as $user) {
            if ($user->uuid() === $uuid) {
                return $user;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByName(string $name) : ?DomainUser
    {
        foreach ($this->users as $user) {
            if ($user->name() === $name) {
                return $user;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email) : ?DomainUser
    {
        foreach ($this->users as $user) {
            if ($user->email() === $email) {
                return $user;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(DomainUser $user)
    {
        $this->users[$user->uuid()] = $user;
    }


    /**
     * {@inheritdoc}
     */
    public function delete(DomainUser $user)
    {
        if (isset($this->users[$user->uuid()])) {
            unset($this->users[$user->uuid()]);
        }
    }
}
