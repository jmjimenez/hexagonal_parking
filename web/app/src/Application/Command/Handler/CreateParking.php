<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\CreateParking as CreateParkingPayload;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\CreateParking as CreateParkingCommand;
use Jmj\Parking\Domain\Service\Factory\Parking as ParkingFactory;

class CreateParking extends Common\BaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var ParkingFactory */
    protected $parkingFactory;

    /**
     * @param UserRepository $userRepository
     * @param ParkingFactory $parkingFactory
     * @param ParkingRepository $parkingRepository
     */
    public function __construct(
        UserRepository $userRepository,
        ParkingFactory $parkingFactory,
        ParkingRepository $parkingRepository
    ) {
        $this->parkingFactory = $parkingFactory;
        $this->parkingRepository = $parkingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CreateParkingPayload $payload
     * @return Parking
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(CreateParkingPayload $payload) : Parking
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
        $this->validateUser($loggedInUser);

        $command = new CreateParkingCommand($this->parkingFactory);

        $parking = $command->execute($loggedInUser, $payload->description());

        $this->parkingRepository->save($parking);

        return $parking;
    }
}
