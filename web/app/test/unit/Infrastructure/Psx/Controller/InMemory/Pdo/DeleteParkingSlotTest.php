<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class DeleteParkingSlotTest extends TestBase
{
    public function testOnPost()
    {
        $parkingUuid = $this->parking->uuid();
        $parkingSlotUuid = $this->parkingSlotOne->uuid();

        $params = [
            'userUuid' => $this->userOne->uuid(),
            'parkingUuid' => $parkingUuid,
            'parkingSlotUuid' => $parkingSlotUuid,
        ];

        $request = new TestRequest(
            'POST',
            '/deleteparkingslot',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'parkings', ['uuid' => $this->parking->uuid()]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($parkingSlotUuid);

        $this->assertNull($parkingSlotFound);
    }
}
