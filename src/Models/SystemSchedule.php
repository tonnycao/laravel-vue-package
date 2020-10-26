<?php

namespace FDT\DataLoader\Models;

use App;
use Carbon\Carbon;
use CB\Fiscal\Fiscal;
use Illuminate\Database\Eloquent\Model;

class SystemSchedule extends Model
{
    protected $guarded = [];

    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_DATETIME = 'Y-m-d H:i:s';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_TEXT = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    const FREQUENCY_TYPE_QUARTER = 'quarterly';
    const FREQUENCY_TYPE_PERIOD = 'periodic';
    const FREQUENCY_TYPE_WEEK = 'weekly';
    const FREQUENCY_TYPE_DAY = 'daily';
    const FREQUENCY_TYPE_WEEKDAYS = 'weekdays';
    const FREQUENCY_TYPE_WEEKENDS = 'weekends';

    const FREQUENCY_TYPES = [
        self::FREQUENCY_TYPE_QUARTER => 'Quarterly',
        self::FREQUENCY_TYPE_PERIOD => 'Periodically',
        self::FREQUENCY_TYPE_WEEK => 'Weekly',
        self::FREQUENCY_TYPE_DAY => 'Daily',
        self::FREQUENCY_TYPE_WEEKDAYS => 'Weekdays',
        self::FREQUENCY_TYPE_WEEKENDS => 'Weekends',
    ];

    const FISCAL_QUARTER_PERIODS = [
        1,
        4,
        7,
        10,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(App\User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(App\User::class, 'updated_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deletedBy()
    {
        return $this->belongsTo(App\User::class, 'deleted_by');
    }

    /**
     * @param string
     * @param string|null $connection
     *
     * @return string
     * @throws \Exception
     */
    public function checkSchedule($today = null)
    {
        if ($today === null) {
            $today = Carbon::now($this->timezone)->format(self::FORMAT_DATE); // today's date in the specified timezone
        } else {
            $today = Carbon::instance(new \DateTime($today),
                new \DateTimeZone($this->timezone))->format(self::FORMAT_DATE); // today's date in the specified timezone
        }
        $fiscalDate = new Fiscal($today);
        $computedTime = null;

        // ensure the configuration is active
        if (self::STATUS_ACTIVE === $this->is_active) {
            // based on frequency type, we shall calculate the UTC time to schedule next job
            switch ($this->frequency_type) {
                case self::FREQUENCY_TYPE_QUARTER:
                    $computedTime = $this->quarterly($fiscalDate);
                    break;

                case self::FREQUENCY_TYPE_PERIOD:
                    $computedTime = $this->periodically($fiscalDate);
                    break;

                case self::FREQUENCY_TYPE_WEEK:
                    $computedTime = $this->weekly($fiscalDate);
                    break;

                case self::FREQUENCY_TYPE_DAY:
                    $computedTime = $this->daily($fiscalDate);
                    break;

                case self:: FREQUENCY_TYPE_WEEKDAYS:
                    $computedTime = $this->weekdays($fiscalDate);
                    break;

                case self:: FREQUENCY_TYPE_WEEKENDS:
                    $computedTime = $this->weekends($fiscalDate);
                    break;

                default:
                    throw new \Exception('Invalid frequency type');
            }
        }

        // get the date from computed time and append the specified time in the configuration
        $computedTime = explode(' ', $computedTime);
        $computedTime = sprintf('%s %s:00', $computedTime[0], $this->time);

        return $computedTime;
    }

    /**
     * @param Fiscal $fiscalDate
     * @param string $connection
     *
     * @return string
     */
    public function quarterly(Fiscal $fiscalDate)
    {
        $week = intval(substr($fiscalDate->formatPeriodWeek(), 10));
        $currentPeriod = $fiscalDate->period();
        $nextPeriod = array_filter(self::FISCAL_QUARTER_PERIODS, function ($value) use ($currentPeriod) {
            if ($value > $currentPeriod) {
                return $value;
            }
        });

        // if none of the conditions are true from above array_filter function, we know date falls in next fiscal year
        if (count($nextPeriod) === 0) {
            $nextPeriod[] = 1;
        }
        $nextPeriod = array_values($nextPeriod)[0];
        try {
            return $this->scheduleNextQuarter($fiscalDate, $nextPeriod, $this->day, $this->week);
        } catch (\Exception $ex) {
            echo sprintf(
                'An error occurred while determining quarterly schedule on line %s in file: %s with message %s',
                $ex->getLine(), $ex->getFile(), $ex->getMessage());
        }
    }

    /**
     * @param Fiscal $fiscalDate
     * @param string|null $connection
     *
     * @return string
     */
    public function periodically(Fiscal $fiscalDate)
    {
        $week = intval(substr($fiscalDate->formatPeriodWeek(), 10));
        $currentPeriod = $fiscalDate->period();
        $nextPeriod = ++$currentPeriod;
        try {
            return $this->scheduleNextPeriod($fiscalDate, $nextPeriod, $this->day, $this->week);
        } catch (\Exception $ex) {
            echo sprintf(
                'An error occurred while determining periodic schedule on line %s in file: %s with message %s',
                $ex->getLine(), $ex->getFile(), $ex->getMessage());
        }
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     * @throws \Exception
     */
    public function weekly(Fiscal $fiscalDate)
    {
        $week = intval(substr($fiscalDate->formatPeriodWeek(), 10));
        if ($this->day === $fiscalDate->date->dayOfWeek) {
            return $this->scheduleNextWeek($fiscalDate);
        }
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     * @throws \Exception
     */
    public function daily(Fiscal $fiscalDate)
    {
        return $this->scheduleNextDay($fiscalDate);
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     */
    public function weekdays(Fiscal $fiscalDate)
    {
        return $this->scheduleNextWeekday($fiscalDate);
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     */
    public function weekends(Fiscal $fiscalDate)
    {
        return $this->scheduleNextWeekend($fiscalDate);
    }

    /**
     * @param string $time
     * @param null|string $region
     * @param string|null $connection
     */
    public function createApprovedJob($time, $region = null, $connection = null)
    {
        $existingScheduledJobs = SystemJob::hasSystemJob([$this->id], $time, $connection);
        if (!$existingScheduledJobs) {
            $loadingLob = new SystemJob();
            $loadingLob->setConnection($connection);
            $loadingLob->region = $region;
            $loadingLob->type = $this->type;
            $loadingLob->subtype = $this->subtype;
            $loadingLob->status = SystemJob::STATUS_APPROVED;
            $loadingLob->user = $this->created_by;
            $loadingLob->name = sprintf('%s on %s', $this->name, $time);
            $loadingLob->system_config_id = $this->id;
            $loadingLob->scheduled_at = $time;
            $loadingLob->download = $this->download;
            $loadingLob->source = $this->source;
            $loadingLob->pattern = $this->pattern;
            $loadingLob->date_pattern = $this->date_pattern;
            $loadingLob->box_folder = $this->box_folder;
            $loadingLob->save();
        }
    }

    /**
     * @param Fiscal $fiscalDate
     * @param int $nextPeriod
     * @param int $day
     * @param int $week
     *
     * @return string
     * @throws \Exception
     */
    private function scheduleNextQuarter(Fiscal $fiscalDate, $nextPeriod, $day, $week = 0)
    {
        return $this->scheduleNextPeriod($fiscalDate, $nextPeriod, $day, $week);
    }

    /**
     * @param Fiscal $fiscalDate
     * @param int $nextPeriod
     * @param int $day
     * @param int $week
     *
     * @return string
     * @throws \Exception
     */
    private function scheduleNextPeriod(Fiscal $fiscalDate, $nextPeriod, $day, $week = 0)
    {
        if ($nextPeriod === 1) {
            $fiscalDate = $fiscalDate->date->addYear(1);
            $fiscalDate = new Fiscal($fiscalDate);
        } else if ($nextPeriod > 12) {
            $nextPeriod = 1;
            $fiscalDate = $fiscalDate->date->addYear(1);
            $fiscalDate = new Fiscal($fiscalDate);
        }

        $nextPeriodStartingWeek = $fiscalDate->periodBounds()[$nextPeriod][0];

        $localDate = new \DateTime($fiscalDate->getStartOfYear(), new \DateTimeZone($this->timezone));
        // subtract one week to land the exact week of the period
        $desiredWeek = $nextPeriodStartingWeek + $week - 1;
        // first get the sunday of the week as its important to add days from there
        $localDate->modify('last sunday');
        $localDate->add(new \DateInterval('P' . (7 * $desiredWeek + $day) . 'D'));
        //$localDate->add(new \DateInterval('P'. ($day - 1) . 'D'));
        $localDate->setTime($this->getHourMinute()[0], $this->getHourMinute()[1]);
        $utcDate = clone $localDate;

        return $utcDate->setTimezone(new \DateTimeZone('UTC'))->format(self::FORMAT_DATETIME);
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     * @throws \Exception
     */
    private function scheduleNextWeek(Fiscal $fiscalDate)
    {
        $localDate = new \DateTime($fiscalDate->date->format(self::FORMAT_DATETIME),
            new \DateTimeZone($this->timezone));
        $localDate->add(new \DateInterval('P' . 7 . 'D'));
        $localDate->setTime($this->getHourMinute()[0], $this->getHourMinute()[1]);
        $utcDate = clone $localDate;

        return $utcDate->setTimezone(new \DateTimeZone('UTC'))->format(self::FORMAT_DATETIME);
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     * @throws \Exception
     */
    private function scheduleNextDay(Fiscal $fiscalDate)
    {
        $localDate = new \DateTime($fiscalDate->date->format(self::FORMAT_DATETIME),
            new \DateTimeZone($this->timezone));
        $localDate->add(new \DateInterval('P' . 1 . 'D'));
        $localDate->setTime($this->getHourMinute()[0], $this->getHourMinute()[1]);
        $utcDate = clone $localDate;

        return $utcDate->setTimezone(new \DateTimeZone('UTC'))->format(self::FORMAT_DATETIME);
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     */
    private function scheduleNextWeekday(Fiscal $fiscalDate)
    {
        $localDate = new \DateTime($fiscalDate->date->nextWeekday()->format(self::FORMAT_DATETIME),
            new \DateTimeZone($this->timezone));
        $localDate->setTime($this->getHourMinute()[0], $this->getHourMinute()[1]);
        $utcDate = clone $localDate;

        return $utcDate->setTimezone(new \DateTimeZone('UTC'))->format(self::FORMAT_DATETIME);
    }

    /**
     * @param Fiscal $fiscalDate
     *
     * @return string
     */
    private function scheduleNextWeekend(Fiscal $fiscalDate)
    {
        $localDate = new \DateTime($fiscalDate->date->nextWeekendDay()->format(self::FORMAT_DATETIME),
            new \DateTimeZone($this->timezone));
        $localDate->setTime($this->getHourMinute()[0], $this->getHourMinute()[1]);
        $utcDate = clone $localDate;

        return $utcDate->setTimezone(new \DateTimeZone('UTC'))->format(self::FORMAT_DATETIME);
    }

    /**
     * @return array
     */
    private function getHourMinute()
    {
        return explode(':', $this->time);
    }

    /**
     * @return string
     */
    public function getCurrentTimeZone()
    {
        return date_default_timezone_get();
    }
}
