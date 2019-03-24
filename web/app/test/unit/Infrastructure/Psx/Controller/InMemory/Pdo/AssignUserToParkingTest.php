<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo;

use Jmj\Parking\Infrastructure\Aggregate\InMemory\User;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestBase;
use Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common\TestRequest;

class AssignUserToParkingTest extends TestBase
{
    /**
     * @param string $userName
     * @param string $userEmail
     * @param string $userPassword
     * @param bool $isParkingAdministrator
     * @throws \Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid
     * @throws \Jmj\Parking\Domain\Exception\UserEmailInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserNameInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserPasswordInvalid
     * @dataProvider onPostDataProvider
     */
    public function testOnPost(string $userName, string $userEmail, string $userPassword, bool $isParkingAdministrator)
    {
        $newUser = new User($userName, $userEmail, $userPassword, false);
        $this->userRepository->save($newUser);

        $params = [
            'userUuid' => $newUser->uuid(),
            'parkingUuid' => $this->parking->uuid(),
            'isAdministrator' => $this->boolToString($isParkingAdministrator)
        ];

        $request = new TestRequest(
            'POST',
            '/assignusertoparking',
            $this->generateAuthorizationKey(),
            json_encode($params)
        );

        $output = $this->executeRequest($request);

        $this->assertEquals(1, count($this->recordedSqlStatements));
        $this->assertUpdate($this->recordedSqlStatements[0], 'parkings', ['uuid' => $this->parking->uuid()]);

        $this->assertResponseCount($output, 1);
        $this->assertOkResponse($output);

        $parkingFound = $this->parkingRepository->findByUuid($this->parking->uuid());

        $this->assertTrue($parkingFound->isUserAssigned($newUser));
        $this->assertEquals($isParkingAdministrator, $parkingFound->isAdministeredByUser($newUser));
    }

    /**
     * @return array
     */
    public function onPostDataProvider(): array
    {
        return [
            [ 'newUserOne', 'newuserone@test.com', 'newuseronepasswd', false ],
            [ 'newUserTwo', 'newusertwo@test.com', 'newusertwopasswd', true ],
        ];
    }
}
