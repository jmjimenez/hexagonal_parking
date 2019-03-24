<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking as InMemoryParking;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class CreateParkingTest extends TestBase
{
    /**
     */
    public function testOnPost()
    {
        $parkingDescription = 'Second Parking';

        $params = [
            'description' => $parkingDescription
        ];

        $request = new TestRequest(
            'POST',
            '/createparking',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertInsert(
            $this->recordedSqlStatements[0],
            'parkings',
            ['version' => '1', 'class' => InMemoryParking::class]
        );

        $this->assertResponseCount($output, 2);
        $this->assertOkResponse($output);

        $result = json_decode($output->output(), true);
        $this->assertTrue(isset($result['parkingUuid']));
        $newParkingUuid = $result['parkingUuid'];
        $parkingFound = $this->parkingRepository->findByUuid($newParkingUuid);
        $this->assertInstanceOf(Parking::class, $parkingFound);
        $this->assertEquals($parkingDescription, $parkingFound->description());
    }
}
