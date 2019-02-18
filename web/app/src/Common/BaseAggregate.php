<?php

namespace Jmj\Parking\Common;

use Exception;
use Jmj\Parking\Common\Exception\ExceptionGeneratingUuid;
use Ramsey\Uuid\Uuid;

abstract class BaseAggregate
{
    /** @var string */
    private static $lastUuid = null;

    /** @var string */
    private $uuid;

    /** @var DomainEventsBroker */
    private $eventBroker;

    abstract protected function getClassName() : string;

    /**
     * BaseAggregate constructor.
     * @throws ExceptionGeneratingUuid
     */
    public function __construct()
    {
        $this->generateUuid();
    }

    /**
     * @return string
     */
    public function uuid() : string
    {
        return $this->uuid;
    }

    protected function publishEvent(string $eventName, $payload = null)
    {
        $eventBroker = $this->getEventBroker();
        $className = $this->getClassName();

        $eventBroker->publishEvent($className, $eventName, $this, $payload);
    }

    /**
     * @throws ExceptionGeneratingUuid
     */
    private function generateUuid()
    {
        do {
            try {
                $uuid = Uuid::uuid4()->__toString();
            } catch (Exception $e) {
                throw new ExceptionGeneratingUuid($e->getMessage(), $e->getCode());
            }
        } while ($uuid === self::$lastUuid);

        self::$lastUuid = $uuid;

        $this->uuid = $uuid;
    }

    /**
     * @return DomainEventsBroker
     */
    protected function getEventBroker() : DomainEventsBroker
    {
        if ($this->eventBroker === null) {
            $this->eventBroker = DomainEventsBroker::getInstance();
        }

        return $this->eventBroker;
    }
}