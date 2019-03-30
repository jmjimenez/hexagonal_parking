<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\DeleteUser as DeleteUserPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\DeleteUser as DeleteUserCommand;

class DeleteUser extends Common\BaseHandler
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var PdoProxy */
    protected $pdoProxy;

    /**
     * @param PdoProxy $pdoProxy
     * @param UserRepository $userRepository
     */
    public function __construct(
        PdoProxy $pdoProxy,
        UserRepository $userRepository
    ) {
        $this->pdoProxy = $pdoProxy;
        $this->userRepository = $userRepository;
    }

    /**
     * @param DeleteUserPayload $payload
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(DeleteUserPayload $payload)
    {
        try {
            $this->pdoProxy->startTransaction();

            $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
            $this->validateUser($loggedInUser);

            $user = $this->userRepository->findByUuid($payload->userUuid());
            $this->validateUser($user);

            $command = new DeleteUserCommand();

            $command->execute($loggedInUser, $user);

            $this->userRepository->delete($user);

            $this->pdoProxy->commitTransaction();
        } catch (Exception\UserNotFound $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }
    }
}
