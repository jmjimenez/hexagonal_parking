<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;

class CreateParkingSlot extends BaseCommand
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
    protected $parkingSlotNumber;

    /**
     * @var string
     */
    protected $parkingSlotDescription;

    /**
     * @var ParkingSlot
     */
    protected $parkingSlot;

    /**
     * @param  User    $loggedInUser
     * @param  Parking $parking
     * @param  string  $parkingSlotNumber
     * @param  string  $parkingSlotDescription
     * @return ParkingSlot
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        string $parkingSlotNumber,
        string $parkingSlotDescription
    ) : ParkingSlot {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->parkingSlotNumber = $parkingSlotNumber;
        $this->parkingSlotDescription = $parkingSlotDescription;

        $this->processCatchingDomainEvents();

        return $this->parkingSlot;
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNumberAlreadyExists
     */
    protected function process()
    {
        //TODO: phpunit that an administrator can perform the operation
        //TODO: perhaps we may encapsulate this checking
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)
            && !$this->loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        $parkingSlot = $this->parking->getParkingSlotByNumber($this->parkingSlotNumber);

        if ($parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNumberAlreadyExists('parking slot number already exists');
        }

        $this->parkingSlot = $this->parking->createParkingSlot($this->parkingSlotNumber, $this->parkingSlotDescription);
    }
}
