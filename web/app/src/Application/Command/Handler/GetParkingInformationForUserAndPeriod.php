<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetParkingInformationForUserAndPeriod
    as GetParkingInformationForUserAndPeriodCommand;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\GetParkingInformationForUserAndPeriod
    as GetParkingInformationForUserAndPeriodDomainCommand;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;

class GetParkingInformationForUserAndPeriod extends ParkingBaseHandler
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
     * @param GetParkingInformationForUserAndPeriodCommand $payload
     * @return array
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(GetParkingInformationForUserAndPeriodCommand $payload) : array
    {
        $user = $this->userRepository->findByUuid($payload->userUuid());
        $this->validateUser($user);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new GetParkingInformationForUserAndPeriodDomainCommand();

        $parkingInformation = $command->execute($parking, $user, $payload->fromDate(), $payload->toDate());

        $result = [
            'reservations' => [],
            'assignments' => []
        ];

        /** @var Reservation $reservation */
        foreach ($parkingInformation['reservations'] as $reservation) {
            $result['reservations'][] = [
                'parkingUuid' => $reservation->parkingSlot()->parking()->uuid(),
                'parkingSlotUuid' => $reservation->parkingSlot()->uuid(),
                'userUuid' => $reservation->user()->uuid(),
                'date' => $reservation->date()->format('Y-m-d'),
            ];
        }

        /** @var Assignment $assignment */
        foreach ($parkingInformation['assignments'] as $assignment) {
            $result['assignments'][] = [
                'parkingUuid' => $assignment->parkingSlot()->parking()->uuid(),
                'parkingSlotUuid' => $assignment->parkingSlot()->uuid(),
                'userUuid' => $assignment->user()->uuid(),
                'date' => $assignment->date()->format('Y-m-d'),
                'exclusive' => $assignment->isExclusive() ? 'true' : 'false',
            ];
        }

        return $result;
    }
}
