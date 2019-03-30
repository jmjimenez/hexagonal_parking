<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\CreateUserForParking as CreateUserForParkingPayload;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\CreateUserForParking as CreateUserForParkingCommand;
use Jmj\Parking\Domain\Service\Factory\User as UserFactory;

class CreateUserForParking extends Common\BaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var UserFactory */
    protected $userFactory;

    /** @var PdoProxy */
    protected $pdoProxy;

    /**
     * @param PdoProxy $pdoProxy
     * @param UserRepository $userRepository
     * @param UserFactory $userFactory
     * @param ParkingRepository $parkingRepository
     */
    public function __construct(
        PdoProxy $pdoProxy,
        UserRepository $userRepository,
        UserFactory $userFactory,
        ParkingRepository $parkingRepository
    ) {
        $this->pdoProxy = $pdoProxy;
        $this->parkingRepository = $parkingRepository;
        $this->userFactory = $userFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CreateUserForParkingPayload $payload
     * @return User
     * @throws Exception\ParkingNotFound
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(CreateUserForParkingPayload $payload) : User
    {
        try {
            $this->pdoProxy->startTransaction();

            //TODO: all payloads may have the common property loggedInUserUuid
            $loggedInUser = $this->userRepository->findByUuid($payload->loggedInUserUuid());
            $this->validateUser($loggedInUser);

            $parking = $this->parkingRepository->findByUuid($payload->parkingUuid());
            $this->validateParking($parking);

            $command = new CreateUserForParkingCommand(
                $this->userRepository,
                $this->userFactory
            );

            $user = $command->execute(
                $loggedInUser,
                $parking,
                $payload->userName(),
                $payload->userEmail(),
                $payload->userPassword(),
                $payload->isAdministrator(),
                $payload->isAdministratorForParking()
            );

            $this->parkingRepository->save($parking);
            $this->userRepository->save($user);

            $this->pdoProxy->commitTransaction();
        } catch (Exception\ParkingNotFound | Exception\UserNotFound | ParkingException $exception) {
            $this->pdoProxy->rollbackTransaction();
            throw $exception;
        }

        return $user;
    }
}
