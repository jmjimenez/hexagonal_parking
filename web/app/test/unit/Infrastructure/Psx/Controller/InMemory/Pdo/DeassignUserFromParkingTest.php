<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class DeassignUserFromParkingTest extends TestBase
{
    /**
     */
    public function testOnPost()
    {
        $userUuid = $this->userOne->uuid();

        $params = [
            'parkingUuid' => $this->parking->uuid(),
            'userUuid' => $userUuid
        ];

        $request = new TestRequest(
            'POST',
            '/deassignuserfromparking',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $userFoundInParking = $parkingFound->getUserByUuid($userUuid);
        $this->assertNull($userFoundInParking);

        $userFoundInRepository = $this->userRepository->findByUuid($userUuid);
        $this->assertInstanceOf(User::class, $userFoundInRepository);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate(
            $this->recordedSqlStatements[0],
            'parkings',
            ['uuid' => $this->parking->uuid(), 'version' => '1']
        );

        $this->assertFalse($parkingFound->isUserAssigned($userFoundInRepository));
    }
}
