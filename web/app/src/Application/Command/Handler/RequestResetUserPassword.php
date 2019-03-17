<?php

namespace Jmj\Parking\Application\Command\Handler;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\RequestResetUserPassword as RequestResetUserPasswordPayload;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\RequestResetUserPassword as RequestResetUserPasswordDomainCommand;

class RequestResetUserPassword extends ParkingBaseHandler
{
    /** @var string */
    const SECRET = 'parkingSecret';

    /** @var int  */
    const TOKEN_DAYS_TIMEOUT = 3;

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
     * @param RequestResetUserPasswordPayload $payload
     * @return array
     * @throws Exception\UserNotFound
     * @throws \Jmj\Parking\Domain\Exception\ParkingException
     * @throws \Exception
     */
    public function execute(RequestResetUserPasswordPayload $payload): array
    {
        $user = $this->userRepository->findByEmail($payload->userEmail());
        $this->validateUser($user);

        //TODO: perhaps inject an infrastructure service to create the token
        $resetPasswordToken = md5($user->email() . date('YmdHis') . self::SECRET);
        //TODO: perhaps inject an infrastructure service to configure the time limit
        $resetPasswordTokenTimeout = new DateTimeImmutable(sprintf('+%s days', self::TOKEN_DAYS_TIMEOUT));

        $command = new RequestResetUserPasswordDomainCommand();

        $command->execute($user, $resetPasswordToken, $resetPasswordTokenTimeout);

        //TODO: transactions should be dealt with at this level
        $this->userRepository->save($user);

        //TODO: this command should send an email to the user perhaps by injecting a notifier
        return [
            'email' => $user->email(),
            'token' => $resetPasswordToken,
            'expirationDate' => $resetPasswordTokenTimeout->format('Y-m-d')
        ];
    }
}
