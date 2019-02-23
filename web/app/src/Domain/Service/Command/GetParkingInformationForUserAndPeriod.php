<?php

namespace Jmj\Parking\Domain\Service\Command;

use DateTimeImmutable;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\UserNotAssigned;

class GetParkingInformationForUserAndPeriod extends ParkingBaseCommand
{
    /** @var Parking */
    protected $parking;

    /** @var User */
    protected $user;

    /** @var DateTimeImmutable */
    protected $fromDate;

    /** @var DateTimeImmutable */
    protected $toDate;

    /** @var array */
    protected $parkingInformation;

    /**
     * @param Parking $parking
     * @param User $user
     * @param DateTimeImmutable $fromDate
     * @param DateTimeImmutable $toDate
     * @return array
     * @throws ParkingException
     */
    public function execute(Parking $parking, User $user, DateTimeImmutable $fromDate, DateTimeImmutable $toDate) : array
    {
        $this->parking = $parking;
        $this->user = $user;
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
        if (!$this->parking->isUserAssigned($this->user)) {
            throw new UserNotAssigned('User is not registered in parking');
        }

        $this->parkingInformation =  $this->parking->getUserInformation($this->user, $this->fromDate, $this->toDate);
    }
}