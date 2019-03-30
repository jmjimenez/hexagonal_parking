<?php

namespace Jmj\Parking\Application\Command\Handler;

use DateTimeImmutable;
use Exception;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Application\Command\RequestResetUserPassword as RequestResetUserPasswordPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\RequestResetUserPassword as RequestResetUserPasswordDomainCommand;

class RequestResetUserPassword extends Common\BaseHandler
{
    /** @var string */
    const SECRET = 'parkingSecret';

    /** @var int  */
    const TOKEN_DAYS_TIMEOUT = 3;

    /** @var UserRepository  */
    protected $userRepository;

    /** @var PdoProxy */
    protected $pdoProxy;

    /**
     * @param PdoProxy $pdoProxy
     * @param UserRepository $userRepository
     */
    public function __construct(PdoProxy $pdoProxy, UserRepository $userRepository)
    {
        $this->pdoProxy = $pdoProxy;
        $this->userRepository = $userRepository;
    }

    /**
     * @param RequestResetUserPasswordPayload $payload
     * @return array
     * @throws UserNotFound
     * @throws ParkingException
     * @throws Exception
     */
    public function execute(RequestResetUserPasswordPayload $payload): array
    {
        try {
            $this->pdoProxy->startTransaction();

            $user = $this->userRepository->findByEmail($payload->userEmail());
            $this->validateUser($user);

            //TODO: perhaps inject an infrastructure service to create the token
            $resetPasswordToken = md5($user->email() . date('YmdHis') . self::SECRET);
            //TODO: perhaps inject an infrastructure service to configure the time limit
            $resetPasswordTokenTimeout = new DateTimeImmutable(sprintf('+%s days', self::TOKEN_DAYS_TIMEOUT));

            $command = new RequestResetUserPasswordDomainCommand();

            $command->execute($user, $resetPasswordToken, $resetPasswordTokenTimeout);
            $this->userRepository->save($user);

            $this->pdoProxy->commitTransaction();
        } catch (UserNotFound | ParkingException | Exception $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }

        //TODO: this command should send an email to the user perhaps by injecting a notifier
        return [
            'email' => $user->email(),
            'token' => $resetPasswordToken,
            'expirationDate' => $resetPasswordTokenTimeout->format('Y-m-d')
        ];
    }
}
