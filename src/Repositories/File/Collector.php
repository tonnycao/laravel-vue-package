<?php

namespace FDT\DataLoader\Repositories\File;

use FDT\BoxModule\Repositories\BoxClient;
use FDT\DataLoader\Models\LoadingFile;
use FDT\DataLoader\Models\SystemJob;
use Illuminate\Support\Facades\Config;
use Storage;

class Collector
{
    const BOX_API_ITEM_LIMIT = 1000;

    public $job = null;
    public $connection = null;

    /**
     * Collector constructor.
     * @param SystemJob $job
     * @param string $connection Database connection to use
     */
    public function __construct($job, $connection)
    {
        $this->job = $job;
        $this->connection = $connection;
    }

    /**
     * @param string $path
     * @param string $source
     *
     * @throws \Exception
     */
    public function collect($path, $source)
    {
        Config::set('database.default', $this->connection);
        $files = [];
        if (empty($this->job->download)) {
            return;
        }
        switch ($source) {
            case SystemJob::SOURCE_MAIL:
                $files = Storage::files($path);
                break;

            case SystemJob::SOURCE_SFTP:
                $allFiles = Storage::disk('sftp')->allFiles();
                $loadedFiles = LoadingFile::whereIn('job',
                    SystemJob::where('subtype', '=', $this->job->subtype)
                        ->whereDate('scheduled_at', \Carbon\Carbon::today())
                        ->get()
                        ->pluck('id')
                )->get()->pluck('name')->toArray();

                $loadedFiles = (array) $loadedFiles;

                // default load all files
                $todayFiles = array_filter($allFiles, function ($value) {
                    if (stripos($value, '.gz') === false) {
                        return $value;
                    }
                });
                if (!empty($this->job->pattern) && !empty($this->job->date_pattern)) {
                    // only check for files matching pattern
                    $date = \Carbon\Carbon::now($this->job->config->timezone)->format($this->job->date_pattern);
                    $todayFiles = array_filter($allFiles, function ($value) use ($date) {
                        if (stripos($value, $this->job->pattern) !== false && stripos($value, $date) !== false) {
                            return $value;
                        }
                    });
                } else if (!empty($this->job->date_pattern) && empty($this->job->pattern)) {
                    // only check for files matching date pattern
                    $date = \Carbon\Carbon::now($this->job->config->timezone)->format($this->job->date_pattern);
                    $todayFiles = array_filter($allFiles, function ($value) use ($date) {
                        if (stripos($value, $date) !== false) {
                            return $value;
                        }
                    });
                } else if (!empty($this->job->pattern) && empty($this->job->date_pattern)) {
                    // only check for files matching pattern
                    $todayFiles = array_filter($allFiles, function ($value) {
                        if (stripos($value, $this->job->pattern) !== false) {
                            return $value;
                        }
                    });
                }
                $newFiles = array_diff($todayFiles, $loadedFiles);

                $directory = sprintf('%s/%s/%s', sprintf(config('dataloader.path'), $this->job->type),
                    'report', $this->job->id);

                foreach ($newFiles as $file) {
                    $local_file = sprintf('%s/%s', $directory, $file);
                    array_push($files, $local_file);
                    Storage::disk('local')->put($local_file, Storage::disk('sftp')->get($file));
                }

                break;

            case SystemJob::SOURCE_BOX:
                try {
                    /** @var BoxClient $box_client */
                    $box_client = app(BoxClient::class);
                    $box_file = $this->_getBoxFile();
                    $box_folder = $this->_getBoxFolder();
                    $box_files = [];

                    if (empty($box_file) && empty($box_folder)) {
                        \Log::info(sprintf('Did not specify either box file: %s or box folder: %s', $box_file, $box_folder));
                        throw new \Exception('You must only specify either a box file or box folder. None specified.');
                    }

                    // NOTE: if its just one file, we allow to load it again
                    if (!empty($box_file)) {
                        $local_file = $box_client->saveFileToStorage($box_client->getToken(), $box_file,
                            $this->_getDirectory());
                        $box_files[$box_file] = pathinfo($local_file, PATHINFO_BASENAME);
                        array_push($files, $local_file);
                    }

                    // NOTE: if its a box folder, we will skip to load all the files that were previously loaded i.e.
                    //       ONLY NEW FILES with different names will be downloaded and loaded
                    if (!empty($box_folder) && empty($box_file)) {
                        $loadedFiles = LoadingFile::whereIn('job',
                            SystemJob::where('subtype', '=', $this->job->subtype)
                                ->get()
                                ->pluck('id')
                        )->get()->pluck('name')->toArray();

                        $loadedFiles = (array)$loadedFiles;

                        // get box folder items
                        $box_folder_items = $this->_getBoxFolderItems($box_client, $box_folder);
                        if (isset($box_folder_items['entries'])) {
                            foreach ($box_folder_items['entries'] as $item) {
                                $box_files[$item['id']] = $item['name'];
                            }
                        }

                        $newFiles = array_diff($box_files, $loadedFiles);
                        $files = array_merge($files, $this->_saveFilesFromBox($box_client, $newFiles));
                    }
                } catch (\Exception $ex) {
                    \Log::info(
                        sprintf('An error occurred while downloading files from Box: %s on line %d',
                            $ex->getMessage(), $ex->getLine())
                    );
                    throw $ex;
                }
                break;

            default:
                throw new \Exception('Unknown source');
        }
        sort($files);
        foreach ($files as $value) {
            $exp_file_path = explode("/", $value);
            $file_name = $exp_file_path[sizeof($exp_file_path) - 1];
            $value = storage_path('app/' . $value);
            $this->_record($file_name, $value);
        }
    }

    /**
     * @param $name
     * @param $file
     */
    private function _record($name, $file)
    {
        $loadingFile = new LoadingFile();
        $loadingFile->job = $this->job->id;
        $loadingFile->name = $name;
        $loadingFile->hash = md5_file($file);
        $loadingFile->info = 'File downloaded';
        $loadingFile->source = $this->job->source;
        $loadingFile->save();
    }

    /**
     * @return string
     */
    private function _getDirectory()
    {
        return sprintf('%s/%s/%s', sprintf(config('dataloader.path'), $this->job->type),
            'report', $this->job->id);
    }

    /**
     * @param BoxClient $box_client
     * @param int $box_folder
     *
     * @return array
     * @throws \Exception
     */
    private function _getBoxFolderItems(BoxClient $box_client, $box_folder)
    {
        if (!empty($this->job->pattern)) {
            $box_folder_items = $box_client->getAllFilesByName($box_client->getToken(),
                $this->job->pattern, self::BOX_API_ITEM_LIMIT, $box_folder);
        } else if (!empty($this->job->date_pattern)) {
            $date = \Carbon\Carbon::now($this->job->config->timezone)->format($this->job->date_pattern);
            $box_folder_items = $box_client->getAllFilesByName($box_client->getToken(),
                $date, self::BOX_API_ITEM_LIMIT, $box_folder);
        } else {
            $box_folder_items = $box_client->getAllFolderItems($box_client->getToken(), $box_folder,
                self::BOX_API_ITEM_LIMIT);
        }

        return $box_folder_items;
    }

    /**
     * @return null|string
     */
    private function _getBoxFolder()
    {
        $dir = null;
        $regExp = '/[0-9].*/mi';
        if (!empty($this->job->box_folder)) {
            preg_match_all($regExp, $this->job->box_folder, $matches, PREG_SET_ORDER, 0);
            $dir = $matches[0][0];
        }

        return $dir;
    }

    /**
     * @return null|string
     */
    private function _getBoxFile()
    {
        $file = null;
        $regExp = '/[0-9].*/mi';
        if (!empty($this->job->box_file)) {
            preg_match_all($regExp, $this->job->box_file, $matches, PREG_SET_ORDER, 0);
            $file = $matches[0][0];
        }

        return $file;
    }

    /**
     * @param BoxClient $box_client
     * @param array $newFiles
     *
     * @return array
     * @throws \Exception
     */
    private function _saveFilesFromBox(BoxClient $box_client, array $newFiles = [])
    {
        $files = [];
        foreach (array_keys($newFiles) as $fileId) {
            $local_file = $box_client->saveFileToStorage($box_client->getToken(), $fileId,
                $this->_getDirectory());
            $date = \Carbon\Carbon::now($this->job->config->timezone)->format($this->job->date_pattern);
            if (!empty($this->job->pattern)  &&  !empty($this->job->date_pattern) &&
                stripos($local_file, $this->job->pattern) !== false &&
                stripos($local_file, $date) !== false
            ) {
                array_push($files, $local_file);
            } else if (!empty($this->job->pattern) &&
                stripos($local_file, $this->job->pattern) !== false
            ) {
                array_push($files, $local_file);
            } else if (!empty($this->job->date_pattern) && stripos($local_file, $date) !== false) {
                array_push($files, $local_file);
            } else {
                // cleanup the downloaded files
                \Storage::delete($local_file);
            }
        }

        return $files;
    }
}
