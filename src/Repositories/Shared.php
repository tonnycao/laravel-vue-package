<?php

namespace FDT\DataLoader\Repositories;

use FDT\DataLoader\Models\LoadingFile;
use FDT\DataLoader\Models\SystemJob;

trait Shared
{
    /**
     * @return mixed
     */
    protected function _list_files()
    {
        $files = LoadingFile::where('job', $this->job->id)->get()->all();
        return $files;
    }

    /**
     * @param array $arr
     * @param string $table
     *
     * @return array
     */
    protected function set_table(array $arr, $table)
    {
        $arr['table'] = $table;

        return $arr;
    }

    /**
     * @param array $arr
     * @param string $source
     *
     * @return array
     */
    protected function set_source(array $arr, $source)
    {
        $arr['source'] = $source;

        return $arr;
    }

    /**
     * @param $file
     * @param $loading_file
     * @param $result
     * @param $tag
     */
    public function afterLoading($file, $loading_file, $result, $tag)
    {
        $loading_file->info = json_encode($result);
        $loading_file->tag = $tag;
        $loading_file->table = array_get($result, 'table', '');
        $loading_file->source = array_get($result, 'source', SystemJob::SOURCE_MAIL);
        $loading_file->save();
        $history_path = storage_path('app/' . $this->path . '/' . config('dataloader.history')) . '/' . $loading_file->job;
        if (!file_exists($history_path)) {
            mkdir($history_path);
        }
        rename($file, $history_path . '/' . $loading_file->name);
    }
}
