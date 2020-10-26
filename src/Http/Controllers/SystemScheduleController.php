<?php

namespace FDT\DataLoader\Http\Controllers;

use DB;
use FDT\DataLoader\Models\SystemJob;
use FDT\DataLoader\Models\SystemSchedule;
use FDT\DataLoader\Http\Requests;

class SystemScheduleController extends Controller
{
    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function index()
    {
        $region = self::getRegion();
        $per_page = 50;
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $cpath = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath();

        $d = DB::table('system_schedules as lss')
            ->leftJoin('users as u1', 'u1.id', '=', 'lss.created_by')
            ->leftJoin('users as u2', 'u2.id', '=', 'lss.updated_by')
            ->leftJoin('users as u3', 'u3.id', '=', 'lss.deleted_by')
            ->select(DB::raw('lss.id, lss.frequency_type, lss.week, lss.day, lss.time, lss.timezone, lss.type, lss.subtype, lss.is_active, lss.download, u1.name as `created_by`, lss.created_at, u2.name as `updated_by`, lss.updated_at, lss.deleted_by'))
            ->where('lss.deleted_by', '=', null);

        if ($region) {
            $d = $d->where('region', $region);
        }

        $system_schedules = $d->skip(($page - 1) * $per_page)
            ->take($per_page)
            ->get()->toArray();

        $total = $total = $d->count();

        $r = new \Illuminate\Pagination\LengthAwarePaginator($system_schedules, $total, $per_page, $page,
            ['path' => $cpath]);
        return $r;
    }

    /**
     * @return array
     */
    public function store()
    {
        $name = trim(request('name'));
        $frequency_type = trim(request('frequency_type'));
        $week = trim(request('week'));
        $day = trim(request('day'));
        $timezone = trim(request('timezone'));
        $hour = trim(request('hour'));
        $min = trim(request('min', 0));
        $isActive = trim(request('is_active'));
        $type = trim(request('type'));
        $subtype = trim(request('subtype'));
        $download = trim(request('download'));
        $source = trim(request('source'));
        $pattern = trim(request('pattern'));
        $date_pattern = trim(request('date_pattern'));
        $box_folder = trim(request('box_folder'));

        $errors = [];
        $errors = $this->validateRequest($errors);

        $ok = false;
        if (!$errors) {
            $sysConfig = new SystemSchedule();
            $sysConfig->created_by = auth()->id();
            $sysConfig->name = $name;
            $sysConfig->frequency_type = $frequency_type;
            $sysConfig->week = self::getWeek($week, $frequency_type);
            $sysConfig->day = self::getDay($day, $frequency_type);
            $sysConfig->timezone = $timezone;
            $sysConfig->time = sprintf("%s:%s", $hour, $min);
            $sysConfig->download = $download;
            $sysConfig->source = $source;
            $sysConfig->pattern = $pattern;
            $sysConfig->date_pattern = $date_pattern;
            $sysConfig->box_folder = $box_folder;
            $sysConfig->type = $type;
            $sysConfig->subtype = $subtype;
            $sysConfig->is_active = $isActive;
            $sysConfig->created_at = now();
            $sysConfig->save();

            $ok = true;
        }

        return ['ok' => $ok, 'errors' => $errors];
    }

    /**
     * @param SystemSchedule $sysConfig
     *
     * @return array
     */
    public function update(SystemSchedule $sysConfig)
    {
        $name = trim(request('name'));
        $frequency_type = trim(request('frequency_type'));
        $week = trim(request('week'));
        $day = trim(request('day'));
        $timezone = trim(request('timezone'));
        $hour = trim(request('hour'));
        $min = trim(request('min', 0));
        $isActive = trim(request('is_active'));
        $type = trim(request('type'));
        $subtype = trim(request('subtype'));
        $download = trim(request('download'));
        $source = trim(request('source'));
        $pattern = trim(request('pattern'));
        $date_pattern = trim(request('date_pattern'));
        $box_folder = trim(request('box_folder'));

        $errors = [];
        $errors = $this->validateRequest($errors);

        $ok = false;

        if (!$errors && $sysConfig) {
            $sysConfig->updated_by = auth()->id();
            $sysConfig->name = $name;
            $sysConfig->frequency_type = $frequency_type;
            $sysConfig->week = self::getWeek($week, $frequency_type);
            $sysConfig->day = self::getDay($day, $frequency_type);
            $sysConfig->timezone = $timezone;
            $sysConfig->time = sprintf("%s:%s", $hour, $min);
            $sysConfig->type = $type;
            $sysConfig->subtype = $subtype;
            $sysConfig->download = $download;
            $sysConfig->source = $source;
            $sysConfig->pattern = $pattern;
            $sysConfig->date_pattern = $date_pattern;
            $sysConfig->box_folder = $box_folder;
            $sysConfig->is_active = $isActive;
            $sysConfig->updated_at = now();
            $sysConfig->save();

            $ok = true;
        }

        return ['ok' => $ok, 'errors' => $errors];
    }

    /**
     * @param SystemSchedule $sys_config
     *
     * @return array
     */
    public function delete(SystemSchedule $sys_config)
    {
        $sys_config->deleted_by = auth()->id();
        $sys_config->updated_at = now();
        $sys_config->is_active = SystemSchedule::STATUS_INACTIVE;
        $sys_config->save();

        return ['refresh' => 1,];
    }

    /**
     * @param array $errors
     *
     * @return array
     */
    private function validateRequest(array $errors)
    {
        $region = self::getRegion();
        $name = trim(request('name'));
        $frequency_type = trim(request('frequency_type'));
        $week = trim(request('week'));
        $day = trim(request('day'));
        $timezone = trim(request('timezone'));
        $hour = trim(request('hour'));
        $min = trim(request('min', 0));
        $isActive = trim(request('is_active'));
        $type = trim(request('type'));
        $subType = trim(request('subtype'));
        $download = trim(request('download'));
        $source = trim(request('source'));
        $pattern = trim(request('pattern'));
        $date_pattern = trim(request('date_pattern'));
        $box_folder = trim(request('box_folder'));

        if (strlen($name) < 3) {
            $errors[] = 'Please enter a name for the request';
        }
        switch ($frequency_type) {
            case SystemSchedule::FREQUENCY_TYPE_QUARTER:

            case SystemSchedule::FREQUENCY_TYPE_PERIOD:
                if (empty($week) || !in_array($week, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14])) {
                    $errors[] = 'Please select a valid option for week';
                }
                if (empty($day) || !in_array($day, [1, 2, 3, 4, 5, 6, 7])) {
                    $errors[] = 'Please select a valid option for day';
                }
                break;

            case SystemSchedule::FREQUENCY_TYPE_WEEK:

                if (empty($day) || !in_array($day, [1, 2, 3, 4, 5, 6, 7])) {
                    $errors[] = 'Please select a valid option for day';
                }
                break;

            case SystemSchedule::FREQUENCY_TYPE_WEEKDAYS:

            case SystemSchedule::FREQUENCY_TYPE_WEEKENDS;

            case SystemSchedule::FREQUENCY_TYPE_DAY:

                break;
            default:
                $errors[] = 'Frequency type not supported, please select' . join("/",
                        array_keys(SystemSchedule::FREQUENCY_TYPES));
                break;
        }
        if (empty($timezone) || !in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL))) {
            $errors[] = 'Please select a valid option for timezone';
        }
        if (empty($hour)) {
            $errors[] = 'Please select a valid option for hour';
        }
        if ($min === '' || $min === null) {
            $errors[] = 'Please select a valid option for min';
        }
        if (!in_array($download, array_keys(SystemJob::TEXT_DOWNLOAD))) {
            $errors[] = 'Please specify whether to download the file';
        }
        if ($download && !in_array($source, array_keys(SystemJob::TEXT_SOURCE))) {
            $errors[] = 'Please specify the source of the file to download';
        }
        if ($download && $pattern === '' && $date_pattern === '') {
            $errors[] = 'Please specify either the pattern or date pattern to match in file names';
        }
        if ($source === SystemJob::SOURCE_BOX && $box_folder === '') {
            $errors[] = 'Please input the box folder from where files would be fetched';
        }
        if (!in_array($isActive,
            [SystemSchedule::STATUS_ACTIVE, SystemSchedule::STATUS_INACTIVE,])
        ) {
            $errors[] = 'Please select a valid option for is_active';
        }

        if (config('dataloader.by_region')) {
            $supported_types = config(sprintf('dataloader.supported_type_region.%s', $region));
            $subtypes = config(sprintf('dataloader.%s.%s.subtype', $type, $region));
        } else {
            $supported_types = config(sprintf('dataloader.supported_type_region'));
            $subtypes = config(sprintf('dataloader.%s.subtype', $type));
        }

        if (!in_array($type, $supported_types)) {
            $errors[] = 'Type not supported, please select ' . join("/", $supported_types);
        }

        if ($subtypes && !in_array($subType, $subtypes)) {
            $errors[] = 'Subtype option must be selected';
        }

        return $errors;
    }

    /**
     * If its a daily, weekday or weekend job we don't need to store week
     *
     * @param int $day
     * @param string $frequency
     * @return null
     */
    private static function getDay($day, $frequency)
    {
        if (in_array($frequency, [
            SystemSchedule::FREQUENCY_TYPE_DAY,
            SystemSchedule::FREQUENCY_TYPE_WEEKENDS,
            SystemSchedule::FREQUENCY_TYPE_WEEKDAYS,
        ])) {
            return null;
        }

        return $day;
    }

    /**
     * If its a weekly, daily, weekday or weekend job we don't need to store week
     *
     * @param int $week
     * @param string $frequency
     * @return null
     */
    private static function getWeek($week, $frequency)
    {
        if (in_array($frequency, [
            SystemSchedule::FREQUENCY_TYPE_WEEK,
            SystemSchedule::FREQUENCY_TYPE_DAY,
            SystemSchedule::FREQUENCY_TYPE_WEEKDAYS,
            SystemSchedule::FREQUENCY_TYPE_WEEKENDS,
        ])) {
            return null;
        }

        return $week;
    }
}
