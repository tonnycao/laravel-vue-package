<?php

namespace FDT\DataLoader\Commands;

use FDT\DataLoader\Models\SystemJob;
use FDT\DataLoader\Models\SystemSchedule;
use Artisan;
use Cache;
use Illuminate\Console\Command;

class ApprovedJob extends Command
{
    const TIME_DIFF_DAYS = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approved:job {connection=mysql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the approved loading job every 5 minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $timezone = new \DateTimeZone('UTC');
        if (config('app.timezone')) {
            $timezone = new \DateTimeZone(config('app.timezone'));
        }
        $currentTime = new \DateTime('now', $timezone);
        $connection = $this->argument('connection');
        \Config::set('database.default', $connection);

        // if active system schedules exist, we must create approved jobs
        $this->scheduleApprovedJob($connection);
        $jobs = SystemJob::on($connection)->where('status', SystemJob::STATUS_APPROVED)->where('scheduled_at', '<=',
            $currentTime)->get()->all();
        $loading_key = 'running_ort_command_%s';
        foreach ($jobs as $job) {
            if (!Cache::add(sprintf($loading_key, $job->id), true, 2000000)) {
                continue;
            }
            Artisan::queue('load:datasource', [
                'job' => $job->id,
                'connection' => $connection,
            ]);
        }
    }

    /**
     * @param string $connection
     */
    private function scheduleApprovedJob($connection)
    {
        $systemSchedule = SystemSchedule::on($connection)
            ->where('is_active', SystemSchedule::STATUS_ACTIVE)->get()->all();

        $timezone = new \DateTimeZone('UTC');
        if (config('app.timezone')) {
            $timezone = new \DateTimeZone(config('app.timezone'));
        }
        $currentTime = new \DateTime('now', $timezone);

        /**
         * @var SystemSchedule $schedule
         */
        foreach ($systemSchedule as $schedule) {
            try {
                $time = $schedule->checkSchedule($currentTime->format('Y-m-d H:i:s'));
                $scheduledAt = new \DateTime($time);
                $difference = $currentTime->diff($scheduledAt);
                if ($difference->days < self::TIME_DIFF_DAYS) {
                    $schedule->createApprovedJob($time, $schedule->region, $connection);
                } else {
                    echo sprintf("difference between current time: %s and schedule time: %s is greater than one day i.e. %s days \n",
                        $currentTime->format('Y-m-d H:i:s'), $time, $difference->days);
                }
            } catch (\Exception $ex) {
                \Log::info(sprintf('An exception occurred when scheduling an approved job based on system schedule: %s',
                    $ex->getMessage()));
            }
        }
    }
}
