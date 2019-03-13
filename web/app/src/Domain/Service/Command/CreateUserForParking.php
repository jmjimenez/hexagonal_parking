<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserEmailAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Repository\User as UserRepositoryInterface;
use Jmj\Parking\Domain\Service\Factory\User as UserFactory;

class CreateUserForParking extends ParkingBaseCommand
{
    /**
     * @var User
     */
    private $loggedInUser;

    /**
     * @var Parking
     */
    private $parking;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    protected $userEmail;

    /**
     * @var string
     */
    protected $userPassword;

    /**
     * @var bool
     */
    protected $userIsAdministrator;

    /**
     * @var bool
     */
    protected $userIsAdministratorForParking;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param UserFactory             $userFactory
     */
    public function __construct(UserRepositoryInterface $userRepository, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    /**
     * @param  User    $loggedInUser
     * @param  Parking $parking
     * @param  string  $userName
     * @param  string  $userEmail
     * @param  string  $userPassword
     * @param  bool    $userIsAdministrator
     * @param  bool    $userIsAdministratorForParking
     * @return User
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        string $userName,
        string $userEmail,
        string $userPassword,
        bool $userIsAdministrator,
        bool $userIsAdministratorForParking
    ) : User {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->userPassword = $userPassword;
        $this->userIsAdministrator = $userIsAdministrator;
        $this->userIsAdministratorForParking = $userIsAdministratorForParking;

        $this->processCatchingDomainEvents();

        return $this->user;
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws NotAuthorizedOperation
     * @throws UserEmailAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    protected function process()
    {
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)
            && !$this->loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation();
        }

        if (null != $this->userRepository->findByName($this->userName)) {
            throw new UserNameAlreadyExists();
        }


        if (null != $this->userRepository->findByEmail($this->userEmail)) {
            throw new UserEmailAlreadyExists();
        }

        $user = $this->userFactory->create(
            $this->userName,
            $this->userEmail,
            $this->userPassword,
            $this->userIsAdministrator
        );

        $this->parking->addUser($user, $this->userIsAdministratorForParking);

        $this->user = $user;
    }
}
