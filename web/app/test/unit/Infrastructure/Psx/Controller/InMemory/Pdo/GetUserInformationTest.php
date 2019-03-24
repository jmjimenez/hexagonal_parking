<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class GetUserInformationTest extends TestBase
{
    public function testOnPost()
    {
        $params = [
            'userUuid' => $this->userOne->uuid(),
        ];

        $request = new TestRequest(
            'POST',
            '/getuserinformation',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(0, count($this->recordedSqlStatements));

        $this->assertResponseCount($output, 1);
        $this->assertResponse($output, 'result', function (array $result) {
            $this->assertEquals(4, count($result));

            $this->assertEquals($this->userOne->uuid(), $result['uuid']);
            $this->assertEquals($this->userOne->name(), $result['name']);
            $this->assertEquals($this->userOne->email(), $result['email']);
            $this->assertEquals($this->userOne->isAdministrator(), $result['isAdministrator']);
        });
    }
}
