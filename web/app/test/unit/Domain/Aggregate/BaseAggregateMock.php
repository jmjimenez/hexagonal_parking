<?php

namespace Jmj\Test\Unit\Domain\Aggregate;

use Jmj\Parking\Domain\Aggregate\Common\BaseAggregate;

class BaseAggregateMock extends BaseAggregate
{
    public const EVENT_TESTEVENT = 'testEvent';

    public static $testPayload = [1, 2, 3];

    protected function getClassName(): string
    {
        return self::class;
    }

    public function publishTestEvent($testPayload)
    {
        $this->publishEvent(self::EVENT_TESTEVENT, $testPayload);
    }
}
