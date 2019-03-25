<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class GetParkingInformationForUserAndPeriod extends Common\BaseCommand
{
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
    protected $parkingInformation;

    /**
     * @param  Parking           $parking
     * @param  User              $loggedInUser
     * @param  DateTimeImmutable $fromDate
     * @param  DateTimeImmutable $toDate
     * @return array
     * @throws ParkingException
     */
    public function execute(
        Parking $parking,
        User $loggedInUser,
        DateTimeImmutable $fromDate,
        DateTimeImmutable $toDate
    ) : array {
        $this->parking = $parking;
        $this->loggedInUser = $loggedInUser;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->processCatchingDomainEvents();

        return $this->parkingInformation;
    }

    /**
     * @throws UserNotAssigned
     */
    protected function process()
    {
        if (!$this->loggedInUserIsAdministrator() && !$this->parking->isUserAssigned($this->loggedInUser)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $this->parkingInformation =  $this->parking->getUserInformation(
            $this->loggedInUser,
            $this->fromDate,
            $this->toDate
        );
    }
}
