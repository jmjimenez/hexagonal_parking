<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Parking\Domain\Aggregate\Parking;
use Jmj\Parking\Infrastructure\Aggregate\InMemory\Parking as InMemoryParking;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\AssertSqlStatements;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class CreateParkingTest extends TestBase
{
    use AssertSqlStatements;

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

        $parkingDescription = 'Second Parking';

        $request = new TestRequest(
            'POST',
            '/createparking',
            'Bearer '  . $this->generateAuthorizationKey(),
            "{ \"description\" : \"{$parkingDescription}\" }"
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertInsert(
            $this->recordedSqlStatements[0],
            'parkings',
            ['version' => '1', 'class' => InMemoryParking::class]
        );

        $result = $manage = json_decode($output->output(), true);
        $this->assertEquals(2, count($result));
        $this->assertTrue(isset($result['result']));
        $this->assertEquals('ok', $result['result']);
        $this->assertTrue(isset($result['parkingUuid']));

        $newParkingUuid = $result['parkingUuid'];
        $parkingFound = $this->parkingRepository->findByUuid($newParkingUuid);
        $this->assertInstanceOf(Parking::class, $parkingFound);
        $this->assertEquals($parkingDescription, $parkingFound->description());
    }
}
