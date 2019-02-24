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
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class UpdateParkingSlotInformation extends ParkingBaseCommand
{
    /** @var User */
    protected $loggedInUser;
    
    /** @var Parking */
    protected $parking;
    
    /** @var string */
    protected $parkingSlotUuid;
    
    /** @var string */
    protected $number;
    
    /** @var string */
    protected $description;

    /** @var ParkingRepositoryInterface  */
    protected $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param string $parkingSlotUuid
     * @param string $number
     * @param string $description
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
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)) {
            throw new NotAuthorizedOperation('operation not allowed');
        }

        /** @var ParkingSlot $parkingSlot */
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

        $this->parkingRepository->save($this->parking);
    }
}

