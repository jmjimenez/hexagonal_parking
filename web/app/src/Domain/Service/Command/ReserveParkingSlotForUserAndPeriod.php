<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class ReserveParkingSlotForUserAndPeriod extends BaseCommand
{
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
     * @param  Parking           $parking
     * @param  User              $loggedInUser
     * @param  string            $parkingSlotUuid
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @throws ParkingException
     */
    public function execute(
        Parking $parking,
        User $loggedInUser,
        string $parkingSlotUuid,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) {
        $this->parking = $parking;
        $this->loggedInUser = $loggedInUser;
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
        //TODO: this command should also be executed by an admin logged in user
        if (!$this->loggedInUserIsAdministrator() && !$this->parking->isUserAssigned($this->loggedInUser)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuid);

        if (!$parkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        $parkingSlot->reserveToUserForPeriod($this->loggedInUser, $this->fromDate, $this->toDate);
    }
}
