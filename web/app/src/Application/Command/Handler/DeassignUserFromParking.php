<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\DeassignUserFromParking as DeassignUserFromParkingPayload;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\DeassignUserFromParking as DeassignUserFromParkingDomainCommand;

class DeassignUserFromParking extends ParkingBaseHandler
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
     * @param DeassignUserFromParkingPayload $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(DeassignUserFromParkingPayload $payload)
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
        $this->validateUser($loggedInUser);

        $user = $this->userRepository->findByUuid($payload->userUuid());
        $this->validateUser($user);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new DeassignUserFromParkingDomainCommand($this->parkingRepository);

        $command->execute($loggedInUser, $parking, $user);

        $this->parkingRepository->save($parking);
    }
}
