<?php

namespace Jmj\Parking\Application\Command\Handler;

use Exception;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Application\Command\ResetUserPassword as ResetUserPasswordPayload;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\ResetUserPassword as ResetUserPasswordDomainCommand;

class ResetUserPassword extends Common\BaseHandler
{
    /** @var UserRepository  */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param ResetUserPasswordPayload $payload
     * @throws UserNotFound
     * @throws ParkingException
     * @throws Exception
     */
    public function execute(ResetUserPasswordPayload $payload)
    {
        $user = $this->userRepository->findByEmail($payload->userEmail());
        $this->validateUser($user);

        $command = new ResetUserPasswordDomainCommand();

        $command->execute($user, $payload->userPassword(), $payload->passwordToken());

        $this->userRepository->save($user);
    }
}
