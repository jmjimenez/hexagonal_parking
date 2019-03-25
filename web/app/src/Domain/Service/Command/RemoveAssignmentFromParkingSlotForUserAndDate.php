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

//TODO: rename this use case to RemoveParkingSlotAssignmentToUserFromDate
class RemoveAssignmentFromParkingSlotForUserAndDate extends Common\BaseCommand
{
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
        if (!$this->loggedInUserIsAdministrator() && $this->user->uuid() !== $this->loggedInUser->uuid()) {
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
