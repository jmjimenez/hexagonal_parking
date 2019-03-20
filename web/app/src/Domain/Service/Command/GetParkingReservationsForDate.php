<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotReservationsForDateIncorrect;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class GetParkingReservationsForDate extends BaseCommand
{
    /**
     * @var DateTimeImmutable
     */
    protected $date;

    /**
     * @var array
     */
    protected $parkingSlotReservations;

    /**
     * @param  User              $loggedInUser
     * @param  Parking           $parking
     * @param  DateTimeImmutable $date
     * @return array
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, Parking $parking, DateTimeImmutable $date) : array
    {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->date = $date;

        $this->processCatchingDomainEvents();

        return $this->parkingSlotReservations;
    }

    /**
     * @throws ParkingSlotReservationsForDateIncorrect
     * @throws UserNotAssigned
     */
    protected function process()
    {
        if (!$this->loggedInUserIsAdministrator() && !$this->parking->isUserAssigned($this->loggedInUser)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $this->parkingSlotReservations = $this->parking->getParkingSlotsReservationsForDate($this->date);
    }
}
