<?php

namespace Jmj\Parking\Common;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class DateRangeProcessor
{
    use NormalizeDate;

    /**
     * @param DateTimeInterface $fromDate
     * @param DateTimeInterface $toDate
     * @param callable $callback
     * @throws Exception
     */
    public function process(DateTimeInterface $fromDate, DateTimeInterface $toDate, callable $callback)
    {
        $dateInterval = new DateInterval('P1D');

        for (
            $d = new DateTime($this->normalizeDate($fromDate));
            $this->lessThanOrEqual($d, $toDate);
            $d->add($dateInterval)
        ) {
            $d2 = new DateTimeImmutable($this->normalizeDate($d));
            $callback($d2);
        }
    }
}