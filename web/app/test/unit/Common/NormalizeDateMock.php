<?php

namespace Jmj\Test\Unit\Common;

use DateTimeImmutable;
use DateTimeInterface;
use Jmj\Parking\Common\NormalizeDate;

class NormalizeDateMock
{
    use NormalizeDate;

    /**
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @return bool
     */
    public function testLessThanOrEqual(DateTimeInterface $date1, DateTimeInterface $date2) : bool
    {
        return $this->dateLessThanOrEqual($date1, $date2);
    }

    /**
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @return bool
     */
    public function testGreaterThanOrEqual(DateTimeInterface $date1, DateTimeInterface $date2) : bool
    {
        return $this->dateGreaterThanOrEqual($date1, $date2);
    }

    /**
     * @param DateTimeInterface $date
     * @param int $days
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public function testIncrementDate(DateTimeInterface $date, int $days) : DateTimeImmutable
    {
        return $this->incrementDate($date, $days);
    }

    /**
     * @param DateTimeInterface $date
     * @param int $days
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public function testDecrementDate(DateTimeInterface $date, int $days) : DateTimeImmutable
    {
        return $this->decrementDate($date, $days);
    }

    /**
     * @param DateTimeInterface $date
     * @return DateTimeImmutable
     */
    public function testNormalizeDate(DateTimeInterface $date) : string
    {
        return $this->normalizeDate($date);
    }

    public function testInRange(DateTimeInterface $date, DateTimeInterface $fromDate, DateTimeInterface $toDate) : bool
    {
        return $this->dateInRange($date, $fromDate, $toDate);
    }
}
