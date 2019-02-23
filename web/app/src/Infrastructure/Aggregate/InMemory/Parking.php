<?php

namespace Jmj\Parking\Infrastructure\Aggregate\InMemory;

use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotDescriptionInvalid;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberInvalid;
use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot as DomainParkingSlot;
use Jmj\Parking\Domain\Aggregate\User;

class Parking extends DomainParking
{
    /** @var DomainParkingSlot[]  */
    protected $parkingSlots = [];

    /** @var User[] */
    protected $users = [];

    /** @var bool[] */
    protected $administrators = [];

    /**
     * @inheritdoc
     */
    protected function _countParkingSlots(): int
    {
        return count($this->parkingSlots);
    }

    /**
     * @inheritdoc
     * @param string $number
     * @param string $description
     * @return DomainParkingSlot
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberInvalid
     * @throws ExceptionGeneratingUuid
     */
    protected function _createParkingSlot(string $number, string $description) : DomainParkingSlot
    {
        return new ParkingSlot($this, $number, $description);
    }

    /**
     * @inheritdoc
     */
    protected function _addParkingSlot(DomainParkingSlot $parkingSlot)
    {
        $this->parkingSlots[$parkingSlot->uuid()] = $parkingSlot;
    }

    /**
     * @inheritdoc
     */
    protected function _deleteParkingSlotByUuid(string $parkingSlotUuid)
    {
        unset($this->parkingSlots[$parkingSlotUuid]);
    }

    /**
     * @inheritdoc
     */
    protected function _getParkingSlotByUuid(string $parkingSlotUuid) : ?DomainParkingSlot
    {
        return isset($this->parkingSlots[$parkingSlotUuid]) ? $this->parkingSlots[$parkingSlotUuid] : null;
    }

    /**
     * @inheritdoc
     */
    protected function _getParkingSlotByNumber(string $number): ?DomainParkingSlot
    {
        foreach ($this->parkingSlots as $parkingSlot) {
            if ($parkingSlot->number() === $number) {
                return $parkingSlot;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function _getParkingSlots()
    {
        return $this->parkingSlots;
    }

    /**
     * @inheritdoc
     */
    protected function _getUserByUuid(string $userUuid): ?User
    {
        return isset($this->users[$userUuid]) ? $this->users[$userUuid] : null;
    }

    /**
     * @inheritdoc
     */
    protected function _getUserByName(string $userName): ?User
    {
        foreach ($this->users as $user) {
            if ($user->name() == $userName) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function _addUser(User $user, bool $isAdministrator)
    {
        $this->users[$user->uuid()] = $user;
        $this->administrators[$user->uuid()] = $isAdministrator;
    }

    /**
     * @inheritdoc
     */
    protected function _removeUser(User $user)
    {
        if (isset($this->users[$user->uuid()])) {
            unset($this->users[$user->uuid()]);
            unset($this->administrators[$user->uuid()]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _addAdministrator(User $user)
    {
        if (isset($this->users[$user->uuid()])) {
            $this->administrators[$user->uuid()] = true;
        }
    }

    /**
     * @inheritdoc
     */
    protected function _removeAdministrator(User $user)
    {
        $this->administrators[$user->uuid()] = false;
    }

    /**
     * @inheritdoc
     */
    protected function _getAdministrators()
    {
        $administrators = [];

        foreach ($this->administrators as $userId => $isAdministrator) {
            if ($isAdministrator) {
                $administrators[] = $this->users[$userId];
            }
        }

        return $administrators;
    }
}