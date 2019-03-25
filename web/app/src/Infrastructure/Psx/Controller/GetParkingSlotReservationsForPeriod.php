<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\GetParkingSlotReservationsForPeriod
    as GetParkingSlotReservationsForPeriodCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class GetParkingSlotReservationsForPeriod extends Common\BaseController
{
    /**
     * @Inject("GetParkingSlotReservationsForPeriodCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\GetParkingSlotReservationsForPeriod
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

        $command = new GetParkingSlotReservationsForPeriodCommand(
            $this->loggedInUser->uuid(),
            $postData->parkingUuid,
            $postData->parkingSlotUuid,
            new DateTimeImmutable($postData->fromDate),
            new DateTimeImmutable($postData->toDate)
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
