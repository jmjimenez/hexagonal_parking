<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class DeleteParkingTest extends TestBase
{
    public function testOnPost()
    {
        $parkingUuid = $this->parking->uuid();

        $params = [
            'parkingUuid' => $parkingUuid
        ];

        $request = new TestRequest(
            'POST',
            '/deleteparking',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertDelete($this->recordedSqlStatements[0], 'parkings', ['uuid' => $parkingUuid]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($parkingUuid);
        $this->assertNull($parkingFound);
    }
}
