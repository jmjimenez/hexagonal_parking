<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\ReserveParkingSlotForUserAndPeriod as ReserveParkingSlotForUserAndPeriodCommand;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\ReserveParkingSlotForUserAndPeriod
    as ReserveParkingSlotForUserAndPeriodDomainCommand;

class ReserveParkingSlotForUserAndPeriod extends ParkingBaseHandler
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
     * @param ReserveParkingSlotForUserAndPeriodCommand $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(ReserveParkingSlotForUserAndPeriodCommand $payload)
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
        $this->validateUser($loggedInUser);

        $user = $this->userRepository->findByUuid($payload->userUuid());
        $this->validateUser($user);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new ReserveParkingSlotForUserAndPeriodDomainCommand();

        $command->execute(
            $parking,
            $user,
            $payload->parkingSlotUuid(),
            $payload->fromDate(),
            $payload->toDate()
        );

        $this->parkingRepository->save($parking);
    }
}
