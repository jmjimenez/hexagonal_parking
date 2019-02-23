<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class FreeAssignedParkingSlotForUserAndPeriod extends ParkingBaseCommand
{
    /** @var User */
    protected $loggedInUser;

    /** @var Parking */
    protected $parking;

    /** @var User */
    protected $user;

    /** @var string */
    protected $parkingSlotUuidd;

    /** @var DateTimeImmutable */
    protected $fromDate;

    /** @var DateTimeImmutable */
    protected $toDate;

    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param User $user
     * @param string $parkingSlotUuid
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        User $user,
        string $parkingSlotUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->user = $user;
        $this->parkingSlotUuidd = $parkingSlotUuid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     * @throws \Exception
     */
    protected function process()
    {
        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        if (
            !$this->parking->isAdministeredByUser($this->loggedInUser)
            && $this->loggedInUser->uuid() != $this->user->uuid()
        ) {
            throw new NotAuthorizedOperation('cannot perform this operation');
        }

        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuidd);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        $parkingSlot->markAsFreeFromUserAndPeriod($this->user, $this->fromDate, $this->toDate);
    }
}