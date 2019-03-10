<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Service\Factory\Parking as ParkingFactory;
use Jmj\Parking\Domain\Repository\Parking as ParkingRepositoryInterface;

class CreateParking extends ParkingBaseCommand
{
    /**
     * @var User
     */
    protected $loggedInUser;

    /**
     * @var string
     */
    protected $parkingName;

    /**
     * @var Parking
     */
    private $parking;

    /**
     * @var ParkingFactory
     */
    private $parkingFactory;

    /**
     * @var ParkingRepositoryInterface
     */
    protected $parkingRepository;

    /**
     * CreateParking constructor.
     *
     * @param ParkingFactory             $parkingFactory
     * @param ParkingRepositoryInterface $parkingRepository
     */
    public function __construct(
        ParkingFactory $parkingFactory,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->parkingFactory = $parkingFactory;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * @param  User   $loggedInUser
     * @param  string $description
     * @return Parking
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, string $description) : Parking
    {
        $this->loggedInUser = $loggedInUser;
        $this->parkingName = $description;

        $this->processCatchingDomainEvents();

        $this->parkingRepository->save($this->parking);

        return $this->parking;
    }

    /**
     * @throws NotAuthorizedOperation
     */
    protected function process()
    {
        if (!$this->loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User cannot create a new Parking');
        }

        //TODO: check the description is unique
        $this->parking = $this->parkingFactory->create($this->parkingName);
    }
}
