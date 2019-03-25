<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\DeleteParkingSlot as DeleteParkingSlotPayload;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\DeleteParkingSlot as DeleteParkingSlotCommand;

class DeleteParkingSlot extends Common\BaseHandler
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
     * @param DeleteParkingSlotPayload $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws \Jmj\Parking\Domain\Exception\ParkingException
     */
    public function execute(DeleteParkingSlotPayload $payload)
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
        $this->validateUser($loggedInUser);

        $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
        $this->validateParking($parking);

        $command = new DeleteParkingSlotCommand();

        $command->execute($loggedInUser, $parking, $payload->parkingSlotUuid());

        $this->parkingRepository->save($parking);
    }
}
