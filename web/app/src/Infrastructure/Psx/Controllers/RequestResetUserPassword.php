<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use Jmj\Parking\Application\Command\RequestResetUserPassword as RequestResetUserPasswordCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class RequestResetUserPassword extends ControllerAbstract
{
    //TODO: instead of injecting the dependencies via comments perhaps there may be another way by code
    /**
     * @Inject("RequestResetUserPasswordCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\RequestResetUserPassword
     */
    protected $commandHandler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws \Exception
     */
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new RequestResetUserPasswordCommand(
            $postData->userEmail
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
