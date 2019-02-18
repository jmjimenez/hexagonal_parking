<?php

namespace Jmj\Parking\Common;

use Jmj\Parking\Domain\Service\Event\DomainEventsBroker as DomainEventsBrokerInterface;

class DomainEventsBroker implements DomainEventsBrokerInterface
{
    private const ALL_EVENTS = '*';

    /** @var DomainEventsBroker */
    protected static $singleton = null;

    /** @var callable[] */
    protected $classEventsSubscriptions = [];

    /** @var callable[] */
    protected $allEventsSubscriptions = [];

    /**
     * DomainEventsBroker constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public static function getInstance() : DomainEventsBrokerInterface
    {
        if (self::$singleton === null) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    /**
     * @inheritdoc
     */
    public function resetSubscriptions()
    {
        $this->classEventsSubscriptions = [];
        $this->allEventsSubscriptions = [];
    }

    /**
     * @inheritdoc
     */
    public function subscribeToAllEvents(callable $callback)
    {
        $this->allEventsSubscriptions[] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function subscribeToClassEvents(string $className, callable $callback)
    {
        if (!isset($this->classEventsSubscriptions[$className])) {
            $this->classEventsSubscriptions[$className] = [];
        }

        if (!isset($this->classEventsSubscriptions[$className][self::ALL_EVENTS])) {
            $this->classEventsSubscriptions[$className][self::ALL_EVENTS] = [];
        }

        $this->classEventsSubscriptions[$className][self::ALL_EVENTS][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function subscribeToSingleClassEvent(string $className, string $eventName, callable $callback)
    {
        if (!isset($this->classEventsSubscriptions[$className])) {
            $this->classEventsSubscriptions[$className] = [];
        }

        if (!isset($this->classEventsSubscriptions[$className][$eventName])) {
            $this->classEventsSubscriptions[$className][$eventName] = [];
        }

        $this->classEventsSubscriptions[$className][$eventName][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function publishEvent(string $className, string $eventName, object $object, $payload = null)
    {
        foreach ($this->allEventsSubscriptions as $subscription) {
            $subscription($className, $eventName, $object, $payload);
        }

        if (!isset($this->classEventsSubscriptions[$className])) {
            return;
        }

        if (isset($this->classEventsSubscriptions[$className][self::ALL_EVENTS])) {
            foreach ($this->classEventsSubscriptions[$className][self::ALL_EVENTS] as $subscription) {
                $subscription($className, $eventName, $object, $payload);
            }
        }

        if (isset($this->classEventsSubscriptions[$className][$eventName])) {
            foreach ($this->classEventsSubscriptions[$className][$eventName] as $subscription) {
                $subscription($className, $eventName, $object, $payload);
            }
        }
    }
}