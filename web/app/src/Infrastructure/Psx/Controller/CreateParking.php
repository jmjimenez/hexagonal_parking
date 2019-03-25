<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Jmj\Parking\Application\Command\CreateParking as CreateParkingCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class CreateParking extends Common\BaseController
{
    /**
     * @Inject("CreateParkingCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\CreateParking
     */
    protected $commandHandler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new CreateParkingCommand(
            $this->loggedInUser->uuid(),
            $postData->description
        );

        try {
            $parking = $this->commandHandler->execute($command);
            $data = [ 'result' => 'ok', 'parkingUuid' => $parking->uuid() ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
