<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetUserInformation as GetUserInformationPayload;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\GetUserInformation as GetUserInformationDomainCommand;

//TODO: commands should be splitted into subfolders depending on the main target of the command
class GetUserInformation extends ParkingBaseHandler
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
     * @param GetUserInformationPayload $payload
     * @return array
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(GetUserInformationPayload $payload): array
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
        $this->validateUser($loggedInUser);

        $user = $this->userRepository->findByUuid($payload->userUuid());
        $this->validateUser($user);

        $command = new GetUserInformationDomainCommand();

        return $command->execute($loggedInUser, $user);
    }
}
