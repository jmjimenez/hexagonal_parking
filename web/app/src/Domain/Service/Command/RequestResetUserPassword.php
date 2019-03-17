<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;

class RequestResetUserPassword extends ParkingBaseCommand
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
    protected $resetPasswordTokenTimeout;

    /**
     * @param  User $user
     * @param  string $resetPasswordToken
     * @param DateTimeImmutable $resetPasswordTokenTimeout
     * @throws ParkingException
     */
    public function execute(User $user, string $resetPasswordToken, DateTimeImmutable $resetPasswordTokenTimeout)
    {
        $this->user = $user;
        $this->resetPasswordToken = $resetPasswordToken;
        $this->resetPasswordTokenTimeout = $resetPasswordTokenTimeout;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws \Exception
     */
    protected function process()
    {
        $this->user->requestResetPassword($this->resetPasswordToken, $this->resetPasswordTokenTimeout);
    }
}