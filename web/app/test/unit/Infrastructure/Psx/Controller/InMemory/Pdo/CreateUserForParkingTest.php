<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Parking\Domain\Aggregate\User;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class CreateUserForParkingTest extends TestBase
{
    /**
     */
    public function testOnPost()
    {
        $userName = 'New User';
        $userEmail = 'newuser@test.com';
        $userPassword = 'newuserpassword';
        $isAdministrator = false;
        $isParkingAdministrator = true;

        $params = [
            'parkingUuid' => $this->parking->uuid(),
            'userName' => $userName,
            'userEmail' => $userEmail,
            'userPassword' => $userPassword,
            'isAdministrator' => $this->boolToString($isAdministrator),
            'isAdministratorForParking' => $this->boolToString($isParkingAdministrator),
        ];

        $request = new TestRequest(
            'POST',
            '/createuserforparking',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertResponseCount($output, 2);
        $this->assertOkResponse($output);

        $result = json_decode($output->output(), true);
        $this->assertTrue(isset($result['userUuid']));
        $newUserUuid = $result['userUuid'];

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());
        $userFoundInParking = $parkingFound->getUserByUuid($newUserUuid);
        $userFoundInRepository = $this->userRepository->findByUuid($newUserUuid);
        $this->assertInstanceOf(User::class, $userFoundInRepository);
        $this->assertEquals($userFoundInParking, $userFoundInRepository);

        $this->assertEquals(2, count($this->recordedSqlStatements));
        $this->assertUpdate(
            $this->recordedSqlStatements[0],
            'parkings',
            ['uuid' => $this->parking->uuid(), 'version' => '1']
        );
        $this->assertInsert(
            $this->recordedSqlStatements[1],
            'users',
            ['uuid' => $newUserUuid, 'version' => '1']
        );

        $this->assertEquals($userName, $userFoundInRepository->name());
        $this->assertEquals($userEmail, $userFoundInRepository->email());
        $this->assertTrue($userFoundInRepository->checkPassword($userPassword));
        $this->assertEquals($isParkingAdministrator, $parkingFound->isAdministeredByUser($userFoundInRepository));
        $this->assertEquals($isAdministrator, $userFoundInRepository->isAdministrator());
    }
}
