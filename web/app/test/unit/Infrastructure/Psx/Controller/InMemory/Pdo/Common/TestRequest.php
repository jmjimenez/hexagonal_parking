<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use PSX\Http\Request;
use PSX\Uri\Uri;

class TestRequest
{
    /** @var string */
    protected $method;

    /** @var string */
    protected $path;

    /** @var string */
    protected $authorization;

    /** @var string[] */
    protected $body;

    /**
     * @param string $method
     * @param string $path
     * @param string|null $authorization
     * @param string|null $body
     */
    public function __construct(string $method, string $path, string $authorization = null, string $body = null)
    {
        //TODO: $authorization should be the last parameter
        $this->method = $method;
        $this->path = $path;
        $this->authorization = $authorization;
        $this->body = $body;
    }

    public function generateRequest() : Request
    {
        $scheme = 'http';
        $host = 'parking.local';
        $query = null;
        $headers = [
            'HOST' => 'parking.local:8000',
            'ACCEPT' => 'application/json',
        ];
        if ($this->authorization !== null) {
            $headers['AUTHORIZATION'] = $this->authorization;
        }

        return new Request(
            new Uri($scheme, $host, $this->path, $query),
            $this->method,
            $headers,
            $this->body
        );
    }
}
