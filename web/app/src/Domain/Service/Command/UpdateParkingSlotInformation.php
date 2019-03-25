<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotDescriptionInvalid;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberInvalid;

class UpdateParkingSlotInformation extends Common\BaseCommand
{
    /**
     * @var string
     */
    protected $parkingSlotUuid;
    
    /**
     * @var string
     */
    protected $number;
    
    /**
     * @var string
     */
    protected $description;

    /**
     * @param  User    $loggedInUser
     * @param  Parking $parking
     * @param  string  $parkingSlotUuid
     * @param  string  $number
     * @param  string  $description
     * @throws ParkingException
     */
    public function execute(
        User $loggedInUser,
        Parking $parking,
        string $parkingSlotUuid,
        string $number,
        string $description
    ) {
        $this->loggedInUser = $loggedInUser;
        $this->parking = $parking;
        $this->parkingSlotUuid = $parkingSlotUuid;
        $this->number = $number;
        $this->description = $description;
        
        $this->processCatchingDomainEvents();
    }

    /**
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws ParkingSlotNumberAlreadyExists
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     */
    protected function process()
    {
        $this->checkAdministrationRights();

        /**
         * @var ParkingSlot $parkingSlot
         */
        $parkingSlot = $this->parking->getParkingSlotByUuid($this->parkingSlotUuid);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('parking slot not found');
        }

        if ($this->number != $parkingSlot->number()) {
            if ($this->parking->getParkingSlotByNumber($this->number) instanceof ParkingSlot) {
                throw new ParkingSlotNumberAlreadyExists('parking number already exists');
            }
        }

        $parkingSlot->updateInformation($this->number, $this->description);
    }
}
