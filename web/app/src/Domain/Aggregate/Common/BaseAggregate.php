<?php

namespace Jmj\Parking\Domain\Aggregate\Common;

use Exception;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Service\Event\DomainEventsBroker;
use Ramsey\Uuid\Uuid;

abstract class BaseAggregate
{
    /**
     * @var string
     */
    protected static $lastUuid = null;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var DomainEventsBroker
     */
    protected static $eventBroker;

    abstract protected function getClassName() : string;

    /**
     * @throws ExceptionGeneratingUuid
     */
    public function __construct()
    {
        $this->generateUuid();
    }

    /**
     * @param DomainEventsBroker $eventsBroker
     */
    public static function setDomainEventBroker(DomainEventsBroker $eventsBroker)
    {
        self::$eventBroker = $eventsBroker;
    }

    /**
     * @return string
     */
    public function uuid() : string
    {
        return $this->uuid;
    }

    /**
     * @param string $eventName
     * @param mixed  $payload
     */
    protected function publishEvent(string $eventName, $payload = null)
    {
        if (self::$eventBroker == null) {
            return;
        }

        self::$eventBroker->publishEvent($this->getClassName(), $eventName, $this, $payload);
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
}
