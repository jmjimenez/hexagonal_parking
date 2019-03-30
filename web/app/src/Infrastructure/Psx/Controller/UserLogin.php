<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use Exception;
use Jmj\Parking\Application\Command\UserLogin as UserLoginCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class UserLogin extends ControllerAbstract
{
    /**
     * @Inject("UserLoginCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\UserLogin
     */
    protected $commandHandler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws Exception
     */
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new UserLoginCommand(
            $postData->userEmail,
            $postData->userPassword
        );

        try {
            $token = $this->commandHandler->execute($command);
            $data = [ 'result' => 'ok', 'token' => $token ];
        } catch (UserNotFound $e) {
            $data = [ 'result' => 'error', 'message' => 'User not found' ];
        } catch (ParkingException $e) {
            $data = [ 'result' => 'error', 'message' => $e->getMessage() ];
        }

        $this->responseWriter->setBody($response, $data, $request);
    }
}
