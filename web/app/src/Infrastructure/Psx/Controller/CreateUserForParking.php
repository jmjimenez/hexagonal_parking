<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Jmj\Parking\Application\Command\CreateUserForParking as CreateUserForParkingCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class CreateUserForParking extends Common\BaseController
{
    /**
     * @Inject("CreateUserForParkingCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\CreateUserForParking
     */
    protected $commandHandler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws \Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound
     *
     * TODO: review all throws in declarations and catch them
     */
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new CreateUserForParkingCommand(
            $this->loggedInUser->uuid(),
            $postData->parkingUuid,
            $postData->userName,
            $postData->userEmail,
            $postData->userPassword,
            $postData->isAdministrator === 'true',
            $postData->isAdministratorForParking === 'true'
        );

        //TODO: the catch part may be common for all controllers
        try {
            $user = $this->commandHandler->execute($command);
            $data = [ 'result' => 'ok', 'userUuid' => $user->uuid() ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
