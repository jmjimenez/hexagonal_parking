<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\ReserveParkingSlotToUser;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Service\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Service\ReserveParkingSlotForUserAndPeriod as UserReservesParkingSlotForPeriodService;
use Jmj\Parking\Infrastructure\Repository\Parking;
use Jmj\Parking\Infrastructure\Repository\User;

class UserReservesParkingSlotForPeriod
{
    /** @var User */
    private $userRepository;

    /** @var Parking  */
    private $parkingRepository;

    /** @var UserReservesParkingSlotForPeriodService  */
    private $userReservesParkingSlotForPeriodService;

    /**
     * UserReservesParkingSlotForDate constructor.
     * @param User $userRepository
     * @param Parking $parkingRepository
     * @param UserReservesParkingSlotForPeriodService $userReservesParkingSlotForDateSevice
     */
    public function __construct(
        User $userRepository,
        Parking $parkingRepository,
        UserReservesParkingSlotForPeriodService $userReservesParkingSlotForDateSevice
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->userReservesParkingSlotForPeriodService = $userReservesParkingSlotForDateSevice;
    }

    /**
     * @param ReserveParkingSlotToUser $command
     * @throws ParkingNotFound
     * @throws UserNotAssigned
     * @throws UserNotFound
     * @throws ParkingSlotNotFound
     */
    public function execute(ReserveParkingSlotToUser $command)
    {
        $user = $this->userRepository->findById($command->userId());
        if (false === $user) {
            throw new UserNotFound("User does not exist");
        }

        $parking = $this->parkingRepository->findById($command->parkingId());
        if (false === $parking) {
            throw new ParkingNotFound("Parking id is not valid");
        }

        $this->userReservesParkingSlotForPeriodService->execute(
            $parking,
            $user,
            $command->parkingSlotId(),
            $command->fromDate(),
            $command->toDate()
        );
    }
}
