<?php

namespace Jmj\Parking\Common;

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
     * @return DomainEventsBroker
     */
    private function getEventBroker(): DomainEventsBroker
    {
        return DomainEventsBroker::getInstance();
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