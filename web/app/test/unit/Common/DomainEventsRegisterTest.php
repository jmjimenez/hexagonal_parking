<?php

namespace Jmj\Test\Unit\Common;

use Jmj\Parking\Common\EventsRecorder;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;
use PHPUnit\Framework\TestCase;

class DomainEventsRegisterTest extends TestCase
{
    use EventsRecorder;

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
