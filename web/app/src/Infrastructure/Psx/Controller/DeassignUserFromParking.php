<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Jmj\Parking\Application\Command\DeassignUserFromParking as DeassignUserFromParkingCommand;
use Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class DeassignUserFromParking extends BaseController
{
    /**
     * @Inject("DeassignUserFromParkingCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\DeassignUserFromParking
     */
    protected $commandHandler;

    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new DeassignUserFromParkingCommand(
            $this->loggedInUser->uuid(),
            $postData->userUuid,
            $postData->parkingUuid
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
