<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Jmj\Parking\Application\Command\GetUserInformation as GetUserInformationCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class GetUserInformation extends BaseController
{
    /**
     * @Inject("GetUserInformationCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\GetUserInformation
     */
    protected $commandHandler;

    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new GetUserInformationCommand(
            $this->loggedInUser->uuid(),
            $postData->userUuid
        );

        try {
            $result = $this->commandHandler->execute($command);
            $data = [ 'result' => $result ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
