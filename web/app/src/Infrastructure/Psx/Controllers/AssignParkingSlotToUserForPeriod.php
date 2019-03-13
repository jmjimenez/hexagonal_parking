<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\AssignParkingSlotToUserForPeriod as AssignParkingSlotToUserForPeriodCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class AssignParkingSlotToUserForPeriod extends BaseController
{
    /**
     * @Inject("AssignParkingSlotToUserForPeriodCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\AssignParkingSlotToUserForPeriod
     */
    protected $commandHandler;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws \Jmj\Parking\Application\Command\Handler\Exception\ParkingNotFound
     * @throws \Exception
     */
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $postData = $this->requestReader->getBody($request);

        $command = new AssignParkingSlotToUserForPeriodCommand(
            $this->loggedInUser->uuid(),
            $postData->userUuid,
            $postData->parkingUuid,
            $postData->parkingSlotUuid,
            new DateTimeImmutable($postData->fromDate),
            new DateTimeImmutable($postData->toDate),
            $postData->exclusive === 'true'
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
