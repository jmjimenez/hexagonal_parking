<?php

namespace Jmj\Parking\Domain\Aggregate;

use DateTime;
use DateTimeImmutable;
use Exception;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameInvalid;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;
use Jmj\Parking\Domain\Exception\UserResetPasswordTokenExpired;
use Jmj\Parking\Domain\Exception\UserResetPasswordTokenInvalid;
use Jmj\Parking\Domain\Exception\UserTokenExpirationDateInvalid;

class User extends Common\BaseAggregate
{
    const EVENT_USER_CREATED = 'UserCreated';
    const EVENT_USER_NAME_CHANGED = 'UserNameChanged';
    const EVENT_USER_EMAIL_CHANGED = 'UserEmailChanged';
    const EVENT_USER_PASSWORD_CHANGED = 'UserPasswordChanged';
    const EVENT_USER_PASSWORD_RESETTED = 'UserPasswordResetted';
    const EVENT_USER_PASSWORD_RESET_REQUESTED = 'UserPasswordResetRequested';
    const EVENT_USER_ADMINISTRATOR_RIGHTS_CONFIGURED = 'UserAdministratorRightsConfigured';
    const EVENT_USER_AUTHENTICATED = 'UserAuthenticated';
    const EVENT_USER_AUTHENTICATION_ERROR = 'UserNotAuthenticated';
    const EVENT_USER_DELETED = 'UserDeleted';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $resetPasswordToken;

    /**
     * @var DateTime
     */
    private $tokenExpirationDate;

    /**
     * @var bool
     */
    private $isAdministrator;

    //TODO: implement createUser use case

    /**
     * @param  string $name
     * @param  string $email
     * @param  string $password
     * @param  bool   $isAdministrator
     * @throws UserEmailInvalid
     * @throws UserNameInvalid
     * @throws UserPasswordInvalid
     * @throws ExceptionGeneratingUuid
     */
    public function __construct(string $name, string $email, string $password, bool $isAdministrator = false)
    {
        parent::__construct();

        $this->setName($name, false);
        $this->setPassword($password, false);
        $this->setEmail($email, false);
        $this->setAdministrator($isAdministrator, false);

        $this->publishEvent(self::EVENT_USER_CREATED);
    }

    /**
     * @param  string $name
     * @param  bool   $publishEvent
     * @throws UserNameInvalid
     */
    public function setName(string $name, bool $publishEvent = true)
    {
        $this->validateName($name);
        $this->name = $name;

        if ($publishEvent) {
            $this->publishEvent(self::EVENT_USER_NAME_CHANGED);
        }
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * @param  string $email
     * @param  bool   $publishEvent
     * @throws UserEmailInvalid
     */
    public function setEmail(string $email, bool $publishEvent = true)
    {
        $this->validateEmail($email);
        $this->email = $email;

        if ($publishEvent) {
            $this->publishEvent(self::EVENT_USER_EMAIL_CHANGED);
        }
    }

    /**
     * @return string
     */
    public function email() : string
    {
        return $this->email;
    }

    /**
     * @param  string $password
     * @param  bool   $publishEvent
     * @throws UserPasswordInvalid
     */
    public function setPassword(string $password, bool $publishEvent = true)
    {
        $this->validatePassword($password);
        $this->password = $password;

        if ($publishEvent) {
            $this->publishEvent(self::EVENT_USER_PASSWORD_CHANGED);
        }
    }

    /**
     * @param  string $password
     * @param  string $resetPasswordToken
     * @throws Exception
     */
    public function resetPassword(string $password, string $resetPasswordToken)
    {
        if ($this->resetPasswordToken !== $resetPasswordToken) {
            throw new UserResetPasswordTokenInvalid();
        }

        if ($this->tokenExpirationDate < new DateTime()) {
            throw new UserResetPasswordTokenExpired();
        }

        $this->setPassword($password, false);

        $this->publishEvent(self::EVENT_USER_PASSWORD_RESETTED);
    }

    /**
     * @param  string            $resetPasswordToken
     * @param  DateTimeImmutable $tokenExpirationDate
     * @throws UserResetPasswordTokenInvalid
     * @throws UserTokenExpirationDateInvalid
     */
    public function requestResetPassword(string $resetPasswordToken, DateTimeImmutable $tokenExpirationDate)
    {
        $this->setResetPasswordToken($resetPasswordToken);
        $this->setTokenExpirationDate($tokenExpirationDate);

        $this->publishEvent(
            self::EVENT_USER_PASSWORD_RESET_REQUESTED,
            [ 'resetPasswordToken' => $resetPasswordToken, 'tokenExpirationDate' => $tokenExpirationDate]
        );
    }

    public function authenticate(string $password): bool
    {
        if ($this->checkPassword($password)) {
            $this->publishEvent(self::EVENT_USER_AUTHENTICATED);

            return true;
        }

        $this->publishEvent(self::EVENT_USER_AUTHENTICATION_ERROR);
        return false;
    }

    /**
     * @param  string $password
     * @return bool
     */
    public function checkPassword(string $password) : bool
    {
        return $this->password === $password;
    }

    /**
     * @param bool $isAdministrator
     * @param bool $publishEvent
     */
    public function setAdministrator(bool $isAdministrator, bool $publishEvent = true)
    {
        if ($this->isAdministrator === $isAdministrator) {
            return;
        }

        $this->isAdministrator = $isAdministrator;

        if ($publishEvent) {
            $this->publishEvent(self::EVENT_USER_ADMINISTRATOR_RIGHTS_CONFIGURED, $isAdministrator);
        }
    }

    /**
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this->isAdministrator;
    }

    /**
     * @return array
     */
    public function getInformation() : array
    {
        return [
            'uuid' => $this->uuid(),
            'name' => $this->name(),
            'email' => $this->email(),
            'isAdministrator' => $this->isAdministrator(),
        ];
    }

    public function delete()
    {
        //TODO: mark the user as deleted
        $this->publishEvent(self::EVENT_USER_DELETED);
    }

    /**
     * @param  string $name
     * @throws UserNameInvalid
     */
    private function validateName(string $name)
    {
        if ($name == '') {
            throw new UserNameInvalid();
        }
    }

    /**
     * @param  string $email
     * @throws UserEmailInvalid
     */
    private function validateEmail(string $email)
    {
        if ($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UserEmailInvalid();
        }
    }

    /**
     * @param  string $password
     * @throws UserPasswordInvalid
     */
    private function validatePassword(string $password)
    {
        //TODO: make password more secured

        if ($password == '') {
            throw new UserPasswordInvalid();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getClassName(): string
    {
        return __CLASS__;
    }

    /**
     * @param  string $resetPasswordToken
     * @throws UserResetPasswordTokenInvalid
     */
    private function setResetPasswordToken(string $resetPasswordToken)
    {
        if ($resetPasswordToken == '') {
            throw new UserResetPasswordTokenInvalid();
        }

        $this->resetPasswordToken = $resetPasswordToken;
    }

    /**
     * @param  DateTimeImmutable $tokenExpirationDate
     * @throws UserTokenExpirationDateInvalid
     * @throws Exception
     */
    private function setTokenExpirationDate(DateTimeImmutable $tokenExpirationDate)
    {
        if ($tokenExpirationDate < new DateTime()) {
            throw new UserTokenExpirationDateInvalid();
        }

        $this->tokenExpirationDate = $tokenExpirationDate;
    }
}
