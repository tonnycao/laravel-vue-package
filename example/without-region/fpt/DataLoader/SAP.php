<?php

namespace FDT\DataLoader;

use App\Http\Controllers\ReceiptController;
use App\Repositories\FBL1N;
use App\Repositories\FBL5N;
use DB;
use FDT\DataLoader\Repositories\Shared;

class SAP extends Loader
{
    use Shared;

    const FBL5N = 'FBL5N';
    const FBL1N = 'FBL1N';

    protected $db = '';
    protected $tag = '';
    protected $path = '';
    protected $rt = 'SAP'; // type
    protected $type = null;
    private $table = null;

    /**
     * SAP constructor.
     *
     * @param int $job
     * @param string bool $db
     */
    public function __construct($job, $db = false)
    {
        if (!$db) {
            $db = env('DB_DATABASE');
        }
        $this->job = $job;
        $this->db = $db;
        $this->tag = $this->tag();
        $this->path = sprintf(config('dataloader.path'), $this->rt);

    }

    /**
     * @return false|string
     */
    public function tag()
    {
        return date('ymdhis');
    }

    /**
     * @return void
     */
    public function load()
    {
        $this->type = $this->job->subtype;
        $this->loadDataForType();
        $this->persist();
    }

    /**
     * @param bool $folder
     */
    private function loadDataForType($folder = false)
    {
        if (!$folder) {
            $folder = config('dataloader.report') . DIRECTORY_SEPARATOR . $this->job->id;
        }

        $history_path = storage_path('app/' . $this->path . DIRECTORY_SEPARATOR .
                config('dataloader.history')) . DIRECTORY_SEPARATOR;

        $this->format(['tag' => $this->tag]);
        $files = $this->_list_files();

        foreach ($files as $loading_file) {
            $file_name = $loading_file->name;
            $this->tag = $this->tag();
            //if the file name contains the pattern
            if (stripos(strtolower($file_name),
                    config(sprintf('dataloader.SAP.%s', $this->type))) === false
            ) {
                continue;
            }

            $value = storage_path('app/' . $this->path . DIRECTORY_SEPARATOR . $folder .
                DIRECTORY_SEPARATOR . $file_name);

            if (is_file($value)) {
                $file_info = ['File' => $file_name, 'Hash' => md5_file($value)];
                if ($file_info['Hash'] != $loading_file->hash) {
                    //hash not match ?
                    continue;
                }
                $this->format();
                $this->format($file_info);
                //actually load the data.
                try {
                    $logs = $this->loadFileType($value);
                    $this->afterLoading($value, $loading_file, $logs, $this->tag);
                    //loading data end
                    echo "FBL5N: File upload completed ($file_name) \n";
                    $this->format($logs);
                } catch (\Exception $ex) {
                    echo "An exception occurred while loading file to database of type {$this->type}\n\n\n";
                    echo $ex->getMessage();
                    echo $ex->getTraceAsString();
                }
            }
        }
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws \Exception
     */
    private function loadFileType($filePath)
    {
        switch ($this->type) {
            case self::FBL5N:
                $this->table = 'receipt_loading';
                return $this->loadFBL5N($filePath);
                break;
            case self::FBL1N:
                $this->table = 'fbl1n';
                return $this->loadFBL1N($filePath);
            default: throw new \Exception("Unknown type {$this->type}");
        }
    }

    /**
     * @param string $filePath
     *
     * @return array
     */
    private function loadFBL5N($filePath)
    {
        $content = self::logs();
        $content = $this->set_table($content, $this->db . '.' . $this->table);
        if (FBL5N::upload($filePath)) {
            $content = array_merge($content, FBL5N::$content);
            $confirmBalance = ReceiptController::confirmBalance();
            if ($confirmBalance['success'] === false) {
                $content['ok'] = false;
            }
        } else {
            echo "failed to open file $filePath \n";
            $content['ok'] = false;
        }

        return $content;
    }

    /**
     * @param string $filePath
     *
     * @return array
     */
    private function loadFBL1N($filePath)
    {
        $content = self::logs();
        $content = $this->set_table($content, $this->db . '.' . $this->table);

        if (FBL1N::upload($filePath)) {
            $content = array_merge($content, FBL1N::$num);
        } else {
            echo "failed to open file $filePath \n";
            $content['ok'] = false;
        }

        return $content;
    }
}
