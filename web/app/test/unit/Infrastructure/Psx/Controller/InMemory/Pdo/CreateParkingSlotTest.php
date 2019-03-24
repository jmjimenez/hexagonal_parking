<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Parking\Domain\Aggregate\ParkingSlot;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class CreateParkingSlotTest extends TestBase
{
    /**
     */
    public function testOnPost()
    {
        $parkingSlotNumber = '999';
        $parkingSlotDescription = 'Parking Slot 999';

        $params = [
            'parkingUuid' => $this->parking->uuid(),
            'parkingSlotNumber' => $parkingSlotNumber,
            'parkingSlotDescription' => $parkingSlotDescription
        ];

        $request = new TestRequest(
            'POST',
            '/createparkingslot',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate(
            $this->recordedSqlStatements[0],
            'parkings',
            ['uuid' => $this->parking->uuid(), 'version' => '1']
        );

        $this->assertResponseCount($output, 2);
        $this->assertOkResponse($output);

        $result = json_decode($output->output(), true);
        $this->assertTrue(isset($result['parkingSlotUuid']));
        $newParkingSlotUuid = $result['parkingSlotUuid'];
        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $parkingSlotFound = $parkingFound->getParkingSlotByUuid($newParkingSlotUuid);
        $this->assertInstanceOf(ParkingSlot::class, $parkingSlotFound);
        $this->assertEquals($parkingSlotNumber, $parkingSlotFound->number());
        $this->assertEquals($parkingSlotDescription, $parkingSlotFound->description());
    }
}
