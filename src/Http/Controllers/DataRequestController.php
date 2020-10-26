<?php

namespace FDT\DataLoader\Http\Controllers;

use FDT\DataLoader\Http\Requests\ConfirmRequest;
use FDT\DataLoader\Models\LoadingException;
use FDT\DataLoader\Models\LoadingFile;
use FDT\DataLoader\Models\SystemJob;
use FDT\DataLoader\Models\LoadingLog;
use FDT\DataLoader\Models\SystemSchedule;
use FDT\DataLoader\Repositories;

class DataRequestController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $region = self::getRegion();

        if (config('dataloader.by_region')) {
            $supported_types = config(sprintf('dataloader.supported_type_region.%s', $region));
            $requests = SystemJob::with('owner')->where('region', $region)->orderBy('id', 'DESC')->get();
        } else {
            $supported_types = config('dataloader.supported_type_region');
            $requests = SystemJob::with('owner')->orderBy('id', 'DESC')->get();
        }
        $supported_subtype = [];
        foreach ($supported_types as $type) {
            if (config('dataloader.by_region')) {
                $subtype = config(sprintf('dataloader.%s.%s.subtype', $type, $region));
            } else {
                $subtype = config(sprintf('dataloader.%s.subtype', $type));
            }
            $subtype = $subtype ?? [];
            $supported_subtype[$type] = $subtype;
        }


        $requests->transform(function ($item) {
            $item->text = SystemJob::TEXT[$item->status];
            return $item;
        });
        $schedules = SystemSchedule::with('createdBy', 'updatedBy')
            ->where('deleted_by', '=', null)->get();
        $schedules->transform(function ($item) {
            $item->text = SystemSchedule::STATUS_TEXT[$item->is_active];
            $item->time = strlen($item->time) === 4 ? "0$item->time" : $item->time;

            return $item;
        });

        $viewdata = [
            'requests' => $requests->all(),
            'types' => $supported_types,
            'subtypes' => $supported_subtype,
            'timezones' => Repositories\Timezone::generate_list(),
            'schedules' => $schedules->all(),
        ];

        return view('dataloader.datarequest.index', $viewdata);
    }

    /**
     * @param $type
     *
     * @return array|\Illuminate\Config\Repository|mixed
     */
    public function subtype($type)
    {
        $region = self::getRegion();
        if (config('dataloader.by_region')) {
            $subtype = config(sprintf('dataloader.%s.%s.subtype', $type, $region));
        } else {
            $subtype = config(sprintf('dataloader.%s.subtype', $type));
        }

        return $subtype ? $subtype : [];
    }

    /**
     * @return array
     */
    public function store()
    {
        $region = self::getRegion();
        $name = trim(request('name'));
        $type = trim(request('type'));
        $subtype = trim(request('subtype'));
        $download = trim(request('download'));
        $source = trim(request('source'));
        $pattern = trim(request('pattern'));
        $date_pattern = trim(request('date_pattern'));
        $box_folder = trim(request('box_folder'));
        $box_file = trim(request('box_file'));
        $errors = [];
        if (strlen($name) < 3) {
            $errors[] = 'Please enter a name for the request';
        }
        if (config('dataloader.by_region')) {
            $supported_types = config(sprintf('dataloader.supported_type_region.%s', $region));
            $subtypes = config(sprintf('dataloader.%s.%s.subtype', $type, $region));
        } else {
            $supported_types = config('dataloader.supported_type_region');
            $subtypes = config(sprintf('dataloader.%s.subtype', $type));
        }
        if (!in_array($type, $supported_types)) {
            $errors[] = 'Type not supported, please select ' . join("/", $supported_types);
        }
        if ($source === SystemJob::SOURCE_BOX && $box_folder === '' && $box_file === '') {
            $errors[] = 'Please input the box folder from where files would be fetched or the box file';
        }
        if ($subtypes && !in_array($subtype, $subtypes)) {
            $errors[] = 'Subtype option must be selected';
        }

        if (!in_array($download, array_keys(SystemJob::TEXT_DOWNLOAD))) {
            $errors[] = 'Please specify whether to download the file from email.';
        }

        $ok = false;
        if (!$errors) {
            $job = new SystemJob();
            $job->user = auth()->id();
            $job->name = $name;
            $job->type = $type;
            $job->subtype = $subtype;
            $job->summary = '';
            $job->trace = '';
            $job->region = config('dataloader.by_region') ? $region : '';
            $job->download = $download;
            $job->status = SystemJob::STATUS_INITIALIZED;
            $job->scheduled_at = now(config('app.timezone'));
            $job->source = $source;
            $job->pattern = $pattern;
            $job->date_pattern = $date_pattern;
            $job->box_folder = $box_folder;
            $job->box_file = $box_file;
            $job->save();
            LoadingLog::create([
                    'job' => $job->id,
                    'user' => auth()->id(),
                    'desc' => 'Data loading request submitted'
                ]
            );
            $ok = true;
            //set the data request created msg.
            $job->message(SystemJob::MSG_CREATED);
        }
        return ['ok' => $ok, 'errors' => $errors,];
    }

    /**
     * @param SystemJob $job
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(SystemJob $job)
    {
        $files = LoadingFile::where('job', $job->id)->get()->transform(function ($item) {
            $item->text = LoadingFile::text($item->status);
            return $item;
        })->all();


        $logs = LoadingLog::where('job', $job->id)->get()->all();
        $exceptions = LoadingException::where('job', $job->id)->get()->transform(function ($item) {
            $item->files = explode(";", $item->files);
            $item->text = LoadingException::getText($item->status);
            return $item;
        })->all();

        $fl = [];
        if ($exceptions) {
            foreach ($files as $f) {
                $fl[$f->id] = $f;
            }
        }

        $viewdata = [
            'job' => $job,
            'files' => $files,
            'logs' => $logs,
            'exceptions' => $exceptions,
            'flist' => $fl,
        ];

        return view('dataloader.datarequest.show', $viewdata);
    }

    /**
     * @param ConfirmRequest $request
     * @param SystemJob $job
     *
     * @return array
     */
    public function update(ConfirmRequest $request, SystemJob $job)
    {
        if ($job->status == SystemJob::STATUS_INITIALIZED) {
            $action = trim($request->action);
            if ($action === 'approve') {
                $job->status = SystemJob::STATUS_APPROVED;
                $desc = 'Approve the loading request';
                $msg = SystemJob::MSG_APPROVED;
            } else {
                $job->status = SystemJob::STATUS_REJECTED;
                $desc = sprintf('Reject the loading request.(comment: %s) ', trim($request->comment));
                $msg = SystemJob::MSG_REJECTED;
            }
            $job->save();
            LoadingLog::create([
                'job' => $job->id,
                'user' => auth()->id(),
                'desc' => $desc,
            ]);
            $job->message($msg);
        }

        return ['refresh' => 1,];
    }

    /**
     * @param LoadingFile $file
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function download(LoadingFile $file)
    {
        try {
            if ($this->authorize('dataloader-admin')) {
                $job = SystemJob::find($file->job);

                $file_path = storage_path(sprintf('app/datasource/%s/history/%s/%s', $job->type, $job->id,
                    $file->name));
                $mime = mime_content_type($file_path);

                return response()->download($file_path, $file->name, ['Content-Type' => $mime]);
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with(['error' => 'Only admins have access to download the files']);
        }
    }
}
