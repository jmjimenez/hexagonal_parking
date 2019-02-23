<?php

namespace Jmj\Parking\Domain\Service\Factory;

use Jmj\Parking\Domain\Aggregate\User as UserAggregate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;

interface User
{
    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @param bool $isAdministrator
     * @return UserAggregate
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function create(string $name, string $email, string $password, bool $isAdministrator): UserAggregate;
}