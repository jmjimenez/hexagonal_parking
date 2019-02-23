<?php

namespace Jmj\Test\Unit\Infrastructure\Aggregate\InMemory;

use DateTimeImmutable;
use DateTimeInterface;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Domain\Aggregate\BaseAggregate;
use Jmj\Parking\Domain\Aggregate\Parking as DomainParking;
use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserIsNotAdministrator;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Value\Assignment;
use Jmj\Parking\Domain\Value\Reservation;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use Jmj\Test\Unit\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ParkingTest extends TestCase
{
    Use DomainEventsRegister;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        BaseAggregate::setDomainEventBroker($this->getEventBroker());
        $this->getEventBroker()->resetSubscriptions();
    }

    /**
     *
     * @throws ExceptionGeneratingUuid
     */
    public function testCreateParking()
    {
        $description = 'createParkingTest';

        $this->startRecordingEvents();

        $parking = $this->createParking($description);

        $this->assertEquals([ DomainParking::EVENT_PARKING_CREATED ], $this->recordedEventNames);
        $this->assertEquals([ DomainParking::class ], $this->recordedClasses);
        $this->assertEquals([ $parking ], $this->recordedObjects);

        $this->assertInstanceOf(DomainParking::class, $parking);
    }

    /**
     * @throws ExceptionGeneratingUuid
     */
    public function testDescription()
    {
        $description = 'Parking description';

        $parking = $this->createParking($description);

        $this->assertEquals($description, $parking->description());

    }

    /**
     * @throws ExceptionGeneratingUuid
     */
    public function testCreateParkingErrorWhenWrongDescription()
    {
        $this->expectExceptionMessageRegExp(
            '/Argument 1 passed to .*construct.* must be of the type string, null given.*/'
        );

        $parking = new Parking(null);

        $this->assertNull($parking);
    }

    /**
     *
     * @throws ExceptionGeneratingUuid
     */
    public function testCreateTwoParkings()
    {
        $description1 = 'createParkingTest1';
        $description2 = 'createParkingTest2';

        $parking1 = $this->createParking($description1);
        $parking2 = $this->createParking($description2);

        $this->assertEquals($description1, $parking1->description());
        $this->assertEquals($description2, $parking2->description());
        $this->assertNotEquals($parking1->uuid(), $parking2->uuid());
    }

    /**
     *
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testCreateParkingSlot()
    {
        $number = '222';
        $description = 'description';

        $parking = $this->createParking();

        $this->startRecordingEvents();

        $parkingSlot = $parking->createParkingSlot($number, $description);

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_CREATED, DomainParking::EVENT_PARKING_SLOT_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );
        $this->assertEquals([ ParkingSlot::class, DomainParking::class  ], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot, $parking ], $this->recordedObjects);
        $this->assertEquals([ null, $parkingSlot ], $this->recordedPayloads);

        $this->assertIsString($parkingSlot->uuid());
        $this->assertEquals($number, $parkingSlot->number());
        $this->assertEquals($description, $parkingSlot->description());
    }

    /**
     *
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testCreateTwoParkingSlots()
    {
        $parkingSlotsInfo = [
            [ 'number' => '001', 'description' => 'Parking 001' ],
            [ 'number' => '002', 'description' => 'Parking 002' ],
        ];

        $parking = $this->createParking();

        $parkingSlots = [];

        foreach ($parkingSlotsInfo as $index => $parkingSlotInfo) {
            $parkingSlots[$index] = $parking->createParkingSlot($parkingSlotInfo['number'], $parkingSlotInfo['description']);
        }

        /** @var  ParkingSlot $parkingSlot */
        foreach ($parkingSlots as $index => $parkingSlot) {
            $this->assertEquals($parkingSlot->number(), $parkingSlotsInfo[$index]['number']);
            $this->assertEquals($parkingSlot->description(), $parkingSlotsInfo[$index]['description']);
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testCreateParkingSlotErrorWhenNumberAlreadyExists()
    {
        $number = '001';
        $description1 = 'Parking 001 - Test 1';
        $description2 = 'Parking 001 - Test 2';

        $parking = $this->createParking();

        $parking->createParkingSlot($number, $description1);

        $this->expectException(ParkingSlotNumberAlreadyExists::class);
        $parking->createParkingSlot($number, $description2);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testCountParkingSlots()
    {
        $countParkingSlots = 5;

        $parking = $this->createParking();

        for ($i = 0; $i < $countParkingSlots; $i++) {
            $parking->createParkingSlot($i, $i);
        }

        $this->assertEquals($countParkingSlots, $parking->countParkingSlots());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNotFound
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testDeleteParkingSlotByUuid()
    {
        $parkingSlotsInfo = [
            [ 'number' => '001', 'description' => 'Parking Slot 001' ],
            [ 'number' => '002', 'description' => 'Parking Slot 002' ],
            [ 'number' => '003', 'description' => 'Parking Slot 003' ],
            [ 'number' => '004', 'description' => 'Parking Slot 004' ],
        ];

        $this->getEventBroker()->resetSubscriptions();
        $parking = $this->createParking();

        /** @var ParkingSlot[] $parkingSlots */
        $parkingSlots = [];

        foreach ($parkingSlotsInfo as $index => $parkingSlotInfo) {
            $parkingSlots[$index] = $parking->createParkingSlot(
                $parkingSlotInfo['number'],
                $parkingSlotInfo['description']
            );
        }

        $this->startRecordingEvents();

        $parking->deleteParkingSlotByUuid($parkingSlots[0]->uuid());

        $this->assertEquals([ ParkingSlot::class, DomainParking::class ], $this->recordedClasses);
        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_DELETED, DomainParking::EVENT_PARKING_SLOT_DELETED_FROM_PARKING ],
            $this->recordedEventNames
        );
        $this->assertEquals([ $parkingSlots[0], $parking ], $this->recordedObjects);
        $this->assertEquals([ null, $parkingSlots[0] ], $this->recordedPayloads);

        $this->assertEquals(count($parkingSlotsInfo) - 1, $parking->countParkingSlots());
        $this->assertNull($parking->getParkingSlotByNumber($parkingSlotsInfo[0]['number']));
        $this->assertNull($parking->getParkingSlotByUuid($parkingSlots[0]->uuid()));
    }

    /**
     * @throws ParkingSlotNotFound
     * @throws ParkingSlotNumberAlreadyExists
     * @throws \Exception
     */
    public function testDeleteParkingSlotByIdErrorWhenParkingSlotNotFound()
    {
        $parkingSlotsInfo = [
            [ 'number' => '001', 'description' => 'Parking Slot 001' ],
            [ 'number' => '002', 'description' => 'Parking Slot 002' ],
            [ 'number' => '003', 'description' => 'Parking Slot 003' ],
            [ 'number' => '004', 'description' => 'Parking Slot 004' ],
        ];

        $parking = $this->createParking();

        foreach ($parkingSlotsInfo as $index => $parkingSlotInfo) {
            $parking->createParkingSlot(
                $parkingSlotInfo['number'],
                $parkingSlotInfo['description']
            );
        }

        $this->expectException(ParkingSlotNotFound::class);
        $parking->deleteParkingSlotByUuid(Uuid::uuid4()->__toString());
        $this->assertEquals(count($parkingSlotsInfo), $parking->countParkingSlots());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testGetParkingSlotByUuid()
    {
        $parkingSlotsInfo = [
            [ 'number' => '001', 'description' => 'Parking Slot 001' ],
            [ 'number' => '002', 'description' => 'Parking Slot 002' ],
            [ 'number' => '003', 'description' => 'Parking Slot 003' ],
            [ 'number' => '004', 'description' => 'Parking Slot 004' ],
        ];

        $parking = $this->createParking();

        /** @var ParkingSlot[] $parkingSlots */
        $parkingSlots = [];

        foreach ($parkingSlotsInfo as $index => $parkingSlotInfo) {
            $parkingSlots[$index] = $parking->createParkingSlot(
                $parkingSlotInfo['number'],
                $parkingSlotInfo['description']
            );
        }

        foreach ($parkingSlots as $index => $parkingSlot) {
            $parkingSlotFound = $parking->getParkingSlotByUuid($parkingSlots[$index]->uuid());

            $this->assertEquals($parkingSlots[$index]->uuid(), $parkingSlotFound->uuid());
            $this->assertEquals($parkingSlots[$index]->number(), $parkingSlotFound->number());
            $this->assertEquals($parkingSlots[$index]->description(), $parkingSlotFound->description());
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     */
    public function testGetParkingSlotByNumber()
    {
        $parkingSlotsInfo = [
            [ 'number' => '001', 'description' => 'Parking Slot 001' ],
            [ 'number' => '002', 'description' => 'Parking Slot 002' ],
            [ 'number' => '003', 'description' => 'Parking Slot 003' ],
            [ 'number' => '004', 'description' => 'Parking Slot 004' ],
        ];

        $parking = $this->createParking();

        /** @var ParkingSlot[] $parkingSlots */
        $parkingSlots = [];

        foreach ($parkingSlotsInfo as $index => $parkingSlotInfo) {
            $parkingSlots[$index] = $parking->createParkingSlot(
                $parkingSlotInfo['number'],
                $parkingSlotInfo['description']
            );
        }

        foreach ($parkingSlotsInfo as $index => $parkingSlotInfo) {
            $parkingSlotFound = $parking->getParkingSlotByNumber($parkingSlotsInfo[$index]['number']);

            $this->assertEquals($parkingSlots[$index]->uuid(), $parkingSlotFound->uuid());
            $this->assertEquals($parkingSlots[$index]->number(), $parkingSlotFound->number());
            $this->assertEquals($parkingSlots[$index]->description(), $parkingSlotFound->description());
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAddAdministratorWhenUserDoesNotExist()
    {
        $parking = $this->createParking();

        $this->startRecordingEvents();

        $userName = 'User01';
        $user = $this->createUser($userName);
        $parking->addAdministrator($user);

        $this->assertEquals(
            [
                DomainUser::EVENT_USER_CREATED,
                DomainParking::EVENT_USER_ADDED_TO_PARKING,
                DomainParking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING
            ],
            $this->recordedEventNames
        );
        $this->assertEquals(
            [ DomainUser::class, DomainParking::class, DomainParking::class ],
            $this->recordedClasses
        );
        $this->assertEquals(
            [ $user, $parking, $parking ],
            $this->recordedObjects
        );
        $this->assertEquals(
            [ null, $user, $user ],
            $this->recordedPayloads
        );

        $this->assertEquals($parking->getUserByName($userName), $user);
        $this->assertEquals(true, $parking->isAdministeredByUser($user));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserIsNotAdministrator
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     */
    public function testRemoveAdministrator()
    {
        $parking = $this->createParking();

        $userNames = [ 'User01', 'User02' ];
        $users = [];
        foreach ($userNames as $userName) {
            $user = $this->createUser($userName);
            $parking->addAdministrator($user);
            $users[] = $user;
        }

        $this->assertEquals(true, $parking->isAdministeredByUser($users[0]));

        $this->startRecordingEvents();
        $parking->removeAdministrator($users[0]);

        $this->assertEquals([ DomainParking::EVENT_ADMINISTRATOR_REMOVED_FROM_PARKING ], $this->recordedEventNames);
        $this->assertEquals([ DomainParking::class ], $this->recordedClasses);
        $this->assertEquals([ $parking ], $this->recordedObjects);
        $this->assertEquals([ $users[0] ], $this->recordedPayloads);

        $this->assertEquals(false, $parking->isAdministeredByUser($users[0]));
        $this->assertEquals(true, $parking->isAdministeredByUser($users[1]));
        $this->assertNotNull($parking->getUserByName($userNames[0]));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserIsNotAdministrator
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     */
    public function testRemoveAdministratorErrorWhenUserIsNotAdministrator()
    {
        $parking = $this->createParking();

        $this->startRecordingEvents();

        $userNames = [ 'User01', 'User02', 'User03' ];
        $users = [];
        foreach ($userNames as $index => $userName) {
            $user = $this->createUser($userName);
            $parking->addUser($user, $index != 0);
            $users[] = $user;
        }

        $this->assertEquals(false, $parking->isAdministeredByUser($users[0]));
        $this->expectException(UserIsNotAdministrator::class);
        $parking->removeAdministrator($users[0]);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserIsNotAdministrator
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     */
    public function testRemoveAdministratorErrorWhenUserDoesNotExist()
    {
        $parking = $this->createParking();

        $this->startRecordingEvents();

        $userNames = [ 'User01', 'User02', 'User03' ];
        $users = [];
        foreach ($userNames as $index => $userName) {
            $user = $this->createUser($userName);
            if ($index != 0) {
                $parking->addUser($user);
            }
            $users[] = $user;
        }

        $this->assertNull($parking->getUserByName($userNames[0]));
        $this->expectException(UserNotAssigned::class);
        $parking->removeAdministrator($users[0]);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAddAdministratorWhenUserExists()
    {
        $parking = $this->createParking();

        $userName = 'User01';
        $user = $this->createUser($userName);
        $parking->addUser($user);

        $this->startRecordingEvents();
        $parking->addAdministrator($user);

        $this->assertEquals([ DomainParking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING ], $this->recordedEventNames);
        $this->assertEquals([ DomainParking::class ], $this->recordedClasses);
        $this->assertEquals([ $parking ], $this->recordedObjects);
        $this->assertEquals([ $user ], $this->recordedPayloads);

        $this->assertEquals($parking->getUserByName($userName), $user);
        $this->assertEquals(true, $parking->isAdministeredByUser($user));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAddUserNotAdministrator()
    {
        $parking = $this->createParking();

        $this->startRecordingEvents();

        $userName = 'User01';
        $user = $this->createUser($userName);
        $parking->addUser($user, false);

        $this->assertEquals(
            [ DomainUser::EVENT_USER_CREATED, DomainParking::EVENT_USER_ADDED_TO_PARKING ],
            $this->recordedEventNames
        );
        $this->assertEquals(
            [ DomainUser::class, DomainParking::class ],
            $this->recordedClasses
        );
        $this->assertEquals(
            [ $user, $parking ],
            $this->recordedObjects
        );
        $this->assertEquals(
            [ null, $user ],
            $this->recordedPayloads
        );

        $this->assertEquals(false, $parking->isAdministeredByUser($user));
        $this->assertEquals($user, $parking->getUserByName($userName));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAddUserAdministrator()
    {
        $parking = $this->createParking();

        $this->startRecordingEvents();

        $userName = 'User01';
        $user = $this->createUser($userName);
        $parking->addUser($user, true);

        $this->assertEquals(
            [
                DomainUser::EVENT_USER_CREATED,
                DomainParking::EVENT_USER_ADDED_TO_PARKING,
                DomainParking::EVENT_ADMINISTRATOR_ADDED_TO_PARKING
            ],
            $this->recordedEventNames
        );
        $this->assertEquals(
            [ DomainUser::class, DomainParking::class, DomainParking::class ],
            $this->recordedClasses
        );
        $this->assertEquals(
            [ $user, $parking, $parking ],
            $this->recordedObjects
        );
        $this->assertEquals(
            [ null, $user, $user ],
            $this->recordedPayloads
        );

        $this->assertEquals(true, $parking->isAdministeredByUser($user));
        $this->assertEquals($user, $parking->getUserByName($userName));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testIsAdministeredByUser()
    {
        $parking = $this->createParking();

        for ($users = [], $i = 0; $i < 5; $i++) {
            $users[$i] = $this->createUser(sprintf('User%s', $i));
            $parking->addUser($users[$i], $i == 0);
        }

        foreach ($users as $index => $user) {
            $this->assertEquals($index == 0, $parking->isAdministeredByUser($user));
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAddSeveralUsers()
    {
        $userNames = [ 'User1', 'User2', 'User3'];

        $parking = $this->createParking();

        foreach ($userNames as $userName) {
            $user = $this->createUser($userName);
            $parking->addUser($user);
        }

        foreach ($userNames as $userName) {
            $this->assertNotNull($parking->getUserByName($userName));
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAddUserErrorWhenUsernameAlreadyExists()
    {
        $userName = 'User';

        $parking = $this->createParking();

        $user1 = $this->createUser($userName);
        $user2 = $this->createUser($userName);

        $parking->addUser($user1);

        $this->expectException(UserNameAlreadyExists::class);
        $parking->addUser($user2);

        $this->assertEquals($user1, $parking->getUserByName($userName));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     */
    public function testRemoveUser()
    {
        $parking = $this->createParking();

        $userName = 'User01';
        $user = $this->createUser($userName);
        $parking->addUser($user, true);

        for ($i = 1, $parkingSlots = []; $i <= 2; $i++) {
            $parkingSlots[] = $parking->createParkingSlot($i, sprintf('Parking slot %s', $i));
        }

        $this->assertEquals($user, $parking->getUserByName($userName));
        $this->startRecordingEvents();
        $parking->removeUser($user);

        $this->assertEquals(
            [
                ParkingSlot::EVENT_USER_REMOVED_FROM_PARKING_SLOT,
                ParkingSlot::EVENT_USER_REMOVED_FROM_PARKING_SLOT,
                DomainParking::EVENT_USER_REMOVED_FROM_PARKING,
            ],
            $this->recordedEventNames
        );
        $this->assertEquals(
            [ ParkingSlot::class, ParkingSlot::class, DomainParking::class ],
            $this->recordedClasses
        );
        $this->assertEquals(
            [ $parkingSlots[0], $parkingSlots[1], $parking ],
            $this->recordedObjects
        );
        $this->assertEquals(
            [ $user, $user, $user ],
            $this->recordedPayloads
        );

        $this->assertNull($parking->getUserByName($userName));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     */
    public function testRemoveUserErrorWhenUserIsNotAssigned()
    {
        $parking = $this->createParking();

        $userName = 'User01';
        $user = $this->createUser($userName);

        $this->assertNull($parking->getUserByName($userName));
        $this->expectException(UserNotAssigned::class);
        $parking->removeUser($user);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testIsUserAssigned()
    {
        $userName = 'User';

        $parking = $this->createParking();

        $user1 = $this->createUser($userName);
        $user2 = $this->createUser($userName);

        $parking->addUser($user1);

        $this->assertTrue($parking->isUserAssigned($user1));
        $this->assertFalse($parking->isUserAssigned($user2));
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testGetUserInformation()
    {
        $userName = 'User';
        $parkingSlotNumber = '22';
        $parkingSlotDescription = 'Parking 22';

        $userInformationFromDate = new DateTimeImmutable('00:00:00');
        $userInformationToDate = new DateTimeImmutable('+7 days 00:00:00');

        $assignFromDate = new DateTimeImmutable('+1 day 00:00:00');
        $assignToDate = new DateTimeImmutable('+3 days 00:00:00');
        $markAsFreeDate = new DateTimeImmutable('+2 days 00:00:00');
        $isExclusive = true;

        $reserveFromDate = new DateTimeImmutable('+4 day 00:00:00');
        $reserveToDate = new DateTimeImmutable('+5 days 00:00:00');

        $parking = $this->createParking();

        $user = $this->createUser($userName);

        $parking->addUser($user);

        $parkingSlot = $parking->createParkingSlot($parkingSlotNumber, $parkingSlotDescription);
        $parkingSlot->assignToUserForPeriod($user, $assignFromDate, $assignToDate, $isExclusive);
        $parkingSlot->markAsFreeFromUserAndPeriod($user, $markAsFreeDate, $markAsFreeDate);

        $parkingSlot->reserveToUserForPeriod($user, $reserveFromDate, $reserveToDate);

        $userInformation = $parking->getUserInformation($user, $userInformationFromDate, $userInformationToDate);

        /** @var Assignment[] $assignments */
        $assignments = $userInformation['assignments'];
        $this->assertEquals(2, count($assignments));
        $this->assertEquals($assignFromDate, $assignments[0]->date());
        $this->assertEquals($assignToDate, $assignments[1]->date());

        /** @var Reservation[] $reservations */
        $reservations = $userInformation['reservations'];
        $this->assertEquals(2, count($reservations));
        $this->assertEquals($reserveFromDate, $reservations[0]->date());
        $this->assertEquals($reserveToDate, $reservations[1]->date());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     * @throws \Exception
     */
    public function testGetUserInformationErrorWhenUserIsNotAssigned()
    {
        $userName1 = 'User1';
        $userName2 = 'User2';

        $userInformationFromDate = new DateTimeImmutable('00:00:00');
        $userInformationToDate = new DateTimeImmutable('+7 days 00:00:00');

        $parking = $this->createParking();

        $user1 = $this->createUser($userName1);
        $user2 = $this->createUser($userName2);

        $parking->addUser($user1);

        $this->expectException(UserNotAssigned::class);
        $userInformation = $parking->getUserInformation($user2, $userInformationFromDate, $userInformationToDate);

        $this->assertNull($userInformation);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws InvalidDateRange
     * @throws \Exception
     */
    public function testDelete()
    {
        $userName = 'User';
        $parkingSlotNumber = '22';
        $parkingSlotDescription = 'Parking 22';

        $assignFromDate = new DateTimeImmutable('+1 day 00:00:00');
        $assignToDate = new DateTimeImmutable('+3 days 00:00:00');
        $isExclusive = true;

        $parking = $this->createParking();

        $user = $this->createUser($userName);

        $parking->addUser($user);

        $parkingSlot = $parking->createParkingSlot($parkingSlotNumber, $parkingSlotDescription);
        $parkingSlot->assignToUserForPeriod($user, $assignFromDate, $assignToDate, $isExclusive);

        $this->startRecordingEvents();
        $parking->delete();

        $this->assertEquals(
            [ ParkingSlot::EVENT_PARKING_SLOT_DELETED, DomainParking::EVENT_PARKING_DELETED ],
            $this->recordedEventNames
        );
        $this->assertEquals([ ParkingSlot::class, DomainParking::class], $this->recordedClasses);
        $this->assertEquals([ $parkingSlot, $parking], $this->recordedObjects);
    }

    /**
     * @param string $description
     * @return Parking
     * @throws ExceptionGeneratingUuid
     */
    private function createParking(string $description = 'test') : Parking
    {
        return new Parking($description);
    }

    /**
     * @param string $userName
     * @return DomainUser
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    private function createUser(string $userName): DomainUser
    {
        $userEmail = sprintf('%s@test.com', $userName);
        $userPassword = sprintf('%spassword', $userName);
        $isAdministrator = false;

        return new User($userName, $userEmail, $userPassword, $isAdministrator);
    }

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function normalizeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
