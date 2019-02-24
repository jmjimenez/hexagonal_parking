<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class CreateParkingSlot extends ParkingBaseCommand
{
    /** @var User */
    protected $loggedInUser;

    /** @var Parking */
    protected $parking;

    /** @var string */
    protected $parkingSlotNumber;

    /** @var string */
    protected $parkingSlotDescription;

    /** @var ParkingRepositoryInterface  */
    protected $parkingRepository;

    /** @var ParkingSlot */
    protected $parkingSlot;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param User $loggedInUser
     * @param Parking $parking
     * @param string $parkingSlotNumber
     * @param string $parkingSlotDescription
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
        if (!$this->parking->isAdministeredByUser($this->loggedInUser)) {
            throw new NotAuthorizedOperation('User cannot do this operation');
        }

        $parkingSlot = $this->parking->getParkingSlotByNumber($this->parkingSlotNumber);

        if ($parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNumberAlreadyExists('parking slot number already exists');
        }

        $this->parkingSlot = $this->parking->createParkingSlot($this->parkingSlotNumber, $this->parkingSlotDescription);

        $this->parkingRepository->save($this->parking);
    }
}