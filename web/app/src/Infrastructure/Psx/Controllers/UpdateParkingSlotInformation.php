<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use Jmj\Parking\Application\Command\UpdateParkingSlotInformation as UpdateParkingSlotInformationCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class UpdateParkingSlotInformation extends BaseController
{
    /**
     * @Inject("UpdateParkingSlotInformationCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\UpdateParkingSlotInformation
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

        $command = new UpdateParkingSlotInformationCommand(
            $this->loggedInUser->uuid(),
            $postData->parkingUuid,
            $postData->parkingSlotUuid,
            $postData->number,
            $postData->description
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
