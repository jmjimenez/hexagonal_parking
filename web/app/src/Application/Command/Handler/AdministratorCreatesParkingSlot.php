<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\CreateParkingSlot;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Service\CreateParkingSlot as AdministratorCreatesParkingSlotService;
use Jmj\Parking\Domain\Service\Exception\NotAuthorizedOperation;
use Jmj\Parking\Infrastructure\Repository\Parking;
use Jmj\Parking\Infrastructure\Repository\User;

class AdministratorCreatesParkingSlot
{
    /** @var User  */
    private $userRepository;

    /** @var Parking  */
    private $parkingRepository;

    /** @var AdministratorCreatesParkingSlotService  */
    private $administratorCreatesParkingSlotService;

    /**
     * AdministratorCreatesParkingSlot constructor.
     * @param User $userRepository
     * @param Parking $parkingRepository
     * @param AdministratorCreatesParkingSlotService $administratorCreatesParkingSlot
     */
    public function __construct(
        User $userRepository,
        Parking $parkingRepository,
        AdministratorCreatesParkingSlotService $administratorCreatesParkingSlot
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->administratorCreatesParkingSlotService = $administratorCreatesParkingSlot;
    }

    /**
     * @param CreateParkingSlot $command
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws NotAuthorizedOperation
     */
    public function execute(CreateParkingSlot $command)
    {
        $administrator = $this->userRepository->findById($command->administratorId());
        if (false === $administrator) {
            throw new UserNotFound("Logged user does not exist");
        }

        $parking = $this->parkingRepository->findById($command->parkingId());
        if (false === $parking) {
            throw new ParkingNotFound("Parking does not exist");
        }

        $this->administratorCreatesParkingSlotService->execute(
            $administrator,
            $parking,
            $command->parkingNumber(),
            $command->parkingDescription()
        );
    }
}