<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepositoryInterface;

class ResetPasswordForUser extends ParkingBaseCommand
{
    /** @var User */
    protected $user;

    /** @var string */
    protected $password;

    /** @var string */
    protected $passwordToken;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $user
     * @param string $password
     * @param string $passwordToken
     * @throws ParkingException
     */
    public function execute(User $user, string $password, string $passwordToken)
    {
        $this->user = $user;
        $this->password = $password;
        $this->passwordToken = $passwordToken;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws \Exception
     */
    protected function process()
    {
        $this->user->resetPassword($this->password, $this->passwordToken);
        $this->userRepository->save($this->user);
    }
}