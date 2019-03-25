<?php

namespace Jmj\Parking\Common;

use Jmj\Parking\Common\Pdo\PdoProxy;
use Jmj\Parking\Domain\Service\Event\DomainEventsBroker as DomainEventsBrokerInterface;
use Jmj\Parking\Infrastructure\Service\Event\InMemory\SynchronousEventsBroker;

trait EventsRecorder
{
    /** @var string[] */
    private $recordedEventNames = [];

    /** @var string[] */
    private $recordedClasses = [];

    /** @var object[]  */
    private $recordedObjects = [];

    /** @var array  */
    private $recordedPayloads = [];

    /** @var array  */
    protected $recordedSqlStatements = [];

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
        $this->recordedSqlStatements = [];

        $eventBroker = $this->getEventBroker();
        $eventBroker->resetSubscriptions();

        $eventBroker->subscribeToAllEvents(function (
            string $className,
            string $eventName,
            object $object,
            $payload
        ) {
            if ($className === PdoProxy::class) {
                if (preg_match('/^SELECT .*$/', $payload) === 0) {
                    $this->recordedSqlStatements[] = $payload;
                }
            } else {
                $this->recordedClasses[] = $className;
                $this->recordedEventNames[] = $eventName;
                $this->recordedObjects[] = $object;
                $this->recordedPayloads[] = $payload;
            }
        });
    }
}
