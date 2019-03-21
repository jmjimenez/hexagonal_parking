<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\ReserveParkingSlotForUserAndPeriod as ReserveParkingSlotForUserAndPeriodCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class ReserveParkingSlotForUserAndPeriod extends BaseController
{
    /**
     * @Inject("ReserveParkingSlotForUserAndPeriodCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\ReserveParkingSlotForUserAndPeriod
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

        $command = new ReserveParkingSlotForUserAndPeriodCommand(
            $this->loggedInUser->uuid(),
            $postData->userUuid,
            $postData->parkingUuid,
            $postData->parkingSlotUuid,
            new DateTimeImmutable($postData->fromDate),
            new DateTimeImmutable($postData->toDate)
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
