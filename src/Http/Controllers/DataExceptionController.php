<?php

namespace FDT\DataLoader\Http\Controllers;

use Cache;
use DB;
use FDT\DataLoader\Http\Requests\ConfirmRequest;
use FDT\DataLoader\Models\LoadingException;
use FDT\DataLoader\Models\LoadingFile;
use FDT\DataLoader\Models\LoadingLog;
use FDT\DataLoader\Models\SystemJob;

class DataExceptionController extends Controller
{
    //
    public function store(ConfirmRequest $request, SystemJob $job)
    {
        $subtype = trim($request->subtype);
        $files = explode(";", $subtype);
        $comment = trim($request->comment);
        if ($files) {
            DB::beginTransaction();
            $updated = LoadingFile::where('job', $job->id)->where('status', LoadingFile::STATUS_NORMAL)
                ->whereIn('id', $files)->update(['status' => LoadingFile::STATUS_INACTIVATING]);
            if ($updated) {
                $loading_exception = new LoadingException();
                $loading_exception->job = $job->id;
                $loading_exception->user = auth()->id();
                $loading_exception->files = $subtype;
                $loading_exception->reason = $comment;
                $loading_exception->save();
                LoadingLog::create([
                    'job' => $job->id,
                    'user' => auth()->id(),
                    'desc' => 'Raise disable loaded file request',
                ]);
                DB::commit();
                $job->message(SystemJob::MSG_EXCEPTION);
                $msg = 'Request submit ok';
            } else {
                $msg = 'No Request submit';
                DB::rollBack();
            }
        } else {
            $msg = 'No File selected';
        }
        session()->flash('msg', $msg);
        return ['refresh' => 1];
    }

    /**
     * @param LoadingException $e
     *
     * @return array
     */
    public function update(LoadingException $e)
    {
        $job = $e->job;
        $job = SystemJob::find($job);
        if ($e->status != LoadingException::STATUS_NORMAL) {
            abort(403);
        }

        if ($job->region != session('region')) {
            abort(403);
        }
        $ex_key = 'loading_exceptions_' . $e->id;
        if (Cache::add($ex_key, true, 10)) {
            $file = explode(";", $e->files);
            if ($file) {
                LoadingLog::create([
                    'job' => $job->id,
                    'user' => auth()->id(),
                    'desc' => 'Approve disable file',
                ]);
                $to_remove = LoadingFile::where('job', $job->id)->whereIn('id', $file)->get()->all();
                foreach ($to_remove as $r) {
                    if ($r->status == LoadingFile::STATUS_INACTIVATING && $r->table) {
                        $record = DB::table($r->table)->where('tag', $r->tag)->update(['active' => 0]);
                        $r->status = LoadingFile::STATUS_INACTIVATED;
                        $r->save();
                        LoadingLog::create([
                            'job' => $job->id,
                            'user' => auth()->id(),
                            'desc' => sprintf('Set record to inactive file:%s,tag:%s, %s affected',
                                $r->name, $r->tag, $record),
                        ]);
                    }
                }
                $e->status = LoadingException::STATUS_APPROVED;
                $e->save();
                $job->message(SystemJob::MSG_EXCEPTION_APPROVED, $e);
            }
            Cache::forget($ex_key);
        }
        return ['refresh' => 1];
    }
}
