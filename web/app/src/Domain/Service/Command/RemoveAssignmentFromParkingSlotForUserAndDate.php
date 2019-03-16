<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

//TODO: rename this use case to RemoveParkingSlotAssignmentToUserFromDate
class RemoveAssignmentFromParkingSlotForUserAndDate extends ParkingBaseCommand
{
    /**
     * @var User
     */
    protected $loggedInUser;

    /**
     * @var Parking
     */
    protected $parking;

    /**
     * @var string
     */
    protected $parkingSlotUuid;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var DateTimeImmutable
     */
    protected $fromDate;

    /**
     * @var ParkingRepositoryInterface
     */
    protected $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param  User              $loggedInUser
     * @param  Parking           $parking
     * @param  string            $parkingSlotUuid
     * @param  User              $user
     * @param  DateTimeImmutable $fromDate
     * @return void
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        string $parkingSlotUuid,
        User $user,
        DateTimeImmutable $fromDate
    ) {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->parkingSlotUuid = $parkingSlotUuid;
        $this->user = $user;
        $this->fromDate = $fromDate;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     */
    protected function process()
    {
        /**
         * TODO: perhaps these checking methods should be in the parent class
         */
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)
            && ($this->user->uuid() !== $this->loggedInUser->uuid()
            && !$this->loggedInUser->isAdministrator())
        ) {
            throw new NotAuthorizedOperation('Operation not allowed');
        }

        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuid);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('Parking Slot not found');
        }

        $parkingSlot->removeAssigment($this->user, $this->fromDate);
    }
}
