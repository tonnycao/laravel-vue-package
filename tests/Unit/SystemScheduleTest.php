<?php

namespace FDT\DataLoader\Tests\Unit;

use FDT\DataLoader\Models;
use Orchestra\Testbench\TestCase;

class SystemScheduleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../../src/Database/factories');
    }

    /**
     * Test daily schedule
     *
     * @return void
     */
    public function testNextDailySchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'daily')->make();
        $this->assertEquals('2019-08-14 14:00:00', $systemSchedule->checkSchedule('2019-08-13'),
            'Next schedule should on next day same time');
    }

    /**
     * Test weekly schedule
     *
     * @return void
     */
    public function testNextWeeklySchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'weekly')->make();
        $this->assertEquals('2019-08-20 14:00:00', $systemSchedule->checkSchedule('2019-08-13'),
            'Next schedule should on next week same time');
    }

    /**
     * Test weekday schedule
     *
     * @return void
     */
    public function testNextWeekdaySchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'weekday')->make();
        $this->assertEquals('2019-08-14 14:00:00', $systemSchedule->checkSchedule('2019-08-13'),
            'Next schedule should on next day same time');
    }

    /**
     * Test weekend schedule
     *
     * @return void
     */
    public function testNextWeekendSchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'weekend')->make();
        $this->assertEquals('2019-08-17 14:00:00', $systemSchedule->checkSchedule('2019-08-13'),
            'Next schedule should on upcoming saturday same time');
    }

    /**
     * Test quarterly schedule
     *
     * @return void
     */
    public function testNextQuarterSchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'quarter')->make();
        $this->assertEquals('2019-10-01 14:00:00', $systemSchedule->checkSchedule('2019-08-13'),
            'Next schedule should on next fiscal year, week 1, day 2 same time');
    }

    /**
     * Test quarterly schedule same year
     *
     * @return void
     */
    public function testNextQuarterSameYearSchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'quarter')->make();
        $this->assertEquals('2019-07-02 14:00:00', $systemSchedule->checkSchedule('2019-05-13'),
            'Next schedule should on same fiscal year, week 1, day 2 same time');
    }

    /**
     * Test period schedule
     *
     * @return void
     */
    public function testNextPeriodSchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'period')->make();
        $this->assertEquals('2019-10-01 14:00:00', $systemSchedule->checkSchedule('2019-09-03'),
            'Next schedule should on next period, week 1, day 2 same time');
    }

    /**
     * Test period schedule
     *
     * @return void
     */
    public function testNextPeriodSameYearSchedule()
    {
        $systemSchedule = factory(Models\SystemSchedule::class, 'period')->make();
        $this->assertEquals('2019-09-03 14:00:00', $systemSchedule->checkSchedule('2019-08-13'),
            'Next schedule should on next period, week 1, day 2 same time');
    }
}
