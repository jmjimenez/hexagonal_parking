<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class DeleteUserTest extends TestBase
{
    public function testOnPost()
    {
        $userUuid = $this->userOne->uuid();

        $params = [
            'userUuid' => $userUuid
        ];

        $request = new TestRequest(
            'POST',
            '/deleteuser',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertDelete($this->recordedSqlStatements[0], 'users', ['uuid' => $userUuid]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $userFound = $this->userRepository->findByUuid($userUuid);
        $this->assertNull($userFound);
    }
}
