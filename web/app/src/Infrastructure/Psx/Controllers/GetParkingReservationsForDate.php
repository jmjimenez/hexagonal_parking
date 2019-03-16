<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use DateTimeImmutable;
use Jmj\Parking\Application\Command\GetParkingReservationsForDate
    as GetParkingReservationsForDateCommand;
use Jmj\Parking\Application\Command\Handler\Exception\UserNotFound;
use Jmj\Parking\Domain\Exception\ParkingException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class GetParkingReservationsForDate extends BaseController
{
    /**
     * @Inject("GetParkingReservationsForDateCommandHandler")
     * @var \Jmj\Parking\Application\Command\Handler\GetParkingReservationsForDate
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

        $command = new GetParkingReservationsForDateCommand(
            $this->loggedInUser->uuid(),
            $postData->parkingUuid,
            new DateTimeImmutable($postData->date)
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
