<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    use DataSamplesGenerator;
    use DomainEventsRegister;

    /** @var TestContainer */
    protected $container;

    /**
     * @param TestRequest $request
     * @return TestOutput
     */
    protected function executeRequest(TestRequest $request): TestOutput
    {
        $output = new TestOutput();

        $engine = new TestEngine($request, $output);
        $environment = new TestEnvironment($this->container, $engine);

        $this->startRecordingEvents();

        $environment->serve();

        return $output;
    }

    /**
     *
     */
    protected function createTestContainer() : void
    {
        $this->container = new TestContainer();
        $this->container->setParameter(
            'config.file',
            __DIR__ . '/../../../../../../../../config/psx/configuration.php'
        );
    }

    /**
     * @param string|null $email
     * @param string|null $password
     * @return string
     */
    protected function generateAuthorizationKey(string $email = null, string $password = null): string
    {
        $email = $email ??  $this->userAdmin->email();
        $password = $password ?? $this->getUserPassword();

        $jwtConfig = $this->container->getConfig()->get('parking_jwt');

        $authorizationKey = \Firebase\JWT\JWT::encode(
            [ 'email' => $email, 'password' => $password ],
            $jwtConfig['secret']
        );

        return $authorizationKey;
    }
}
