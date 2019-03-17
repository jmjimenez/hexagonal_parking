<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\CreateParkingSlot as CreateParkingSlotPayload;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\CreateParkingSlot as CreateParkingSlotCommand;

class CreateParkingSlot extends ParkingBaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     * @param ParkingRepository $parkingRepository
     */
    public function __construct(
        UserRepository $userRepository,
        ParkingRepository $parkingRepository
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CreateParkingSlotPayload $payload
     * @return \Jmj\Parking\Domain\Aggregate\ParkingSlot
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(CreateParkingSlotPayload $payload) : ParkingSlot
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
        $this->validateUser($loggedInUser);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new CreateParkingSlotCommand();

        $parkingSlot = $command->execute(
            $loggedInUser,
            $parking,
            $payload->parkingSlotNumber(),
            $payload->parkingSlotDescription()
        );

        $this->parkingRepository->save($parking);

        return $parkingSlot;
    }
}
