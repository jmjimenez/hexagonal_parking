<?php

namespace Jmj\Test\Unit\Infrastructure\Aggregate\Event;

use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;
use PHPUnit\Framework\TestCase;

class DomainEventsBrokerTest extends TestCase
{
    /** @var SynchronousEventsBroker */
    private $domainEventsBroker;

    /** @var array */
    private $callbackData;

    protected function setUp()
    {
        parent::setUp();

        $this->domainEventsBroker = SynchronousEventsBroker::getInstance();
    }

    /**
     *
     */
    public function testGetInstance()
    {
        $secondInstance = SynchronousEventsBroker::getInstance();

        $this->assertEquals($this->domainEventsBroker, $secondInstance);
    }

    /**
     *
     */
    public function testSubscribeToAllEventsWhenEventIsPublished()
    {
        $className = 'className';
        $eventName = 'eventName';
        $object = $this;
        $payload = [1, 2, 3];

        $this->callbackData = [
            'className' => $className,
            'eventName' => $eventName,
            'object' => $object,
            'payload' => $payload,
            'eventCalled' => false,
        ];

        $callable = function (
            string $className,
            string $eventName,
            object $object,
            $payload
        ) {
            $this->callbackData['eventCalled'] = true;

            $this->assertEquals($this->callbackData['className'], $className);
            $this->assertEquals($this->callbackData['eventName'], $eventName);
            $this->assertEquals($this->callbackData['object'], $object);
            $this->assertEquals($this->callbackData['payload'], $payload);
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToAllEvents($callable);
        $this->domainEventsBroker->publishEvent($className, $eventName, $object, $payload);

        $this->assertEquals(true, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToAllEventsWhenSeveralEventArePublished()
    {
        $classNames = [ 'className1', 'className2' ];
        $eventNames = [ 'eventName1', 'eventName2' ];
        $object = $this;

        $this->callbackData = [
            'eventCalled' => 0,
        ];

        $callable = function () {
            $this->callbackData['eventCalled']++;
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToAllEvents($callable);

        foreach ($classNames as $className) {
            foreach ($eventNames as $eventName) {
                $this->domainEventsBroker->publishEvent($className, $eventName, $object);
            }
        }

        $this->assertEquals(4, $this->callbackData['eventCalled']);
    }


    /**
     *
     */
    public function testSubscribeToClassEventsWhenClassEventIsPublished()
    {
        $className = 'className';
        $eventName = 'eventName';
        $object = $this;
        $payload = [1, 2, 3];

        $this->callbackData = [
            'className' => $className,
            'eventName' => $eventName,
            'object' => $object,
            'payload' => $payload,
            'eventCalled' => false,
        ];

        $callable = function (
            string $className,
            string $eventName,
            object $object,
            $payload
        ) {
            $this->callbackData['eventCalled'] = true;

            $this->assertEquals($this->callbackData['className'], $className);
            $this->assertEquals($this->callbackData['eventName'], $eventName);
            $this->assertEquals($this->callbackData['object'], $object);
            $this->assertEquals($this->callbackData['payload'], $payload);
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToClassEvents($className, $callable);
        $this->domainEventsBroker->publishEvent($className, $eventName, $object, $payload);

        $this->assertEquals(true, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToClassEventsWhenClassEventIsNotPublished()
    {
        $className = 'className';
        $this->callbackData['eventCalled'] = false;

        $callable = function () {
            $this->callbackData['eventCalled'] = true;
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToClassEvents($className, $callable);

        $this->assertEquals(false, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToClassEventsWhenOtherClassEventIsPublished()
    {
        $className = 'className';
        $publishedClassName = 'publishedClassName';
        $eventName = 'eventName';
        $object = $this;
        $payload = [1, 2, 3];
        $this->callbackData['eventCalled'] = false;

        $callable = function () {
            $this->callbackData['eventCalled'] = true;
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToClassEvents($className, $callable);
        $this->domainEventsBroker->publishEvent($publishedClassName, $eventName, $object, $payload);

        $this->assertEquals(false, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToClassEventsWhenSeveralClassEventArePublished()
    {
        $className = 'className';
        $eventName1 = 'eventName1';
        $eventName2 = 'eventName2';
        $object = $this;
        $payload = [1, 2, 3];
        $this->callbackData['eventCalled'] = 0;

        $callable = function () {
            $this->callbackData['eventCalled']++;
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToClassEvents($className, $callable);
        $this->domainEventsBroker->publishEvent($className, $eventName1, $object, $payload);
        $this->domainEventsBroker->publishEvent($className, $eventName2, $object, $payload);

        $this->assertEquals(2, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToSingleClassEventWhenEventIsPublished()
    {
        $className = 'className';
        $eventName = 'eventName';
        $object = $this;
        $payload = [1, 2, 3];

        $this->callbackData = [
            'className' => $className,
            'eventName' => $eventName,
            'object' => $object,
            'payload' => $payload,
            'eventCalled' => false,
        ];

        $callable = function (
            string $className,
            string $eventName,
            object $object,
            $payload
        ) {
            $this->callbackData['eventCalled'] = true;

            $this->assertEquals($this->callbackData['className'], $className);
            $this->assertEquals($this->callbackData['eventName'], $eventName);
            $this->assertEquals($this->callbackData['object'], $object);
            $this->assertEquals($this->callbackData['payload'], $payload);
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToSingleClassEvent($className, $eventName, $callable);
        $this->domainEventsBroker->publishEvent($className, $eventName, $object, $payload);

        $this->assertEquals(true, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToSingleClassEventWhenEventIsNotPublished()
    {
        $className = 'className';
        $eventName1 = 'eventName1';
        $eventName2 = 'eventName2';
        $object = $this;

        $this->callbackData = [
            'eventCalled' => false,
        ];

        $callable = function () {
            $this->callbackData['eventCalled'] = true;
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToSingleClassEvent($className, $eventName1, $callable);
        $this->domainEventsBroker->publishEvent($className, $eventName2, $object);

        $this->assertEquals(false, $this->callbackData['eventCalled']);
    }

    /**
     *
     */
    public function testSubscribeToSingleClassEventWhenEventIsPublishedByAnotherClass()
    {
        $className1 = 'className1';
        $className2 = 'className2';
        $eventName = 'eventName';
        $object = $this;

        $this->callbackData = [
            'eventCalled' => false,
        ];

        $callable = function () {
            $this->callbackData['eventCalled'] = true;
        };

        $this->domainEventsBroker->resetSubscriptions();
        $this->domainEventsBroker->subscribeToSingleClassEvent($className1, $eventName, $callable);
        $this->domainEventsBroker->publishEvent($className2, $eventName, $object);

        $this->assertEquals(false, $this->callbackData['eventCalled']);
    }
}
