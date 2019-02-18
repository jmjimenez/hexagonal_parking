<?php

namespace Jmj\Test\Unit\Domain\Aggregate;

use Jmj\Parking\Domain\Aggregate\BaseAggregate;
use Jmj\Parking\Common\DomainEventsBroker;
use Jmj\Parking\Domain\Aggregate\Exception\ExceptionGeneratingUuid;
use PHPUnit\Framework\TestCase;

class BaseAggregateTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        BaseAggregate::setDomainEventBroker(DomainEventsBroker::getInstance());
    }

    /**
     *
     * @throws ExceptionGeneratingUuid
     */
    public function testGetInstance()
    {
        $baseAggregateMock = new BaseAggregateMock();
        $this->assertNotEmpty($baseAggregateMock->uuid());
    }

    /**
     * @throws ExceptionGeneratingUuid
     */
    public function testGetInstanceWithDifferentUUids()
    {
        for ($i = 0, $baseAggregageMocks = []; $i < 20; $i++) {
            $baseAggregageMocks[] = new BaseAggregateMock();
        }

        for ($i = 0; $i < count($baseAggregageMocks); $i++) {
            for ($j = 0; $j < count($baseAggregageMocks); $j++) {
                if ($i != $j) {
                    $this->assertNotEquals($baseAggregageMocks[$i]->uuid(), $baseAggregageMocks[$j]->uuid());
                }
            }
        }
    }

    /**
     * @throws ExceptionGeneratingUuid
     */
    public function testPublishEvent()
    {
        $baseAggregateMock = new BaseAggregateMock();
        $testPayload = [ 1, 2, 3 ];

        $eventBroker = DomainEventsBroker::getInstance();

        $eventBroker->resetSubscriptions();

        $eventBroker->subscribeToClassEvents(
            BaseAggregateMock::class,
            function(string $className, string $eventName, object $object, $payload) use ($testPayload) {
                $this->assertEquals($className, BaseAggregateMock::class);
                $this->assertEquals($eventName, BaseAggregateMock::EVENT_TESTEVENT);
                $this->assertInstanceOf(BaseAggregateMock::class, $object);
                $this->assertEquals($payload, $testPayload);
            }
        );

        $baseAggregateMock->publishTestEvent($testPayload);
    }
}

class BaseAggregateMock extends BaseAggregate
{
    public const EVENT_TESTEVENT = 'testEvent';

    public static $testPayload = [1, 2, 3];

    protected function getClassName(): string
    {
        return self::class;
    }

    public function publishTestEvent($testPayload)
    {
        $this->publishEvent(self::EVENT_TESTEVENT, $testPayload);
    }
}
