<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetParkingSlotsReservationsForDate;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Service\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Service\GetParkingSlotsReservationsForDate as UserGetsParkingReservationsForDateService;
use Jmj\Parking\Infrastructure\Repository\Parking;
use Jmj\Parking\Infrastructure\Repository\User;

class UserGetsParkingSlotsReservationsForDate
{
    /** @var User  */
    private $userRepository;

    /** @var Parking */
    private $parkingRepository;

    /** @var UserGetsParkingReservationsForDateService  */
    private $userGetsParkingReservationsForDateService;

    public function __construct(
        User $userRepository,
        Parking $parkingRepository,
        UserGetsParkingReservationsForDateService $userGetsParkingReservationsForDateService
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->userGetsParkingReservationsForDateService = $userGetsParkingReservationsForDateService;
    }

    /**
     * @param GetParkingSlotsReservationsForDate $command
     * @return array
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws UserNotAssigned
     */
    public function execute(GetParkingSlotsReservationsForDate $command)
    {
        $user = $this->userRepository->findById($command->userId());
        if (false === $user) {
            throw new UserNotFound("User does not exist");
        }

        $parking = $this->parkingRepository->findById($command->parkingId());
        if (false === $parking) {
            throw new ParkingNotFound("Parking id is not valid");
        }

        return $this->userGetsParkingReservationsForDateService->execute($parking, $user, $command->date());
    }
}