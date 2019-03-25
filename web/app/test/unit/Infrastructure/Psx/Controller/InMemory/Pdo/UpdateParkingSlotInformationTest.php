<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class UpdateParkingSlotInformationTest extends TestBase
{
    /**
     * @throws \Exception
     */
    public function testOnPost()
    {
        $parkingSlotUuid = $this->parkingSlotOne->uuid();
        $number = '333';
        $description = 'Parking Slot 333';

        $params = [
            'userUuid' => $this->userOne->uuid(),
            'parkingUuid' => $this->parking->uuid(),
            'parkingSlotUuid' => $parkingSlotUuid,
            'number' => $number,
            'description' => $description
        ];

        $request = new TestRequest(
            'POST',
            '/updateparkingslotinformation',
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

        $this->assertEquals($number, $parkingSlotFound->number());
        $this->assertEquals($description, $parkingSlotFound->description());
    }
}
