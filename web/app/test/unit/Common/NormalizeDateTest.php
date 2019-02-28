<?php

namespace Jmj\Test\Unit\Common;

use DateTimeImmutable;
use DateTimeInterface;
use Jmj\Parking\Common\NormalizeDate;
use Nette\Utils\DateTime;
use PHPUnit\Framework\TestCase;

class NormalizeDateTest extends TestCase
{
    /** @var NormalizeDateMock */
    private $normalizeDate;

    protected function setUp()
    {
        parent::setUp();

        $this->normalizeDate = new NormalizeDateMock();
    }

    /**
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @param bool $expectedResult
     *
     * @dataProvider lessThanOrEqualDataProvider
     */
    public function testLessThanOrEqual(DateTimeInterface $date1, DateTimeInterface $date2, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizeDate->testLessThanOrEqual($date1, $date2));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function lessThanOrEqualDataProvider() : array
    {
        return [
            [  new DateTime(''), new DateTimeImmutable('+3 days'), true ],
            [  new DateTimeImmutable(''), new DateTimeImmutable('+3 days'), true ],
            [  new DateTimeImmutable('+3 days'), new DateTimeImmutable('+3 days'), true ],
            [  new \DateTime('00:10:00'), new DateTimeImmutable('00:00:00'), true ],
        ];
    }

    /**
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @param bool $expectedResult
     *
     * @dataProvider greaterThanOrEqualDataProvider
     */
    public function testGreaterThanOrEqual(DateTimeInterface $date1, DateTimeInterface $date2, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizeDate->testGreaterThanOrEqual($date1, $date2));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function greaterThanOrEqualDataProvider() : array
    {
        return [
            [  new DateTime(''), new DateTimeImmutable('+3 days'), false ],
            [  new DateTimeImmutable(''), new DateTimeImmutable('+3 days'), false ],
            [  new DateTimeImmutable('+3 days'), new DateTimeImmutable('+3 days'), true ],
            [  new \DateTime('00:10:00'), new DateTimeImmutable('00:00:00'), true ],
        ];
    }

    /**
     * @param DateTimeInterface $date
     * @param int $days
     * @param DateTimeImmutable $expectedResult
     * @throws \Exception
     *
     * @dataProvider incrementDateDataProvider
     */
    public function testIncrementDate(DateTimeInterface $date, int $days, DateTimeImmutable $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizeDate->testIncrementDate($date, $days));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function incrementDateDataProvider()
    {
        return [
            [ new DateTimeImmutable('+3 days'), 0, new DateTimeImmutable('+3 days 00:00:00') ],
            [ new DateTime('+3 days'), 3, new DateTimeImmutable('+6 days 00:00:00') ],
        ];
    }

    /**
     * @param DateTimeInterface $date
     * @param int $days
     * @param DateTimeImmutable $expectedResult
     *
     * @throws \Exception
     * @dataProvider decrementDateDataProvider
     */
    public function testDecrementDate(DateTimeInterface $date, int $days, DateTimeImmutable $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizeDate->testDecrementDate($date, $days));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function decrementDateDataProvider()
    {
        return [
            [ new DateTimeImmutable('+3 days'), 0, new DateTimeImmutable('+3 days 00:00:00') ],
            [ new DateTime('+3 days'), 3, new DateTimeImmutable('+0 days 00:00:00') ],
        ];
    }

    /**
     * @param DateTimeInterface $date
     * @param string $expectedResult
     *
     * @dataProvider normalizeDateDataProvider
     */
    public function testNormalizeDate(DateTimeInterface $date, string $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->normalizeDate->testNormalizeDate($date));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function normalizeDateDataProvider()
    {
        return [
            [ new DateTimeImmutable('2019-01-01 23:00:00'), '2019-01-01' ],
            [ new DateTime('2018-03-03 00:00:00'),  '2018-03-03' ],
        ];
    }

    /**
     * @param DateTimeInterface $date
     * @param DateTimeInterface $fromDate
     * @param DateTimeInterface $toDate
     * @param bool $expectedResult
     *
     * @dataProvider inRangeDataProvider
     */
    public function testInRange(
        DateTimeInterface $date,
        DateTimeInterface $fromDate,
        DateTimeInterface $toDate,
        bool $expectedResult
    ) {
        $this->assertEquals(
            $expectedResult,
            $this->normalizeDate->testInRange($date, $fromDate, $toDate)
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function inRangeDataProvider()
    {
        return [
            [
                new DateTimeImmutable('2019-01-01 23:00:00'),
                new DateTimeImmutable('2019-01-01 23:00:00'),
                new DateTimeImmutable('2019-01-01 00:00:00'),
                true
            ],
            [
                new DateTimeImmutable('2019-01-01 23:00:00'),
                new DateTimeImmutable('2019-01-05 23:00:00'),
                new DateTimeImmutable('2018-01-01 00:00:00'),
                false
            ],
            [
                new DateTime('2019-01-05 23:00:00'),
                new DateTime('2019-01-05 23:00:00'),
                new DateTime('2019-01-01 23:00:00'),
                false
            ],
        ];
    }
}

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
