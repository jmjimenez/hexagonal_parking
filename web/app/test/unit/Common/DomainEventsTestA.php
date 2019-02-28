<?php

namespace Jmj\Test\Unit\Common;

use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;

class DomainEventsTestA
{
    public const EVENT_TEST_A = 'DomainEventsTestAEvent';

    public function publishEvent()
    {
        $domainBroker = SynchronousEventsBroker::getInstance();

        $domainBroker->publishEvent(get_class($this), self::EVENT_TEST_A, $this);
    }
}
