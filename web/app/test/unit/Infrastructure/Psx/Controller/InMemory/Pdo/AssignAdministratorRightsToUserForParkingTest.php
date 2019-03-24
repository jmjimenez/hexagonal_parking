<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class AssignAdministratorRightsToUserForParkingTest extends TestBase
{
    /**
     * @throws \Jmj\Parking\Common\Exception\PdoExecuteError
     * @throws \Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid
     * @throws \Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserEmailInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserNameAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserNameInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserPasswordInvalid
     */
    public function testOnPost()
    {
        $this->createTestContainer();

        $this->createTestCase(
            $this->container->get('PdoProxy'),
            $this->container->get('UserRepository'),
            $this->container->get('ParkingRepository')
        );

        //TODO: check what happens when the body is no correct
        //TODO: check what happens in wrong path when user or parking don´t exist

        $params = [
            'userUuid' => $this->userOne->uuid(),
            'parkingUuid' => $this->parking->uuid(),
        ];

        $request = new TestRequest(
            'POST',
            '/assignadministratorrightstouserforparking',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'parkings', ['uuid' => $this->parking->uuid()]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $this->assertTrue($parkingFound->isAdministeredByUser($this->userOne));
    }
}
