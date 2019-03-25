<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\GetParkingInformationForUserAndPeriod
    as GetParkingInformationForUserAndPeriodCommand;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\GetParkingInformationForUserAndPeriod
    as GetParkingInformationForUserAndPeriodDomainCommand;

class GetParkingInformationForUserAndPeriod extends Common\BaseHandler
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

        foreach ($parkingInformation['reservations'] as $reservation) {
            $result['reservations'][] = $this->reservationToArray($reservation);
        }

        foreach ($parkingInformation['assignments'] as $assignment) {
            $result['assignments'][] = $this->assignmentToArray($assignment);
        }

        return $result;
    }
}
