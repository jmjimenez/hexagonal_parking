<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class AssignParkingSlotToUserForPeriod extends ParkingBaseCommand
{
    /** @var User  */
    protected $loggedInUser;

    /** @var User */
    protected $user;

    /** @var Parking */
    protected $parking;

    /** @var string */
    protected $parkingSlotUuidd;

    /** @var DateTimeImmutable */
    protected $fromDate;

    /** @var DateTimeImmutable */
    protected $toDate;

    /** @var bool */
    protected $exclusive;

    /** @var ParkingRepositoryInterface  */
    protected $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param User $loggedInUser
     * @param User $user
     * @param Parking $parking
     * @param string $parkingSlotUuidd
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @param bool $exclusive
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        User $user,
        Parking $parking,
        string $parkingSlotUuidd,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate,
        bool $exclusive
    )
    {
        $this->loggedInUser = $loggedInUser;
        $this->user = $user;
        $this->parking = $parking;
        $this->parkingSlotUuidd = $parkingSlotUuidd;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->exclusive = $exclusive;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws InvalidDateRange
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     */
    protected function process()
    {
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not assigned to this parking');
        }

        /** @var ParkingSlot $parkingSlot */
        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuidd);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        $parkingSlot->assignToUserForPeriod($this->user, $this->fromDate, $this->toDate, $this->exclusive);

        $this->parkingRepository->save($this->parking);
    }
}