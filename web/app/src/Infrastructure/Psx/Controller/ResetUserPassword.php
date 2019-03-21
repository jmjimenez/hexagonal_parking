<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Jmj\Parking\Application\Command\ResetUserPassword as ResetUserPasswordCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class ResetUserPassword extends ControllerAbstract
{
    /**
     * @Inject("ResetUserPasswordCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\ResetUserPassword
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

        $command = new ResetUserPasswordCommand(
            $postData->userEmail,
            $postData->passwordToken,
            $postData->userPassword
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
