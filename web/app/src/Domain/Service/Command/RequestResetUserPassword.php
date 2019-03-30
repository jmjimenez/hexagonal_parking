<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Exception;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;

class RequestResetUserPassword extends Common\BaseCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $resetPasswordToken;

    /**
     * @var DateTimeImmutable
     */
    protected $tokenExpirationDate;

    /**
     * @param  User $user
     * @param  string $resetPasswordToken
     * @param DateTimeImmutable $tokenExpirationDate
     * @throws ParkingException
     */
    public function execute(User $user, string $resetPasswordToken, DateTimeImmutable $tokenExpirationDate)
    {
        $this->user = $user;
        $this->resetPasswordToken = $resetPasswordToken;
        $this->tokenExpirationDate = $tokenExpirationDate;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws Exception
     */
    protected function process()
    {
        $this->user->requestResetPassword($this->resetPasswordToken, $this->tokenExpirationDate);
    }
}
