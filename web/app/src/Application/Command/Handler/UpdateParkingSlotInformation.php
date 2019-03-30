<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\UpdateParkingSlotInformation as UpdateParkingSlotInformationPayload;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\UpdateParkingSlotInformation as UpdateParkingSlotInformationCommand;

class UpdateParkingSlotInformation extends Common\BaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     * @param ParkingRepository $parkingRepository
     */
    public function __construct(
        UserRepository $userRepository,
        ParkingRepository $parkingRepository
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param UpdateParkingSlotInformationPayload $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(UpdateParkingSlotInformationPayload $payload)
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
        $this->validateUser($loggedInUser);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new UpdateParkingSlotInformationCommand();

        $command->execute(
            $loggedInUser,
            $parking,
            $payload->parkingSlotUuid(),
            $payload->number(),
            $payload->description()
        );

        $this->parkingRepository->save($parking);
    }
}
