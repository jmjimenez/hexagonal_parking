<?php

namespace Jmj\Parking\Application\Command\Handler;

use Exception;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Application\Command\ResetUserPassword as ResetUserPasswordPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\ResetUserPassword as ResetUserPasswordDomainCommand;

class ResetUserPassword extends Common\BaseHandler
{
    /** @var UserRepository  */
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
     * @param ResetUserPasswordPayload $payload
     * @throws UserNotFound
     * @throws ParkingException
     * @throws Exception
     */
    public function execute(ResetUserPasswordPayload $payload)
    {
        try {
            $this->pdoProxy->startTransaction();

            $user = $this->userRepository->findByEmail($payload->userEmail());
            $this->validateUser($user);

            $command = new ResetUserPasswordDomainCommand();

            $command->execute($user, $payload->userPassword(), $payload->passwordToken());

            $this->userRepository->save($user);

            $this->pdoProxy->commitTransaction();
        } catch (UserNotFound | ParkingException $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }
    }
}
