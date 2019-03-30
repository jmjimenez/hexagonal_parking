<?php

namespace Jmj\Parking\Common;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;

trait NormalizeDate
{

    /**
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @return bool
     */
    protected function dateLessThanOrEqual(DateTimeInterface $date1, DateTimeInterface $date2) : bool
    {
        return $this->normalizeDate($date1) <= $this->normalizeDate($date2);
    }

    /**
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @return bool
     */
    protected function dateGreaterThanOrEqual(DateTimeInterface $date1, DateTimeInterface $date2) : bool
    {
        return $this->normalizeDate($date1) >= $this->normalizeDate($date2);
    }

    /**
     * @param DateTimeInterface $date
     * @param int $days
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function incrementDate(DateTimeInterface $date, int $days) : DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('%s +%s days', $this->normalizeDate($date), $days));
    }

    /**
     * @param DateTimeInterface $date
     * @param int $days
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function decrementDate(DateTimeInterface $date, int $days) : DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('%s -%s days', $this->normalizeDate($date), $days));
    }

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function normalizeDate(DateTimeInterface $date) : string
    {
        return $date->format('Y-m-d');
    }

    /**
     * @param DateTimeInterface $date
     * @param DateTimeInterface $fromDate
     * @param DateTimeInterface $toDate
     * @return bool
     */
    protected function dateInRange(
        DateTimeInterface $date,
        DateTimeInterface $fromDate,
        DateTimeInterface $toDate
    ) : bool {
        return $this->normalizeDate($date) >= $this->normalizeDate($fromDate)
            && $this->normalizeDate($date) <= $this->normalizeDate($toDate);
    }
}
