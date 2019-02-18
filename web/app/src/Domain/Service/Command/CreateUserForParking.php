<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Command\Exception\UserNameAlreadyExists;
use Jmj\Parking\Infrastructure\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Factory\User as UserFactory;

class CreateUserForParking
{
    /** @var UserRepository  */
    private $userRepository;

    /** @var UserFactory */
    private $userFactory;

    public function __construct(User $userRepository, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param string $userName
     * @param bool $isAdministrator
     * @return User
     * @throws NotAuthorizedOperation
     * @throws UserNameAlreadyExists
     */
    public function execute(User $loggedInUser, Parking $parking, string $userName, bool $isAdministrator): User
    {
        if (!$parking->isAdministeredByUser($loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        if (false !== $this->userRepository->findUserByName($userName)) {
            throw new UserNameAlreadyExists('user name already exists');
        }

        $user = $this->userFactory->create($userName);

        return $parking->addUser($user, $isAdministrator);
    }
}