<?php

namespace Jmj\Test\Unit\Infrastructure\Aggregate\Event;

use Jmj\Test\Unit\Common\DomainEventsRegister;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;
use PHPUnit\Framework\TestCase;

class DomainEventsRegisterTest extends TestCase
{
    use DomainEventsRegister;

    public function testStartRecordingEvents()
    {
        $domainBroker = SynchronousEventsBroker::getInstance();
        $domainBroker->resetSubscriptions();

        $objectA = new DomainEventsTestA();
        $objectB = new DomainEventsTestB();

        $objectA->publishEvent();
        $objectB->publishEvent();

        $this->assertEquals([], $this->recordedEventNames);

        $this->startRecordingEvents();

        $objectA->publishEvent();
        $objectB->publishEvent();

        $this->assertEquals(
            [ DomainEventsTestA::EVENT_TEST_A, DomainEventsTestB::EVENT_TEST_B ],
            $this->recordedEventNames
        );
    }
}

class DomainEventsTestA
{
    public const EVENT_TEST_A = 'DomainEventsTestAEvent';

    public function publishEvent()
    {
        $domainBroker = SynchronousEventsBroker::getInstance();

        $domainBroker->publishEvent(get_class($this), self::EVENT_TEST_A, $this);
    }
}

class DomainEventsTestB
{
    public const EVENT_TEST_B = 'DomainEventsTestAEvent';

    public function publishEvent()
    {
        $domainBroker = SynchronousEventsBroker::getInstance();

        $domainBroker->publishEvent(get_class($this), self::EVENT_TEST_B, $this);
    }
}
