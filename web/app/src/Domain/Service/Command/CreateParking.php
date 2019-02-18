<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Service\Command\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Service\Factory\Parking as ParkingFactory;

class CreateParking
{
    /** @var ParkingFactory  */
    private $parkingFactory;

    /**
     * CreateParking constructor.
     * @param ParkingFactory $parkingFactory
     */
    public function __construct(ParkingFactory $parkingFactory)
    {
        $this->parkingFactory = $parkingFactory;
    }

    /**
     * @param User $loggedInUser
     * @param string $parkingName
     * @return Parking
     * @throws NotAuthorizedOperation
     */
    public function execute(User $loggedInUser, string $parkingName): Parking
    {
        if (!$loggedInUser->isAdministrator()) {
            throw new NotAuthorizedOperation('User cannot create a new Parking');
        }

        return $this->parkingFactory->create($loggedInUser, $parkingName);
    }
}