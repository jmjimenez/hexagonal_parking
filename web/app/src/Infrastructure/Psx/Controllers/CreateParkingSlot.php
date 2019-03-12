<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use Jmj\Parking\Application\Command\CreateParkingSlot as CreateParkingSlotCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class CreateParkingSlot extends BaseController
{
    /**
     * @Inject("CreateParkingSlotCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\CreateParkingSlot
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

        $command = new CreateParkingSlotCommand(
            $this->loggedInUser->uuid(),
            $postData->parkingUuid,
            $postData->parkingSlotNumber,
            $postData->parkingSlotDescription
        );

        try {
            $parkingSlot = $this->commandHandler->execute($command);
            $data = [ 'result' => 'ok', 'parkingSlotUuid' => $parkingSlot->uuid() ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
