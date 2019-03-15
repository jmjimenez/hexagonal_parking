<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class GetParkingSlotReservationsForPeriod extends ParkingBaseCommand
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
    protected $parkingSlotUuidd;

    /**
     * @var DateTimeImmutable
     */
    protected $fromDate;

    /**
     * @var DateTimeImmutable
     */
    protected $toDate;

    /**
     * @var array
     */
    protected $parkingSlotReservations;

    /**
     * @param  User              $loggedInUser
     * @param  Parking           $parking
     * @param  string            $parkingSlotUuid
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @return array
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        string $parkingSlotUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) : array {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->parkingSlotUuidd = $parkingSlotUuid;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->processCatchingDomainEvents();

        return $this->parkingSlotReservations;
    }

    /**
     * @throws ParkingSlotNotFound
     * @throws UserNotAssigned
     */
    protected function process()
    {
        if (!$this->parking->isUserAssigned($this->loggedInUser)
            && !$this->loggedInUser->isAdministrator()) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuidd);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        $this->parkingSlotReservations = $parkingSlot->getReservationsForPeriod($this->fromDate, $this->toDate);
    }
}
