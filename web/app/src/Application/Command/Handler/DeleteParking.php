<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\DeleteParking as DeleteParkingPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\DeleteParking as DeleteParkingCommand;

class DeleteParking extends Common\BaseHandler
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
     * @param DeleteParkingPayload $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(DeleteParkingPayload $payload)
    {
        try {
            $this->pdoProxy->startTransaction();

            $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
            $this->validateUser($loggedInUser);

            $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
            $this->validateParking($parking);

            $command = new DeleteParkingCommand();

            $command->execute($loggedInUser, $parking);

            $this->parkingRepository->delete($parking);

            $this->pdoProxy->commitTransaction();
        } catch (Exception\ParkingNotFound | Exception\UserNotFound | ParkingException $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }
    }
}
