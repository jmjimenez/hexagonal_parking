<?php

namespace Jmj\Parking\Application\Command\Handler;

use Jmj\Parking\Application\Command\CreateParking as CreatesParkingPayload;
use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepository;
use Jmj\Parking\Domain\Repository\User as UserRepository;
use Jmj\Parking\Domain\Service\Command\CreateParking as CreatesParkingCommand;
use Jmj\Parking\Domain\Service\Factory\Parking as ParkingFactory;

class CreateParking extends ParkingBaseHandler
{
    /** @var ParkingRepository */
    protected $parkingRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var ParkingFactory */
    protected $parkingFactory;

    /**
     * AssignAdministratorRightsToUserForParking constructor.
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
     * @param CreatesParkingPayload $payload
     * @return \Jmj\Parking\Domain\Aggregate\Parking
     * @throws Exception\UserNotFound
     * @throws ParkingException
     */
    public function execute(CreatesParkingPayload $payload) : Parking
    {
        $loggedInUser = $this->userRepository->findByUuid($payload->loggedUserUuid());
        $this->validateUser($loggedInUser);

        $command = new CreatesParkingCommand($this->parkingFactory, $this->parkingRepository);

        $parking = $command->execute($loggedInUser, $payload->description());

        $this->parkingRepository->save($parking);

        return $parking;
    }
}
