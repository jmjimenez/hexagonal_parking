<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\FreeAssignedParkingSlotForUserAndPeriod
    as FreeAssignedParkingSlotForUserAndPeriodCommand;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\FreeAssignedParkingSlotForUserAndPeriod
    as FreeAssignedParkingSlotForUserAndPeriodDomainCommand;

class FreeAssignedParkingSlotForUserAndPeriod extends ParkingBaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository  */
    protected $userRepository;

    /**
     * @param ParkingRepository $parkingRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ParkingRepository $parkingRepository, UserRepository $userRepository)
    {
        $this->parkingRepository = $parkingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param FreeAssignedParkingSlotForUserAndPeriodCommand $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws \Jmj\Parking\Domain\Exception\ParkingException
     */
    public function execute(FreeAssignedParkingSlotForUserAndPeriodCommand $payload)
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
        $this->validateUser($loggedInUser);

        $user = $this->userRepository->findByUuid($payload->userUuid());
        $this->validateUser($user);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new FreeAssignedParkingSlotForUserAndPeriodDomainCommand($this->parkingRepository);

        $command->execute(
            $loggedInUser,
            $parking,
            $user,
            $payload->parkingSlotUuid(),
            $payload->fromDate(),
            $payload->toDate()
        );

        $this->parkingRepository->save($parking);
    }
}
