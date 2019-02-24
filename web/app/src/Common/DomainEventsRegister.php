<?php

namespace Jmj\Parking\Common;

use Jmj\Parking\Domain\Service\Event\DomainEventsBroker as DomainEventsBrokerInterface;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;

trait DomainEventsRegister
{
    /** @var string[] */
    private $recordedEventNames = [];

    /** @var string[] */
    private $recordedClasses = [];

    /** @var object[]  */
    private $recordedObjects = [];

    /** @var array  */
    private $recordedPayloads = [];

    /**
     * @return SynchronousEventsBroker
     */
    private function getEventBroker() : DomainEventsBrokerInterface
    {
        return SynchronousEventsBroker::getInstance();
    }

    /**
     *
     */
    private function startRecordingEvents()
    {
        $this->recordedEventNames = [];
        $this->recordedClasses = [];
        $this->recordedObjects = [];
        $this->recordedPayloads = [];

        $eventBroker = $this->getEventBroker();
        $eventBroker->resetSubscriptions();

        $eventBroker->subscribeToAllEvents(function (
            string $className,
            string $eventName,
            object $object,
            $payload
        ) {
            $this->recordedClasses[] = $className;
            $this->recordedEventNames[] = $eventName;
            $this->recordedObjects[] = $object;
            $this->recordedPayloads[] = $payload;
        });
    }
}