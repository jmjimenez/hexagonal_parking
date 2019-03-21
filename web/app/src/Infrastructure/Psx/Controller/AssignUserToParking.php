<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Jmj\Parking\Application\Command\AssignUserToParking as AssignUserToParkingCommand;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class AssignUserToParking extends BaseController
{
    /**
     * @Inject("AssignUserToParkingCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\AssignUserToParking
     */
    protected $commandHandler;

    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new AssignUserToParkingCommand(
            $this->loggedInUser->uuid(),
            $postData->userUuid,
            $postData->parkingUuid,
            $postData->isAdministrator === 'true'
        );

        try {
            $this->commandHandler->execute($command);
            $data = [ 'result' => 'ok' ];
        } catch (ParkingNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'Parking not found' ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
