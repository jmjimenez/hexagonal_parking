<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;

class GetUserInformation extends BaseCommand
{
    /**
     * @var User
     */
    protected $loggedInUser;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $userInformation;

    /**
     * @param  User $loggedInUser
     * @param  User $user
     * @return array
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, User $user): array
    {
        $this->loggedInUser = $loggedInUser;
        $this->user = $user;

        $this->processCatchingDomainEvents();

        return $this->userInformation;
    }

    /**
     * @throws NotAuthorizedOperation
     */
    protected function process()
    {
        if (!$this->loggedInUser->isAdministrator() && $this->loggedInUser->uuid() != $this->user->uuid()) {
            throw new NotAuthorizedOperation('User does not have rights');
        }

        $this->userInformation = $this->user->getInformation();
    }
}
