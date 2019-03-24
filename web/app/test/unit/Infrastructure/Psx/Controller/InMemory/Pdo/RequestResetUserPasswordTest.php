<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use DateTimeImmutable;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class RequestResetUserPasswordTest extends TestBase
{
    /**
     */
    public function testOnPost()
    {
        $params = [
            'userEmail' => $this->userOne->email()
        ];

        $request = new TestRequest(
            'POST',
            '/requestresetuserpassword',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        //TODO: table name should be a constant
        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate(
            $this->recordedSqlStatements[0],
            'users',
            ['version' => '1', 'uuid' => $this->userOne->uuid()]
        );

        $this->assertResponseCount($output, 1);
        $this->assertResponse($output, 'result', function (array $result) {
            $this->assertEquals(3, count($result));
            $this->assertEquals($this->userOne->email(), $result['email']);
            $this->assertTrue(isset($result['token']));
            $this->assertTrue(isset($result['expirationDate']));
            $this->assertInstanceOf(DateTimeImmutable::class, new DateTimeImmutable($result['expirationDate']));
            $this->assertTrue(new DateTimeImmutable($result['expirationDate']) > new DateTimeImmutable());
        });
    }
}
