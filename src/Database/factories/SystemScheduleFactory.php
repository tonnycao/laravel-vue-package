<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(FDT\DataLoader\Models\SystemSchedule::class, function () {
    return [
        'id' => '1',
        'name' => 'Daily Job',
        'created_by' => 1,
        'frequency_type' => 'daily',
        'week' => null,
        'day' => null,
        'time' => '14:00',
        'timezone' => 'Asia/Shanghai',
        'type' => 'FB03',
        'created_at' => '2019-08-13 14:00:00',
        'updated_at' => '2019-08-13 14:00:00',
        'is_active' => FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE,
    ];
});

$factory->define(FDT\DataLoader\Models\SystemSchedule::class, function () {
    return [
        'id' => '1',
        'name' => 'Weekly Job',
        'created_by' => 1,
        'frequency_type' => 'weekly',
        'week' => null,
        'day' => 2,
        'time' => '14:00',
        'timezone' => 'Asia/Shanghai',
        'type' => 'FB03',
        'created_at' => '2019-08-13 14:00:00',
        'updated_at' => '2019-08-13 14:00:00',
        'is_active' => FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE,
    ];
});

$factory->define(FDT\DataLoader\Models\SystemSchedule::class, function () {
    return [
        'id' => '1',
        'name' => 'Weekday Job',
        'created_by' => 1,
        'frequency_type' => 'weekdays',
        'week' => null,
        'day' => null,
        'time' => '14:00',
        'timezone' => 'Asia/Shanghai',
        'type' => 'FB03',
        'created_at' => '2019-08-13 14:00:00',
        'updated_at' => '2019-08-13 14:00:00',
        'is_active' => FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE,
    ];
});

$factory->define(FDT\DataLoader\Models\SystemSchedule::class, function () {
    return [
        'id' => '1',
        'name' => 'Weekend Job',
        'created_by' => 1,
        'frequency_type' => 'weekends',
        'week' => null,
        'day' => null,
        'time' => '14:00',
        'timezone' => 'Asia/Shanghai',
        'type' => 'FB03',
        'created_at' => '2019-08-13 14:00:00',
        'updated_at' => '2019-08-13 14:00:00',
        'is_active' => FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE,
    ];
});

$factory->define(FDT\DataLoader\Models\SystemSchedule::class, function () {
    return [
        'id' => '1',
        'name' => 'Quarterly Job',
        'created_by' => 1,
        'frequency_type' => 'quarterly',
        'week' => 1,
        'day' => 2,
        'time' => '14:00',
        'timezone' => 'Asia/Shanghai',
        'type' => 'FB03',
        'created_at' => '2019-08-13 14:00:00',
        'updated_at' => '2019-08-13 14:00:00',
        'is_active' => FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE,
    ];
});

$factory->define(FDT\DataLoader\Models\SystemSchedule::class, function () {
    return [
        'id' => '1',
        'name' => 'Periodic Job',
        'created_by' => 1,
        'frequency_type' => 'periodic',
        'week' => 1,
        'day' => 2,
        'time' => '14:00',
        'timezone' => 'Asia/Shanghai',
        'type' => 'FB03',
        'created_at' => '2019-08-13 14:00:00',
        'updated_at' => '2019-08-13 14:00:00',
        'is_active' => FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE,
    ];
});
