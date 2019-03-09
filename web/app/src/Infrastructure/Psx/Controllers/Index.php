<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class Index extends BaseController
{
    public function onGet(RequestInterface $request, ResponseInterface $response)
    {
        $data = [
            'message' => 'this is a message',
        ];

        $this->responseWriter->setBody($response, $data, $request);
    }
}
