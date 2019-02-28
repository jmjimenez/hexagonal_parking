<?php

namespace Jmj\Test\Unit\Infrastructure\Repository\Pdo;

use DateTimeImmutable;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\ParkingSlot;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use Jmj\Parking\Infrastructure\Repository\Pdo\Parking as PdoParkingRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ParkingTest extends TestCase
{
    const PARKING_DESCRIPTION = 'Parking Test';

    /** @var PdoParkingRepository */
    protected static $parkingRepository;

    /** @var PdoProxy */
    protected static $pdoProxy;

    /** @var Parking */
    protected static $parking;

    /** @var ParkingSlot[] $parkingSlots */
    protected static $parkingSlots = [];

    /** @var User[] $users */
    protected static $users = [];

    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws ParkingSlotNumberAlreadyExists
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$pdoProxy = new PdoProxy();
        self::$pdoProxy->connectToSqlite(':memory:');


        self::$parkingRepository = new PdoParkingRepository('Parking', self::$pdoProxy);
        self::$parkingRepository->initializeRepository();
        self::$parking = self::createParkingTest();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$pdoProxy = null;
    }

    /**
     * @throws \Exception
     */
    public function testSaveWhenNew()
    {
        $result = self::$parkingRepository->save(self::$parking);
        $this->assertEquals(1, $result);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws PdoExecuteError
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testSaveWhenUpdating()
    {
        $newUser = new User('administrator', 'admin@test.com', 'adminpasswd', false);
        self::$parking->addUser($newUser, true);

        $result = self::$parkingRepository->save(self::$parking);
        $this->assertEquals(1, $result);

        $parkingFound = self::$parkingRepository->findByUuid(self::$parking->uuid());
        $this->assertTrue($parkingFound->isAdministeredByUser($newUser));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws PdoExecuteError
     * @throws \Exception
     */
    public function testDelete()
    {
        $parking = new Parking('Parking new');

        $result = self::$parkingRepository->save($parking);
        $this->assertEquals(1, $result);

        $result = self::$parkingRepository->delete($parking);
        $this->assertEquals(1, $result);

        $parkingFound = self::$parkingRepository->findByUuid($parking->uuid());
        $this->assertNull($parkingFound);
    }

    /**
     * @throws PdoExecuteError
     */
    public function testFindByUuid()
    {
        $parkingFound = self::$parkingRepository->findByUuid(self::$parking->uuid());

        $this->assertInstanceOf(Parking::class, $parkingFound);
        $this->assertEquals(self::$parking->uuid(), $parkingFound->uuid());

        foreach (self::$parkingSlots as $parkingSlot) {
            $this->assertEquals($parkingSlot->uuid(), $parkingFound->getParkingSlotByUuid($parkingSlot->uuid())->uuid());
        }

        foreach (self::$users as $user) {
            $this->assertTrue($parkingFound->isUserAssigned($user));
        }
    }

    /**
     * @throws PdoExecuteError
     * @throws \Exception
     */
    public function testFindByUuidWhenNotFound()
    {
        $uuid = Uuid::uuid4()->__toString();
        $parking = self::$parkingRepository->findByUuid($uuid);
        $this->assertNull($parking);
    }

    /**
     * @throws InvalidDateRange
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    protected static function createParkingTest() : Parking
    {
        $parking = new Parking(self::PARKING_DESCRIPTION);

        foreach (['1', '2', '3'] as $parkingNumber) {
            self::$parkingSlots[] = $parking->createParkingSlot($parkingNumber, "Parking Slot number {$parkingNumber}");
        }

        foreach (['one', 'two', 'three'] as $userName) {
            $user = new User(
                $userName,
                "{$userName}@test.com",
                "password{$userName}",
                false
            );

            $parking->addUser($user);
            self::$users[] = $user;
        }

        $assignFromDate = new DateTimeImmutable('+3 days');
        $assignToDate = new DateTimeImmutable('+13 days');

        for ($i = 0; $i < 3; $i++) {
            self::$parkingSlots[$i]->assignToUserForPeriod(self::$users[$i], $assignFromDate, $assignToDate, true);
        }

        return $parking;
    }
}

