<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetParkingReservationsForDate as GetParkingReservationsForDateCommand;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Repository\Parking;
use Jmj\Parking\Domain\Repository\User;
use Jmj\Parking\Domain\Service\Command\GetParkingReservationsForDate as GetParkingReservationsForDateDomainCommand;

class GetParkingReservationsForDate extends ParkingBaseHandler
{
    /** @var User  */
    private $userRepository;

    /** @var Parking */
    private $parkingRepository;

    public function __construct(
        User $userRepository,
        Parking $parkingRepository
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param GetParkingReservationsForDateCommand $payload
     * @return array
     * @throws ParkingNotFound
     * @throws UserNotFound
     * @throws \Jmj\Parking\Domain\Exception\ParkingException
     */
    public function execute(GetParkingReservationsForDateCommand $payload)
    {
        $user = $this->userRepository->findByUuid($payload->userUuid());
        $this->validateUser($user);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new GetParkingReservationsForDateDomainCommand();

        return $command->execute($user, $parking, $payload->date());
    }
}
