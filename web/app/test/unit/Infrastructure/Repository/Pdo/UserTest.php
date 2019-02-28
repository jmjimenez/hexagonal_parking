<?php

namespace Jmj\Test\Unit\Infrastructure\Repository\Pdo;

use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use Jmj\Parking\Infrastructure\Repository\Pdo\User as PdoUserRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserTest extends TestCase
{
    const USER_NAME = 'User Name';
    const USER_EMAIL = 'useremail@test.com';
    const USER_PASSWORD = 'userpassword';
    const USER_IS_ADMINISTRATOR = false;

    /** @var PdoUserRepository */
    protected static $userRepository;

    /** @var PdoProxy */
    protected static $pdoProxy;

    /** @var User */
    protected static $user;

    /**
     * @throws ExceptionGeneratingUuid
     * @throws PdoConnectionError
     * @throws PdoExecuteError
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$pdoProxy = new PdoProxy();
        self::$pdoProxy->connectToSqlite(':memory:');


        self::$userRepository = new PdoUserRepository('User', self::$pdoProxy);
        self::$userRepository->initializeRepository();
        self::$user = self::createUserTest();
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
        $result = self::$userRepository->save(self::$user);
        $this->assertEquals(1, $result);
    }

    /**
     * @throws PdoExecuteError
     */
    public function testFindByUuid()
    {
        $userFound = self::$userRepository->findByUuid(self::$user->uuid());

        $this->assertInstanceOf(User::class, $userFound);
        $this->assertEquals(self::$user->uuid(), $userFound->uuid());
        $this->assertEquals(self::$user->name(), $userFound->name());
        $this->assertEquals(self::$user->email(), $userFound->email());
        $this->assertEquals(self::$user->isAdministrator(), $userFound->isAdministrator());
        $this->assertTrue($userFound->checkPassword(self::USER_PASSWORD));
    }

    /**
     * @throws PdoExecuteError
     * @throws \Exception
     */
    public function testFindByUuidWhenNotFound()
    {
        $uuid = Uuid::uuid4()->__toString();
        $user = self::$userRepository->findByUuid($uuid);
        $this->assertNull($user);
    }

    /**
     * @throws PdoExecuteError
     */
    public function testFindByName()
    {
        $userFound = self::$userRepository->findByName(self::USER_NAME);

        $this->assertInstanceOf(User::class, $userFound);
        $this->assertEquals(self::$user->uuid(), $userFound->uuid());
    }

    /**
     * @throws PdoExecuteError
     */
    public function testFindByEmail()
    {
        $userFound = self::$userRepository->findByEmail(self::USER_EMAIL);

        $this->assertInstanceOf(User::class, $userFound);
        $this->assertEquals(self::$user->uuid(), $userFound->uuid());
    }

    /**
     * @throws PdoExecuteError
     * @throws UserNameInvalid
     * @throws \Exception
     */
    public function testSaveWhenUpdating()
    {
        $newName = 'New Name';

        self::$user->setName($newName);
        $result = self::$userRepository->save(self::$user);
        $this->assertEquals(1, $result);

        $userFound = self::$userRepository->findByUuid(self::$user->uuid());
        $this->assertEquals($newName, $userFound->name());

        self::$user->setName(self::USER_NAME);
        self::$userRepository->save(self::$user);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws PdoExecuteError
     * @throws \Exception
     */
    public function testDelete()
    {
        $user = new User('New User', 'newuser@test.com', 'newuserpassword', false);

        $result = self::$userRepository->save($user);
        $this->assertEquals(1, $result);

        $result = self::$userRepository->delete($user);
        $this->assertEquals(1, $result);

        $userFound = self::$userRepository->findByUuid($user->uuid());
        $this->assertNull($userFound);
    }

    /**
     * @return User
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    protected static function createUserTest() : User
    {
        $user = new User(
            self::USER_NAME,
            self::USER_EMAIL,
            self::USER_PASSWORD,
            self::USER_IS_ADMINISTRATOR
        );

        return $user;
    }
}
