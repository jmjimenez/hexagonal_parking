<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\DeleteParkingSlot as DeleteParkingSlotPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\DeleteParkingSlot as DeleteParkingSlotCommand;

class DeleteParkingSlot extends Common\BaseHandler
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
     * @param DeleteParkingSlotPayload $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(DeleteParkingSlotPayload $payload)
    {
        try {
            $this->pdoProxy->startTransaction();

            $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
            $this->validateUser($loggedInUser);

            $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
            $this->validateParking($parking);

            $command = new DeleteParkingSlotCommand();

            $command->execute($loggedInUser, $parking, $payload->parkingSlotUuid());

            $this->parkingRepository->save($parking);

            $this->pdoProxy->commitTransaction();
        } catch (Exception\ParkingNotFound | Exception\UserNotFound | ParkingException $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }
    }
}
