<?php

test03();

function test03()
{
    $date = new DateTimeImmutable();

    $date2 = new DateTimeImmutable(sprintf('%s +2 day', $date->format('Y-m-d')));

    var_dump($date);
    var_dump($date2);
}

function test02()
{
    $fromDate = new DateTime('2019-02-01 12:00:00');
    $toDate = new DateTime('2019-02-10 10:10:01');

    for ($d = $fromDate; $d->format('Y-m-d') <= $toDate->format('Y-m-d'); $d->add(new DateInterval('P1D'))) {
        echo sprintf("%s\n", $d->format('Y-m-d'));
    }
}

class Father
{
    protected function event(string $eventName)
    {
        $className = get_class($this);
        echo sprintf("%s : %s\n", $className, $eventName);
    }
}

class Son extends Father
{
    public function hello()
    {
        $this->event('hello');
    }
}

function test01()
{
    $s = new Son();

    $s->hello();
}
