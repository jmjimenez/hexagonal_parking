<?php

namespace Jmj\Parking\Common;

class DomainEventsBroker
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
     * @return DomainEventsBroker
     */
    public static function getInstance() : DomainEventsBroker
    {
        if (self::$singleton === null) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    /**
     *
     */
    public function resetSubscriptions()
    {
        $this->classEventsSubscriptions = [];
        $this->allEventsSubscriptions = [];
    }

    /**
     * @param callable $callback
     */
    public function subscribeToAllEvents(callable $callback)
    {
        $this->allEventsSubscriptions[] = $callback;
    }

    /**
     * @param string $className
     * @param callable $callback
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
     * @param string $className
     * @param string $eventName
     * @param callable $callback
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
     * @param string $className
     * @param string $eventName
     * @param object $object
     * @param mixed $payload
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