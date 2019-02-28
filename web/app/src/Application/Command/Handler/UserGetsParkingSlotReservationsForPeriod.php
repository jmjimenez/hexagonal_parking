<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetParkingSlotReservationsForPeriod;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Service\GetParkingSlotReservationsForPeriod as UserGetsParkingSlotReservationsForPeriodService;
use Jmj\Parking\Infrastructure\Repository\Parking;
use Jmj\Parking\Infrastructure\Repository\User;

class UserGetsParkingSlotReservationsForPeriod
{
    /** @var User  */
    private $userRepository;

    /** @var Parking */
    private $parkingRepository;

    /** @var UserGetsParkingSlotReservationsForPeriodService  */
    private $userGetsParkingSlotReservationsForPeriodService;

    public function __construct(
        User $userRepository,
        Parking $parkingRepository,
        UserGetsParkingSlotReservationsForPeriodService $userGetsParkingSlotReservationsForPeriodService
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->userGetsParkingSlotReservationsForPeriodService = $userGetsParkingSlotReservationsForPeriodService;
    }

    /**
     * @param GetParkingSlotReservationsForPeriod $command
     * @return array
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws \Jmj\Parking\Domain\Service\Exception\UserNotAssigned
     */
    public function execute(GetParkingSlotReservationsForPeriod $command)
    {
        $user = $this->userRepository->findById($command->userId());
        if (false === $user) {
            throw new UserNotFound("User does not exist");
        }

        $parking = $this->parkingRepository->findById($command->parkingId());
        if (false === $parking) {
            throw new ParkingNotFound("Parking id is not valid");
        }

        return $this->userGetsParkingSlotReservationsForPeriodService->execute(
            $parking,
            $user,
            $command->parkingSlotId(),
            $command->fromDate(),
            $command->toDate()
        );
    }
}
