<?php

namespace Jmj\Test\Unit\Common;

use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;

class DomainEventsTestB
{
    public const EVENT_TEST_B = 'DomainEventsTestAEvent';

    public function publishEvent()
    {
        $domainBroker = SynchronousEventsBroker::getInstance();

        $domainBroker->publishEvent(get_class($this), self::EVENT_TEST_B, $this);
    }
}
