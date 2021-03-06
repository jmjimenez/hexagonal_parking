<?php

namespace Jmj\Test\Unit\Infrastructure\Aggregate\InMemory;

use DateTimeImmutable;
use Exception;
use Jmj\Parking\Domain\Aggregate\Common\BaseAggregate;
use Jmj\Parking\Domain\Aggregate\User as DomainUser;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Exception\UserResetPasswordTokenInvalid;
use Jmj\Parking\Domain\Exception\UserTokenExpirationDateInvalid;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use Jmj\Parking\Common\EventsRecorder;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    use EventsRecorder;

    /** @var string  */
    private $userName = 'User Name';

    /** @var string  */
    private $userEmail = 'user@email.test';

    /** @var string  */
    private $userPassword = 'userPassword';

    /** @var bool  */
    private $userIsAdministrator = false;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userName = 'User Name';
        $this->userEmail = 'user@mail.test';
        $this->userPassword = 'userPassword';
        $this->userIsAdministrator = false;

        BaseAggregate::setDomainEventBroker($this->getEventBroker());
        $this->getEventBroker()->resetSubscriptions();
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testCreateUser()
    {
        $user = $this->createUser();

        $this->assertEquals([ DomainUser::EVENT_USER_CREATED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->uuid());

        $this->assertEquals($this->userName, $user->name());
        $this->assertEquals($this->userEmail, $user->email());
        $this->assertTrue($user->checkPassword($this->userPassword));
        $this->assertEquals($this->userIsAdministrator, $user->isAdministrator());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testCreateUserErrorWhenInvalidEmail()
    {
        $this->userEmail = 'invalidemail';

        $this->expectException(UserEmailInvalid::class);
        $user = $this->createUser();

        $this->assertNull($user);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testCreateUserErrorWhenInvalidPassword()
    {
        $this->userPassword = '';

        $this->expectException(UserPasswordInvalid::class);
        $user = $this->createUser();

        $this->assertNull($user);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testCreateUserErrorWhenInvalidName()
    {
        $this->userName = '';

        $this->expectException(UserNameInvalid::class);
        $user = $this->createUser();

        $this->assertNull($user);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testUserName()
    {
        $user = $this->createUser();

        $this->assertEquals($this->userName, $user->name());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testUserEmail()
    {
        $user = $this->createUser();

        $this->assertEquals($this->userEmail, $user->email());
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUserSetName()
    {
        $newUserName = 'New User Name';

        $user = $this->createUser();

        $this->assertEquals($this->userName, $user->name());

        $this->startRecordingEvents();
        $user->setName($newUserName);

        $this->assertEquals([ DomainUser::EVENT_USER_NAME_CHANGED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class ], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);
        $this->assertEquals($newUserName, $user->name());
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUserSetNameErrorWhenInvalidName()
    {
        $newUserName = '';

        $user = $this->createUser();

        $this->assertEquals($this->userName, $user->name());

        $this->startRecordingEvents();
        $this->expectException(UserNameInvalid::class);
        $user->setName($newUserName);
        $this->assertEquals($this->userName, $user->name());

        $this->assertEquals([ ], $this->recordedEventNames);
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUserSetEmail()
    {
        $newUserEmail = 'newuseremail@email.text';

        $user = $this->createUser();

        $this->assertEquals($this->userEmail, $user->email());

        $this->startRecordingEvents();
        $user->setEmail($newUserEmail);

        $this->assertEquals([ DomainUser::EVENT_USER_EMAIL_CHANGED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class ], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);
        $this->assertEquals($newUserEmail, $user->email());
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUserSetEmailErrorWhenInvalidEmail()
    {
        $newUserEmail = 'wrongEmail';

        $user = $this->createUser();

        $this->assertEquals($this->userEmail, $user->email());

        $this->startRecordingEvents();
        $this->expectException(UserEmailInvalid::class);
        $user->setEmail($newUserEmail);

        $this->assertEquals([], $this->recordedEventNames);
        $this->assertEquals($this->userEmail, $user->email());
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUserSetPassword()
    {
        $newUserPassword = 'newpassword';

        $user = $this->createUser();

        $this->assertTrue($user->checkPassword($this->userPassword));

        $this->startRecordingEvents();
        $user->setPassword($newUserPassword);

        $this->assertEquals([ DomainUser::EVENT_USER_PASSWORD_CHANGED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class ], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);

        $this->assertTrue($user->checkPassword($newUserPassword));
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testUserSetPasswordErrorWhenInvalidPassword()
    {
        $newUserPassword = '';

        $user = $this->createUser();

        $this->assertTrue($user->checkPassword($this->userPassword));

        $this->startRecordingEvents();
        $this->expectException(UserPasswordInvalid::class);
        $user->setPassword($newUserPassword);

        $this->assertEquals([], $this->recordedEventNames);

        $this->assertTrue($user->checkPassword($this->userPassword));
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     * @throws UserResetPasswordTokenInvalid
     * @throws UserTokenExpirationDateInvalid
     * @throws Exception
     */
    public function testRequestResetPassword()
    {
        $user = $this->createUser();

        $resetPasswordToken = 'resetPasswordToken';
        $passwordTokenTimeout = new DateTimeImmutable('tomorrow');

        $this->startRecordingEvents();
        $user->requestResetPassword($resetPasswordToken, $passwordTokenTimeout);

        $this->assertEquals([ DomainUser::EVENT_USER_PASSWORD_RESET_REQUESTED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class ], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);
        $this->assertEquals(
            [
                [
                    'resetPasswordToken' => $resetPasswordToken,
                    'tokenExpirationDate' => $passwordTokenTimeout
                ]
            ],
            $this->recordedPayloads
        );
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws UserResetPasswordTokenInvalid
     * @throws ExceptionGeneratingUuid
     * @throws UserTokenExpirationDateInvalid
     * @throws Exception
     */
    public function testRequestResetPasswordErrorWhenResetTokenInvalid()
    {
        $user = $this->createUser();

        $resetPasswordToken = '';
        $tokenExpirationDate = new DateTimeImmutable('tomorrow');

        $this->startRecordingEvents();
        $this->expectException(UserResetPasswordTokenInvalid::class);
        $user->requestResetPassword($resetPasswordToken, $tokenExpirationDate);

        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws UserResetPasswordTokenInvalid
     * @throws UserTokenExpirationDateInvalid
     * @throws ExceptionGeneratingUuid
     * @throws Exception
     */
    public function testRequestResetPasswordErrorWhenResetTokenTimeoutInvalid()
    {
        $user = $this->createUser();

        $resetPasswordToken = 'resetPasswordToken';
        $tokenExpirationDate = new DateTimeImmutable('yesterday');

        $this->startRecordingEvents();
        $this->expectException(UserTokenExpirationDateInvalid::class);
        $user->requestResetPassword($resetPasswordToken, $tokenExpirationDate);

        $this->assertEquals([], $this->recordedEventNames);
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws UserResetPasswordTokenInvalid
     * @throws UserTokenExpirationDateInvalid
     * @throws ExceptionGeneratingUuid
     * @throws Exception
     */
    public function testResetPassword()
    {
        $user = $this->createUser();

        $newPassword = 'newpassword';
        $resetPasswordToken = 'resetPasswordToken';
        $tokenExpirationDate = new DateTimeImmutable('tomorrow');

        $user->requestResetPassword($resetPasswordToken, $tokenExpirationDate);
        $this->startRecordingEvents();
        $user->resetPassword($newPassword, $resetPasswordToken);

        $this->assertEquals([ DomainUser::EVENT_USER_PASSWORD_RESETTED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class ], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws UserResetPasswordTokenInvalid
     * @throws UserTokenExpirationDateInvalid
     * @throws ExceptionGeneratingUuid
     * @throws Exception
     */
    public function testResetPasswordErrorWhenNewPasswordInvalid()
    {
        $user = $this->createUser();

        $newPassword = '';
        $resetPasswordToken = 'resetPasswordToken';
        $tokenExpirationDate = new DateTimeImmutable('tomorrow');

        $user->requestResetPassword($resetPasswordToken, $tokenExpirationDate);
        $this->startRecordingEvents();
        $this->expectException(UserPasswordInvalid::class);
        $user->resetPassword($newPassword, $resetPasswordToken);
        $this->assertEquals([], $this->recordedEventNames);
        $this->assertTrue(($user->checkPassword($this->userPassword)));
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws UserResetPasswordTokenInvalid
     * @throws UserTokenExpirationDateInvalid
     * @throws ExceptionGeneratingUuid
     * @throws Exception
     */
    public function testResetPasswordErrorWhenResetPasswordTokenInvalid()
    {
        $user = $this->createUser();

        $newPassword = 'newPassword';
        $resetPasswordToken = 'resetPasswordToken';
        $wrongPasswordToken = 'wrongPasswordToken';
        $tokenExpirationDate = new DateTimeImmutable('tomorrow');

        $user->requestResetPassword($resetPasswordToken, $tokenExpirationDate);
        $this->startRecordingEvents();
        $this->expectException(UserResetPasswordTokenInvalid::class);
        $user->resetPassword($newPassword, $wrongPasswordToken);
        $this->assertEquals([], $this->recordedEventNames);
        $this->assertTrue(($user->checkPassword($this->userPassword)));
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testSetIsAdministrator()
    {
        $user = $this->createUser();

        $this->assertFalse($user->isAdministrator());

        $this->startRecordingEvents();
        $user->setAdministrator(true);

        $this->assertEquals([ DomainUser::EVENT_USER_ADMINISTRATOR_RIGHTS_CONFIGURED ], $this->recordedEventNames);
        $this->assertEquals([ DomainUser::class ], $this->recordedClasses);
        $this->assertEquals([ $user ], $this->recordedObjects);

        $this->assertTrue($user->isAdministrator());
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testSetIsAdministratorWhenThereIsNoChange()
    {
        $user = $this->createUser();

        $this->assertFalse($user->isAdministrator());

        $this->startRecordingEvents();
        $user->setAdministrator(false);

        $this->assertEquals([], $this->recordedEventNames);

        $this->assertFalse($user->isAdministrator());
    }

    /**
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function testGetInformation()
    {
        $user = $this->createUser();

        $expectedResult = [
            'uuid' => $user->uuid(),
            'name' => $user->name(),
            'email' => $user->email(),
            'isAdministrator' => $user->isAdministrator(),
        ];

        $this->assertEquals($expectedResult, $user->getInformation());
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAuthenticate()
    {
        $user = $this->createUser();

        $this->startRecordingEvents();
        $this->assertTrue($user->authenticate($this->userPassword));

        $this->assertEquals([ User::EVENT_USER_AUTHENTICATED ], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testAuthenticationErrorWhenInvalidPassword()
    {
        $user = $this->createUser();

        $this->startRecordingEvents();
        $this->assertFalse($user->authenticate($this->userPassword . 'sssssss'));

        $this->assertEquals([ User::EVENT_USER_AUTHENTICATION_ERROR ], $this->recordedEventNames);
    }

    /**
     * @throws ExceptionGeneratingUuid
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     */
    public function testDelete()
    {
        $user = $this->createUser();

        $this->startRecordingEvents();
        $user->delete();

        $this->assertEquals([ User::EVENT_USER_DELETED ], $this->recordedEventNames);
    }

    /**
     * @return DomainUser
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    private function createUser() : DomainUser
    {
        $this->startRecordingEvents();

        return new User($this->userName, $this->userEmail, $this->userPassword, $this->userIsAdministrator);
    }
}
