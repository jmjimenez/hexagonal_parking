<?php

namespace Jmj\Parking\Application\Command\Handler;

use Firebase\JWT\JWT;
use Jmj\Parking\Application\Command\UserLogin as UserLoginPayload;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\UserLogin as UserLoginCommand;

class UserLogin extends Common\BaseHandler
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var string  */
    protected $tokenSecret;

    /** @var string  */
    protected $algorithm;

    /**
     * @param UserRepository $userRepository
     * @param string $tokenSecret
     * @param string $algorithm
     */
    public function __construct(UserRepository $userRepository, string $tokenSecret, string $algorithm)
    {
        $this->userRepository = $userRepository;
        $this->tokenSecret = $tokenSecret;
        $this->algorithm = $algorithm;
    }

    /**
     * @param UserLoginPayload $payload
     * @return string
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(UserLoginPayload $payload) : string
    {
        $user = $this->userRepository->findByEmail($payload->userEmail());
        $this->validateUser($user);

        $command = new UserLoginCommand();

        $command->execute($user, $payload->userPassword());

        return $this->generateAuthorizationKey($user->email(), $payload->userPassword());
    }

    /**
     * @param string|null $email
     * @param string|null $password
     * @return string
     */
    protected function generateAuthorizationKey(string $email, string $password): string
    {
        return JWT::encode([ 'email' => $email, 'password' => $password ], $this->tokenSecret, $this->algorithm);
    }
}
