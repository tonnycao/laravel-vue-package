@extends('layouts.app')
@section('content')
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div class="level">
                <ul class="nav nav-tabs col-10">
                    <li class="nav-item"><a data-toggle="tab" class="nav-link active" href="#requests">Data Loading Requests</a></li>
                    <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#scheduled">Scheduled Jobs</a></li>
                </ul>
                <div class="col-2">
                    @can('dataloader-admin')
                        <a class="btn btn-success btn-sm" data-toggle="modal" data-target="#new_request"> Make a
                            Request</a>
                        <a class="btn btn-primary btn-sm float-right mb-auto" data-toggle="modal" data-target="#schedule_request"> Schedule
                            Request</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div id="requests" class="tab-pane fade show active">
                    <table class="table table-strip table-hover" id="requests-table">
                    </table>
                </div>
                <div id="scheduled" class="tab-pane fade">
                    <table class="table table-strip table-hover">
                        <tr>
                            <th>Config ID</th>
                            <th>Name</th>
                            <th>Frequency</th>
                            <th>Type</th>
                            <th>Subtype</th>
                            <th>Week</th>
                            <th>Day</th>
                            <th>Timezone</th>
                            <th>Time</th>
                            <th>Download</th>
                            <th>Source</th>
                            <th>Box Folder</th>
                            <th>Pattern</th>
                            <th>Date Pattern</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Modified By</th>
                            <th>Modified At</th>
                            <th></th>
                        </tr>
                        @foreach ($schedules as $k=>$v)
                            <tr>
                                <td> {{ $v->id }}</td>
                                <td> {{ $v->name }}</td>
                                <td> {{ ucfirst($v->frequency_type) }}</td>
                                <td> {{ $v->type}}</td>
                                <td> {{ $v->subtype}}</td>
                                <td> {{ $v->week }}</td>
                                <td> {{ $v->day }}</td>
                                <td> {{ $v->timezone }}</td>
                                <td> {{ $v->time }}</td>
                                <td> {{ \FDT\DataLoader\Models\SystemJob::TEXT_DOWNLOAD[$v->download] }}</td>
                                <td> {{ \FDT\DataLoader\Models\SystemJob::TEXT_SOURCE[$v->source] }}</td>
                                <td> {{ $v->box_folder }} </td>
                                <td> {{ $v->pattern}}</td>
                                <td> {{ $v->date_pattern}}</td>
                                <td> {{ $v->text}}</td>
                                <td> {{ $v->createdBy->name }}</td>
                                <td> {{ display_datetime($v->created_at) }}</td>
                                <td> {{ $v->updatedBy && $v->updatedBy->name ? $v->updatedBy->name : '' }}</td>
                                <td> {{ display_datetime($v->updated_at) }}</td>
                                <td>
                                    <a class="btn btn-info btn-sm" data-toggle="modal"
                                       data-target="#edit_system_request" data-edit="{{json_encode($v)}}"
                                       onclick="actionEdit(this)">
                                        Edit</a>
                                    @if ($v->status == 0)
                                        @can ('file-request')
                                            <a data-action="reject"
                                               data-msg="Are you sure to remove this schedule?"
                                               class="btn btn-danger btn-sm btn-confirm-box"
                                               href="/loading/schedule/disable/{{$v->id}}" target="_blank">Delete</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include ('dataloader.datarequest.confirmbox')
    @include ('dataloader.datarequest.create')
    @include ('dataloader.datarequest.system')
    @include ('dataloader.datarequest.modal')
    @include ('dataloader.datarequest.system-edit')
@endsection

@section('css')
    <style>
        #schedule-request .modal-body .hidden, #edit_system_request .modal-body .hidden {
            display: none;
        }
        #schedule-request .modal-body .has-error, #edit_system_request .modal-body .has-error {
            color: red;
        }
        .alert-notification {
            position: fixed;
            right: 20px;
            bottom: 20px;
            min-width: 200px;
            display: none;
            z-index: 10000;
        }
    </style>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/dataloader.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/schedule_request.js') }}"></script>
@endsection
