<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\CreateParkingSlot as CreateParkingSlotPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\CreateParkingSlot as CreateParkingSlotCommand;

class CreateParkingSlot extends Common\BaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var PdoProxy */
    protected $pdoProxy;

    /**
     * @param PdoProxy $pdoProxy
     * @param UserRepository $userRepository
     * @param ParkingRepository $parkingRepository
     */
    public function __construct(
        PdoProxy $pdoProxy,
        UserRepository $userRepository,
        ParkingRepository $parkingRepository
    ) {
        $this->pdoProxy = $pdoProxy;
        $this->parkingRepository = $parkingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CreateParkingSlotPayload $payload
     * @return ParkingSlot
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(CreateParkingSlotPayload $payload) : ParkingSlot
    {
        try {
            $this->pdoProxy->startTransaction();

            $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
            $this->validateUser($loggedInUser);

            $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
            $this->validateParking($parking);

            $command = new CreateParkingSlotCommand();

            $parkingSlot = $command->execute(
                $loggedInUser,
                $parking,
                $payload->parkingSlotNumber(),
                $payload->parkingSlotDescription()
            );

            $this->parkingRepository->save($parking);

            $this->pdoProxy->commitTransaction();
        } catch (Exception\ParkingNotFound | Exception\UserNotFound | ParkingException $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }

        return $parkingSlot;
    }
}
