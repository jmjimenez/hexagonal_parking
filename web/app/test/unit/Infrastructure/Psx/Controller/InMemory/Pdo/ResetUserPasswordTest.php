<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class ResetUserPasswordTest extends TestBase
{
    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $passwordToken = 'passwordToken';
        $passwordTokenExpirationDate = new DateTimeImmutable('+3 days');
        $newPassword = 'newuseronepassword';

        $this->userOne->requestResetPassword($passwordToken, $passwordTokenExpirationDate);
        $this->userRepository->save($this->userOne);

        $params = [
            'userEmail' => $this->userOne->email(),
            'passwordToken' => $passwordToken,
            'userPassword' => $newPassword
        ];

        $request = new TestRequest(
            'POST',
            '/resetuserpassword',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertOkResponse($output);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate(
            $this->recordedSqlStatements[0],
            'users',
            ['version' => '2', 'uuid' => $this->userOne->uuid()]
        );

        $userFound = $this->userRepository->findByUuid($this->userOne->uuid());

        $this->assertTrue($userFound->checkPassword($newPassword));
    }
}
