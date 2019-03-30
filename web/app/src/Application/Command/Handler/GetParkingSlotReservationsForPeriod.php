<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetParkingSlotReservationsForPeriod as GetParkingSlotReservationsForPeriodCommand;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking;
use Jmj\Parking\Domain\Repository\User;
use Jmj\Parking\Domain\Service\Command\GetParkingSlotReservationsForPeriod
    as GetParkingSlotReservationsForPeriodDomainCommand;

class GetParkingSlotReservationsForPeriod extends Common\BaseHandler
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
     * @param GetParkingSlotReservationsForPeriodCommand $payload
     * @return array
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(GetParkingSlotReservationsForPeriodCommand $payload) : array
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
        $this->validateUser($loggedInUser);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new GetParkingSlotReservationsForPeriodDomainCommand();

        $parkingSlotReservations = $command->execute(
            $loggedInUser,
            $parking,
            $payload->parkingSlotUuid(),
            $payload->fromDate(),
            $payload->toDate()
        );

        $result = [];
        foreach ($parkingSlotReservations as $reservation) {
            $result[] = $this->reservationToArray($reservation);
        }

        return $result;
    }
}
