<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use Jmj\Parking\Application\Command\DeleteParkingSlot as DeleteParkingSlotCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class DeleteParkingSlot extends BaseController
{
    /**
     * @Inject("DeleteParkingSlotCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\DeleteParkingSlot
     */
    protected $commandHandler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws \Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound
     */
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        //TODO: how to check the payload is correct
        $command = new DeleteParkingSlotCommand(
            $this->loggedInUser->uuid(),
            $postData->parkingUuid,
            $postData->parkingSlotUuid
        );

        try {
            $this->commandHandler->execute($command);
            $data = [ 'result' => 'ok' ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
