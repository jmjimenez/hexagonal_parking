<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use Jmj\Parking\Common\DomainEventsRegister;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    use DataSamplesGenerator;
    use DomainEventsRegister;
    use AssertSqlStatements;

    /** @var TestContainer */
    protected $container;

    /**
     * @throws \Jmj\Parking\Common\Exception\PdoExecuteError
     * @throws \Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid
     * @throws \Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserEmailInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserNameAlreadyExists
     * @throws \Jmj\Parking\Domain\Exception\UserNameInvalid
     * @throws \Jmj\Parking\Domain\Exception\UserPasswordInvalid
     */
    protected function setUp(): void
    {
        $this->createTestContainer();

        $this->createTestCase(
            $this->container->get('PdoProxy'),
            $this->container->get('UserRepository'),
            $this->container->get('ParkingRepository')
        );
    }

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

        return 'Bearer '  . $authorizationKey;
    }

    /**
     * @param TestOutput $output
     */
    protected function assertOkResponse(TestOutput $output) : void
    {
        $result = json_decode($output->output(), true);
        $this->assertTrue(isset($result['result']));
        $this->assertEquals('ok', $result['result']);
    }


    protected function assertResponse(TestOutput $output, string $param, callable $callback)
    {
        $result = json_decode($output->output(), true);
        $this->assertTrue(isset($result[$param]));
        $callback($result[$param]);
    }

    /**
     * @param TestOutput $output
     * @param int $count
     */
    protected function assertResponseCount(TestOutput $output, int $count) : void
    {
        $result = json_decode($output->output(), true);
        $this->assertEquals($count, count($result));
    }

    /**
     * @param bool $value
     * @return string
     */
    protected function boolToString(bool $value): string
    {
        return  $value ? 'true' : 'false';
    }
}
