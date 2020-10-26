<?php

namespace FDT\DataLoader\Commands;

use Cache;
use DB;
use FDT\DataLoader\Models\SystemJob;
use FDT\DataLoader\Models\LoadingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DataSource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:datasource {job} {connection=mysql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'load data source for different type';

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
        //
        $job = $this->argument('job');
        $connection = $this->argument('connection');
        try {
            Config::set('database.default', $connection);
        } catch (\Exception $ex) {
            $this->_exit($ex->getMessage());
        }
        $loading_job = SystemJob::on($connection)->find($job);
        if (!$loading_job || $loading_job->status !== SystemJob::STATUS_APPROVED) {
            $this->_exit("No loading job found");
        }
        if (!Cache::add('loading.job.' . $loading_job->id, true, 1)) {
            $this->_exit("Job is already running");
        }

        $type = strtoupper($loading_job->type);
        $region = strtoupper($loading_job->region);
        if (!in_array($type, config('dataloader.supported_type_region'))) {
            $this->_exit("The type $type is not supported");
        }
        if (config('dataloader.by_region')) {
            $supported_region = config('dataloader.' . $type);
            if ($supported_region) {
                $supported_region = array_keys($supported_region);
            } else {
                $supported_region = [];
            }
            if (!in_array($region, $supported_region)) {
                $this->_exit("The region $region is not supported");
            }
            $file_class = sprintf("FDT\\DataLoader\\File\\%s\\%s\\%s", ucfirst(config('dataloader.source')),
                $type, $region);
            $loading_class = sprintf("FDT\\DataLoader\\%s\\%s", $type, $region);
            $path = sprintf(config('dataloader.path'), $type, $region);
        } else {
            $file_class = sprintf("FDT\\DataLoader\\File\\%s\\%s", ucfirst(config('dataloader.source')),
                $type);
            $loading_class = sprintf("FDT\\DataLoader\\%s", $type);
            $path = sprintf(config('dataloader.path'), $type);
        }

        if (class_exists($file_class) && class_exists($loading_class)) {
            $loading_job->status = SystemJob::STATUS_LOADING;
            $loading_job->save();
            DB::beginTransaction();
            try {

                //download files if loading job download bit is set to 1
                if ($loading_job->download && $loading_job->source === SystemJob::SOURCE_MAIL) {
                    /** @var \FDT\DataLoader\Repositories\File\Mail\MailDownloader $downloader */
                    $downloader = app()->makeWith($file_class, ['job' => $loading_job]);
                    $path = $downloader->download();
                }
                //collect the downloaded files
                /** @var \FDT\DataLoader\Repositories\File\Collector $collector */
                $collector = app()->makeWith("FDT\\DataLoader\\Repositories\\File\\Collector",
                    ['job' => $loading_job, 'connection' => $connection]);
                $collector->collect($path, $loading_job->source);
                //load the files
                $loader = app()->makeWith($loading_class, ['job' => $loading_job]);
                $loader->load();
                DB::commit();
                $loading_job->status = SystemJob::STATUS_FINISHED;
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                $loading_job->status = SystemJob::STATUS_FAILED;
            }
            $loading_job->save();

            LoadingLog::create([
                    'job' => $loading_job->id,
                    'user' => 0,
                    'desc' => 'Data loading request loaded',
                ]
            );
            try {
                $loading_job->message(SystemJob::MSG_LOADED);
            } catch (\Exception $ex) {
                \Log::info($ex->getMessage());
            }
        } else {
            $this->info("Unable to find class ${file_class} or ${loading_class}");
        }
    }

    /**
     * @param string $msg
     */
    private function _exit($msg)
    {
        $this->error($msg);
        exit;
    }
}
