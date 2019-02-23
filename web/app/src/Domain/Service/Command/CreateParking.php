<?php

namespace Jmj\Parking\Domain\Service\Command;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Service\Factory\Parking as ParkingFactory;

class CreateParking extends ParkingBaseCommand
{
    /** @var User */
    protected $loggedInUser;

    /** @var string */
    protected $parkingName;

    /** @var Parking */
    private $parking;

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
     * @param string $description
     * @return Parking
     * @throws ParkingException
     */
    public function execute(User $loggedInUser, string $description) : Parking
    {
        //TODO: create a parking repository or a parking collection

        $this->loggedInUser = $loggedInUser;
        $this->parkingName = $description;

        $this->processCatchingDomainEvents();

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

        $this->parking = $this->parkingFactory->create($this->parkingName);
    }
}