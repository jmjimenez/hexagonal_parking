<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use PSX\Framework\Config\Config;
use PSX\Framework\Dispatch\Dispatch;
use PSX\Framework\Environment\EngineInterface;
use PSX\Framework\Environment\WebServer\ResponseFactory;

class TestEngine implements EngineInterface
{
    /**
     * @var TestRequest
     */
    protected $testRequest;

    /**
     * @var TestOutput
     */
    protected $testOutput;

    public function __construct(TestRequest $input, TestOutput $output)
    {
        $this->testRequest  = $input;
        $this->testOutput = $output;
    }

    /**
     * @inheritdoc
     */
    public function serve(Dispatch $dispatch, Config $config)
    {
        $request  = $this->testRequest->generateRequest();
        $response = (new ResponseFactory())->createResponse();

        $response = $dispatch->route($request, $response);

        $this->testOutput->setOutput($response->getBody()->__toString());

        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 600 ? 1 : 0;
    }
}
