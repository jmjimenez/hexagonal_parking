<?php

namespace Jmj\Test\Unit\Domain\Aggregate;

use Jmj\Parking\Domain\Aggregate\Common\BaseAggregate;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;
use PHPUnit\Framework\TestCase;

class BaseAggregateTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        BaseAggregate::setDomainEventBroker(SynchronousEventsBroker::getInstance());
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

        $eventBroker = SynchronousEventsBroker::getInstance();

        $eventBroker->resetSubscriptions();

        $eventBroker->subscribeToClassEvents(
            BaseAggregateMock::class,
            function (string $className, string $eventName, object $object, $payload) use ($testPayload) {
                $this->assertEquals($className, BaseAggregateMock::class);
                $this->assertEquals($eventName, BaseAggregateMock::EVENT_TESTEVENT);
                $this->assertInstanceOf(BaseAggregateMock::class, $object);
                $this->assertEquals($payload, $testPayload);
            }
        );

        $baseAggregateMock->publishTestEvent($testPayload);
    }
}
