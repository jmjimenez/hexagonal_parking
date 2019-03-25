<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Firebase\JWT\JWT;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class UserLoginTest extends TestBase
{
    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $params = [
            'userEmail' => $this->userOne->email(),
            'userPassword' => 'userpasswd'
        ];

        $request = new TestRequest(
            'POST',
            '/login',
            null,
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $this->assertOkResponse($output);
        $this->assertResponse($output, 'token', function ($token) {
            $jwtConfig = $this->container->getConfig()->get('parking_jwt');
            $result = JWT::decode($token, $jwtConfig['secret'], [ $jwtConfig['algorithm'] ]);
            $this->assertEquals($this->userOne->email(), $result->email);
            $this->assertTrue($this->userOne->checkPassword($result->password));
        });
    }
}
