<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class ReserveParkingSlotForUserAndPeriod extends ParkingBaseCommand
{
    /**
     * @var Parking
     */
    protected $parking;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $parkingSlotUuid;

    /**
     * @var DateTimeImmutable
     */
    protected $fromDate;

    /**
     * @var DateTimeImmutable
     */
    protected $toDate;

    /**
     * @var ParkingRepositoryInterface
     */
    protected $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param  Parking           $parking
     * @param  User              $user
     * @param  string            $parkingSlotUuid
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @throws ParkingException
     */
    public function execute(
        Parking $parking,
        User $user,
        string $parkingSlotUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $this->parking = $parking;
        $this->user = $user;
        $this->parkingSlotUuid = $parkingSlotUuid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->processCatchingDomainEvents();
    }

    /**
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     * @throws \Exception
     */
    protected function process()
    {
        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuid);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        $parkingSlot->reserveToUserForPeriod($this->user, $this->fromDate, $this->toDate);

        $this->parkingRepository->save($this->parking);
    }
}
