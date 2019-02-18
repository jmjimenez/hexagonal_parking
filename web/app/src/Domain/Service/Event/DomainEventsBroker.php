<?php

namespace Jmj\Parking\Domain\Service\Event;

interface DomainEventsBroker
{
    /**
     * @return DomainEventsBroker
     */
    public static function getInstance() : DomainEventsBroker;

    /**
     * @param string $className
     * @param string $eventName
     * @param object $object
     * @param mixed | null $payload
     */
    public function publishEvent(string $className, string $eventName, object $object, $payload = null);

    /**
     */
    public function resetSubscriptions();

    /**
     * @param callable $callback
     */
    public function subscribeToAllEvents(callable $callback);

    /**
     * @param string $className
     * @param callable $callback
     */
    public function subscribeToClassEvents(string $className, callable $callback);

    /**
     * @param string $className
     * @param string $eventName
     * @param callable $callback
     */
    public function subscribeToSingleClassEvent(string $className, string $eventName, callable $callback);
}