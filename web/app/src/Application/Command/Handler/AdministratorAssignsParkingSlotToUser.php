<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Aggregate\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Service\AssignParkingSlotToUser as AdministratorAssignsParkingSlotToUserService;
use Jmj\Parking\Domain\Service\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Exception\UserNotAssigned;
use Jmj\Parking\Infrastructure\Repository\Parking;
use Jmj\Parking\Infrastructure\Repository\User;
use Jmj\Parking\Application\Command\AssignParkingSlotToUserForPeriod;

class AdministratorAssignsParkingSlotToUser
{
    /** @var User  */
    private $userRepository;

    /** @var Parking */
    private $parkingRepository;

    /** @var AdministratorAssignsParkingSlotToUserService  */
    private $administratorAssignsParkingSlotToUserService;

    public function __construct(
        User $userRepository,
        Parking $parkingRepository,
        AdministratorAssignsParkingSlotToUserService $administratorAssignsParkingSlotToUserService
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->administratorAssignsParkingSlotToUserService = $administratorAssignsParkingSlotToUserService;
    }

    /**
     * @param AssignParkingSlotToUserForPeriod $command
     * @throws ParkingNotFound
     * @throws ParkingSlotNotFound
     * @throws UserNotFound
     * @throws NotAuthorizedOperation
     * @throws UserNotAssigned
     */
    public function execute(AssignParkingSlotToUserForPeriod $command)
    {
        $administrator = $this->userRepository->findById($command->administratorId());
        if (false === $administrator) {
            throw new UserNotFound("Logged user does not exist");
        }

        $user = $this->userRepository->findById($command->userId());
        if (false === $user) {
            throw new UserNotFound("User does not exist");
        }

        $parking = $this->parkingRepository->findById($command->parkingId());
        if (false === $parking) {
            throw new ParkingNotFound("Parking id is not valid");
        }

        $this->administratorAssignsParkingSlotToUserService->execute(
            $administrator,
            $user,
            $parking,
            $command->parkingSlotId(),
            $command->fromDate(),
            $command->toDate(),
            $command->exclusive()
        );
    }
}
