<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Application\Command\UserFreeAssignedParkingSlot;
use Jmj\Parking\Domain\Service\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Service\FreeAssignedParkingSlotForUserAndPeriod as UserFreesAssignedParkingSlotForPeriodService;
use Jmj\Parking\Infrastructure\Repository\Parking;
use Jmj\Parking\Infrastructure\Repository\User;

class UserFreesAssignedParkingSlotForPeriod
{
    /** @var User */
    private $userRepository;

    /** @var Parking  */
    private $parkingRepository;

    /** @var UserFreesAssignedParkingSlotForPeriodService */
    private $userFreesAssignedParkingSlotForPeriodService;

    public function __construct(
        User $userRepository,
        Parking $parkingRepository,
        UserFreesAssignedParkingSlotForPeriodService $userFreesAssignedParkingSlotForPeriodService
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->userFreesAssignedParkingSlotForPeriodService = $userFreesAssignedParkingSlotForPeriodService;
    }

    /**
     * @param UserFreeAssignedParkingSlot $command
     * @throws ParkingNotFound
     * @throws UserNotAssigned
     * @throws UserNotFound
     */
    public function execute(UserFreeAssignedParkingSlot $command)
    {
        $user = $this->userRepository->findById($command->userId());
        if (false === $user) {
            throw new UserNotFound("User does not exist");
        }

        $parking = $this->parkingRepository->findById($command->parkingId());
        if (false === $parking) {
            throw new ParkingNotFound("Parking id is not valid");
        }

        $this->userFreesAssignedParkingSlotForPeriodService->execute(
            $parking,
            $user,
            $command->parkingSlotId(),
            $command->fromDate(),
            $command->toDate()
        );
    }
}
