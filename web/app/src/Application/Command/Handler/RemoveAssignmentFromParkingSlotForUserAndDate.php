<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\RemoveAssignmentFromParkingSlotForUserAndDate
    as RemoveAssignmentFromParkingSlotForUserAndDateCommand;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\RemoveAssignmentFromParkingSlotForUserAndDate
    as RemoveAssignmentFromParkingSlotForUserAndDateDomainCommand;

class RemoveAssignmentFromParkingSlotForUserAndDate extends Common\BaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository  */
    protected $userRepository;

    /** @var PdoProxy */
    protected $pdoProxy;

    /**
     * @param PdoProxy $pdoProxy
     * @param ParkingRepository $parkingRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        PdoProxy $pdoProxy,
        ParkingRepository $parkingRepository,
        UserRepository $userRepository
    ) {
        $this->pdoProxy = $pdoProxy;
        $this->parkingRepository = $parkingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param RemoveAssignmentFromParkingSlotForUserAndDateCommand $payload
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(RemoveAssignmentFromParkingSlotForUserAndDateCommand $payload)
    {
        try {
            $this->pdoProxy->startTransaction();

            $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
            $this->validateUser($loggedInUser);

            $user = $this->userRepository->findByUuid($payload->userUuid());
            $this->validateUser($user);

            $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
            $this->validateParking($parking);

            $command = new RemoveAssignmentFromParkingSlotForUserAndDateDomainCommand();

            $command->execute(
                $loggedInUser,
                $parking,
                $payload->parkingSlotUuid(),
                $user,
                $payload->date()
            );

            $this->parkingRepository->save($parking);

            $this->pdoProxy->commitTransaction();
        } catch (Exception\ParkingNotFound | Exception\UserNotFound | ParkingException $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }
    }
}
