<?php

namespace Jmj\Test\Unit\Common;

use DateTime;
use DateTimeImmutable;
use Jmj\Parking\Common\DateRangeProcessor;
use PHPUnit\Framework\TestCase;

class DateRangeProcessorTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testProcessWhenDateTimeImmutableAreUsed()
    {
        $fromDate = new DateTimeImmutable('+1 days 10:00:00');
        $toDate = new DateTimeImmutable('+3 days 13:00:00');

        $processor = new DateRangeProcessor();

        $datesProcessed = [];

        $processor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use (&$datesProcessed) {
                $datesProcessed[] = $date;
            }
        );

        $this->assertEquals(
            [
                new DateTimeImmutable('+1 days 00:00:00'),
                new DateTimeImmutable('+2 days 00:00:00'),
                new DateTimeImmutable('+3 days 00:00:00')
            ],
            $datesProcessed
        );
    }

    /**
     * @throws \Exception
     */
    public function testProcessWhenDateTimesAreUsed()
    {
        $fromDate = new DateTime('+1 days 10:00:00');
        $toDate = new DateTime('+3 days 13:00:00');

        $processor = new DateRangeProcessor();

        $datesProcessed = [];

        $processor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use (&$datesProcessed) {
                $datesProcessed[] = $date;
            }
        );

        $this->assertEquals(
            [
                new DateTimeImmutable('+1 days 00:00:00'),
                new DateTimeImmutable('+2 days 00:00:00'),
                new DateTimeImmutable('+3 days 00:00:00')
            ],
            $datesProcessed
        );
    }
    /**
     * @throws \Exception
     */
    public function testProcessWhenOneSingleDayRange()
    {
        $fromDate = new DateTime('+1 days 13:00:00');
        $toDate = new DateTime('+1 days 11:00:00');

        $processor = new DateRangeProcessor();

        $datesProcessed = [];

        $processor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use (&$datesProcessed) {
                $datesProcessed[] = $date;
            }
        );

        $this->assertEquals(
            [
                new DateTimeImmutable('+1 days 00:00:00'),
            ],
            $datesProcessed
        );
    }

    /**
     * @throws \Exception
     */
    public function testProcessWhenWrongDateRange()
    {
        $fromDate = new DateTime('+3 days 10:00:00');
        $toDate = new DateTime('+1 days 13:00:00');

        $processor = new DateRangeProcessor();

        $datesProcessed = [];

        $processor->process(
            $fromDate,
            $toDate,
            function (DateTimeImmutable $date) use (&$datesProcessed) {
                $datesProcessed[] = $date;
            }
        );

        $this->assertEquals([], $datesProcessed);
    }
}
