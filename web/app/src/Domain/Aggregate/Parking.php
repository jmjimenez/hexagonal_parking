<?php

namespace Jmj\Parking\Domain\Aggregate;

use DateTimeImmutable;
use DateTimeInterface;
use Jmj\Parking\Common\DateRangeProcessor;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\ParkingSlotReservationsForDateIncorrect;
use Jmj\Parking\Domain\Exception\UserIsNotAdministrator;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;

abstract class Parking extends BaseAggregate
{
    const EVENT_PARKING_DELETED = 'ParkingDeleted';
    const EVENT_PARKING_SLOT_ADDED_TO_PARKING = 'ParkingSlotAddedToParking';
    const EVENT_PARKING_SLOT_DELETED_FROM_PARKING = 'ParkingSlotDeletedFromParking';
    const EVENT_ADMINISTRATOR_ADDED_TO_PARKING = 'AdministratorAddedToParking';
    const EVENT_ADMINISTRATOR_REMOVED_FROM_PARKING = 'AdministratorRemovedFromParking';
    const EVENT_USER_ADDED_TO_PARKING = 'UserAddedToParking';
    const EVENT_USER_REMOVED_FROM_PARKING = 'UserRemovedFromParking';
    const EVENT_PARKING_CREATED = 'ParkingCreated';

    /** @var string  */
    private $description;

    /**
     * @param string $number
     * @param string $description
     * @return ParkingSlot
     */
    abstract protected function _createParkingSlot(string $number, string $description) : ParkingSlot;

    /**
     * @param ParkingSlot $parkingSlot
     */
    abstract protected function _addParkingSlot(ParkingSlot $parkingSlot);

    /**
     * @param string $parkingSlotUuid
     * @return ParkingSlot|null
     */
    abstract protected function _getParkingSlotByUuid(string $parkingSlotUuid) : ?ParkingSlot;

    /**
     * @param string $number
     * @return ParkingSlot|null
     */
    abstract protected function _getParkingSlotByNumber(string $number) : ?ParkingSlot;

    /**
     * @return ParkingSlot[]
     */
    abstract protected function _getParkingSlots();

    /**
     * @param string $userUuid
     * @return User|null
     */
    abstract protected function _getUserByUuid(string $userUuid) : ?User;

    /**
     * @param User $user
     * @param bool $isAdministrator
     */
    abstract protected function _addUser(User $user, bool $isAdministrator);

    /**
     * @param User $user
     */
    abstract protected function _removeUser(User $user);

    /**
     * @param User $user
     * @return mixed
     */
    abstract protected function _addAdministrator(User $user);

    /**
     * @return User[]
     */
    abstract protected function _getAdministrators();

    /**
     * @return int
     */
    abstract protected function _countParkingSlots() : int;

    /**
     * @param string $parkingSlotUuid
     * @return mixed
     */
    abstract protected function _deleteParkingSlotByUuid(string $parkingSlotUuid);

    /**
     * @param string $userName
     * @return User|null
     */
    abstract protected function _getUserByName(string $userName) : ?User;

    /**
     * @param User $user
     */
    abstract protected function _removeAdministrator(User $user);

    /**
     * Parking constructor.
     * @param string $description
     * @throws ExceptionGeneratingUuid
     */
    public function __construct(string $description)
    {
        parent::__construct();

        $this->description = $description;

        $this->publishEvent(self::EVENT_PARKING_CREATED);
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @param string $number
     * @param string $description
     * @return ParkingSlot
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function createParkingSlot(string $number, string $description): ParkingSlot
    {
        if ($this->getParkingSlotByNumber($number)) {
            throw new ParkingSlotNumberAlreadyExists('Parking slot already exists');
        }

        $parkingSlot = $this->_createParkingSlot($number, $description);

        $this->_addParkingSlot($parkingSlot);

        $this->publishEvent(self::EVENT_PARKING_SLOT_ADDED_TO_PARKING, $parkingSlot);

        return $parkingSlot;
    }

    /**
     * @return int
     */
    public function countParkingSlots(): int
    {
        return $this->_countParkingSlots();
    }

    /**
     * @param string $parkingSlotUuid
     * @throws ParkingSlotNotFound
     */
    public function deleteParkingSlotByUuid(string $parkingSlotUuid)
    {
        $parkingSlot = $this->_getParkingSlotByUuid($parkingSlotUuid);

        if (!$parkingSlot instanceof ParkingSlot) {
            throw new ParkingSlotNotFound('Parking Slot not found');
        }

        $parkingSlot->delete();
        $this->_deleteParkingSlotByUuid($parkingSlotUuid);

        $this->publishEvent(self::EVENT_PARKING_SLOT_DELETED_FROM_PARKING, $parkingSlot);
    }

    /**
     * @param $parkingSlotUuidd
     * @return ParkingSlot
     */
    public function getParkingSlotByUuid(string $parkingSlotUuidd): ?ParkingSlot
    {
        return $this->_getParkingSlotByUuid($parkingSlotUuidd);
    }

    /**
     * @param string $number
     * @return ParkingSlot|null
     */
    public function getParkingSlotByNumber(string $number) : ?ParkingSlot
    {
        return $this->_getParkingSlotByNumber($number);
    }

    /**
     * @param User $user
     * @throws UserNameAlreadyExists
     */
    public function addAdministrator(User $user)
    {
        if (!$this->_getUserByUuid($user->uuid()) instanceof User) {
            $this->addUser($user, true);
            return;
        }

        $this->_addAdministrator($user);

        $this->publishEvent(self::EVENT_ADMINISTRATOR_ADDED_TO_PARKING, $user);
    }

    /**
     * @param User $user
     * @throws UserIsNotAdministrator
     * @throws UserNotAssigned
     */
    public function removeAdministrator(User $user)
    {
        if (!$this->_getUserByUuid($user->uuid()) instanceof User) {
            throw new UserNotAssigned();
        }

        if (!$this->isAdministeredByUser($user)) {
            throw new UserIsNotAdministrator();
        }

        $this->_removeAdministrator($user);

        $this->publishEvent(self::EVENT_ADMINISTRATOR_REMOVED_FROM_PARKING, $user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAdministeredByUser(User $user) : bool
    {
        foreach ($this->_getAdministrators() as $administrator) {
            if ($administrator->uuid() == $user->uuid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param bool $isAdministrator
     * @throws UserNameAlreadyExists
     */
    public function addUser(User $user, bool $isAdministrator = false)
    {
        if ($this->getUserByName($user->name())) {
            throw new UserNameAlreadyExists();
        }

        $this->_addUser($user, $isAdministrator);

        $this->publishEvent(self::EVENT_USER_ADDED_TO_PARKING, $user);

        if ($isAdministrator) {
            $this->publishEvent(self::EVENT_ADMINISTRATOR_ADDED_TO_PARKING, $user);
        }
    }

    /**
     * @param string $userName
     * @return User|null
     */
    public function getUserByName(string $userName) : ?User
    {
        return $this->_getUserByName($userName);
    }

    /**
     * @param User $user
     * @throws UserNotAssigned
     */
    public function removeUser(User $user)
    {
        $user = $this->_getUserByUuid($user->uuid());

        if (!$user instanceof User) {
            throw new UserNotAssigned('User not found');
        }

        foreach ($this->_getParkingSlots() as $parkingSlot) {
            $parkingSlot->removeUser($user);
        }

        $this->_removeUser($user);

        $this->publishEvent(self::EVENT_USER_REMOVED_FROM_PARKING, $user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isUserAssigned(User $user) : bool
    {
        return $this->_getUserByUuid($user->uuid()) instanceof User;
    }

    /**
     * @param User $user
     * @param DateTimeInterface $fromDate
     * @param DateTimeInterface $toDate
     * @return array
     * @throws UserNotAssigned
     * @throws \Exception
     */
    public function getUserInformation(User $user, DateTimeInterface $fromDate, DateTimeInterface $toDate) : array
    {
        if (!$this->_getUserByUuid($user->uuid()) instanceof User) {
            throw new UserNotAssigned();
        }

        $userInformation = [
            'reservations' => [],
            'assignments' => [],
        ];

        $dateRangeProcessor = new DateRangeProcessor();

        $dateRangeProcessor->process($fromDate, $toDate, function (DateTimeImmutable $date) use ($user, &$userInformation) {
            $reservations = $this->getParkingSlotsReservationsForDate($date);

            /** @var Reservation $reservation */
            foreach ($reservations as $reservation) {
                if ($reservation->user()->uuid() == $user->uuid()) {
                    $userInformation['reservations'][] = $reservation;
                }
            }

            $assignments = $this->getParkingSlotsAssignmentsForDate($date);

            /** @var Assignment $assignment */
            foreach ($assignments as $parkingSlotAssignments) {
                foreach ($parkingSlotAssignments as $assignment) {
                    if ($assignment->user()->uuid() == $user->uuid()) {
                        $userInformation['assignments'][] = $assignment;
                    }
                }
            }

        });

        return $userInformation;
    }

    /**
     * @param DateTimeImmutable $date
     * @return array
     * @throws ParkingSlotReservationsForDateIncorrect
     */
    public function getParkingSlotsReservationsForDate(DateTimeImmutable $date) : array
    {
        $parkingReservations = [];

        foreach ($this->_getParkingSlots() as $parkingSlot) {
            $parkingSlotReservations = $parkingSlot->getReservationsForPeriod($date, $date);

            if (count($parkingSlotReservations) > 1) {
                throw new ParkingSlotReservationsForDateIncorrect();
            }

            if (count($parkingSlotReservations) == 1) {
                $parkingReservations[$parkingSlot->number()] = $parkingSlotReservations[0];
            }
        }

        return $parkingReservations;
    }

    /**
     * @param DateTimeImmutable $date
     * @return array
     */
    public function getParkingSlotsAssignmentsForDate(DateTimeImmutable $date) : array
    {
        $parkingAssignments = [];

        //TODO: perhaps the result should be a plain array instead of a bidimensional array
        foreach ($this->_getParkingSlots() as $parkingSlot) {
            $parkingAssignments[$parkingSlot->number()] = $parkingSlot->getAssignmentsForPeriod($date, $date);
        }

        return $parkingAssignments;
    }

    /**
     *
     */
    public function delete()
    {
        foreach ($this->_getParkingSlots() as $parkingSlot) {
            $parkingSlot->delete();
        }

        $this->publishEvent(self::EVENT_PARKING_DELETED);
    }

    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return __CLASS__;
    }
}