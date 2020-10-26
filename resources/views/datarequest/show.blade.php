@extends('layouts.app')

@section('content')
    <div class="card panel-default" style="margin-bottom:20px">
        <div class="card-heading">
            <div class="level">
                <div class="flex">
                    <h5 class="panel-title"> Data Loading Request Detail</h5>
                </div>
                <div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                @if (config('dataloader.by_region'))
                    Region: <b> {{ $job->region }} </b> /
                @endif
                Type: <b> {{ $job->type }} </b> / Subtype Option:
                <b> {{ $job->subtype }} @if ($job->name) ( {{ $job->name }} ) @endif </b>
                Requested At: <b>{{ $job->created_at }}</b>
                @can('file-request')
                    <a class="btn-confirm-box btn btn-sm btn-danger hide pull-right" id="submit_inactive"
                       data-action="reject"
                       data-msg="Are you sure to mark the selected files as inactive?"
                       data-post_hook="before_submit" href="/loading/request/{{$job->id}}/disable" target="_bla
nk">Mark as Inactive...</a>
                @endcan
            </div>
            <div style="background-color: white">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a href="#files" aria-controls="files" class="nav-link active" data-toggle="tab">Files </a>
                    </li>
                    <li class="nav-item">
                        <a href="#summary" aria-controls="summary" class="nav-link" data-toggle="tab">Summary </a>
                    </li>
                    <li class="nav-item">
                        <a href="#trace" aria-controls="trace" class="nav-link" data-toggle="tab">Logs </a>
                    </li>
                    @if ($exceptions)
                        <li class="nav-item">
                            <a href="#exception" id="exceptions" aria-controls="exception" class="nav-link" data-toggle="tab"> Exceptions </a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="files">
                        @if ($files)
                            <table class="table table-bordered" style="background-color:white;margin-top:20px">
                                <tr>
                                    <th>Source</th>
                                    <th>File Name</th>
                                    <th>Hash</th>
                                    <th>Tag</th>
                                    <th>Status</th>
                                    <th><input type="checkbox" id="checkAll"/></th>
                                </tr>

                                @foreach ($files as $file)
                                    <tr>
                                        <td>{{ $job->source }}</td>
                                        <td>
                                            @if(file_exists(storage_path(sprintf('app/datasource/%s/history/%s/%s', $job->type, $job->id, $file->name))))
                                                <a href="{{"/loading/request/file/" . $file->id}}">{{ $file->name }}</a>
                                            @else
                                                {{ $file->name }}
                                            @endif
                                        </td>
                                        <td>{{ $file->hash }}</td>
                                        <td>{{ $file->tag }}</td>
                                        <td>{{ $file->text }}</td>
                                        <td>
                                            @if (!$file->status && $file->table)
                                                <input type="checkbox" class="file" name="file[]"
                                                       value="{{$file->id}}"/>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                            </table>
                        @else
                            <div class="alert alert-warning" style="margin-top:20px">
                                @if ($job->status > 1)
                                    No Files found.
                                @else

                                @endif
                            </div>
                        @endif
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="summary">
                        <div style="margin-top:20px;">
                            <textarea readonly style="width:98%;height:300px;border:none">{{ $job->summary }}</textarea>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="trace">
                        <div style="margin-top:20px">
                            @foreach ($logs as $item)
                                <p>{{ display_datetime($item->created_at) }} &gt; {{ $item ->desc }} by
                                    <strong>{{ $item->owner->name }}</strong></p>
                            @endforeach
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade in" id="exception">
                        <div style="margin-top:20px">
                            @if ($exceptions)
                                <table class="table table-bordered" style="background-color:white;margin-top:20px">
                                    <tr>
                                        <th>Files</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                    @foreach ($exceptions as $item)
                                        <tr>
                                            <td> @foreach ($item->files as $file)
                                                    {{$flist[$file]->name}} (<b>{{ $flist[$file]->tag }}</b>) <br/>
                                                @endforeach
                                            </td>
                                            <td>{{$item->reason}}</td>
                                            <td>{{$item->text}}</td>
                                            <td>
                                                @if ($item->status == 0)
                                                    @can ('file-request')
                                                        <a class="btn-confirm-box btn btn-sm btn-success"
                                                           data-action="approve"
                                                           data-msg="This is action will mark all records loaded from selected files inactive. Are you sure to approve the request?"
                                                           href="/loading/request/{{$item->id}}/approve"
                                                           target="_blank">Approve</a>
                                                    @endcan
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <div class="alert alert-success">No Exceptions.</div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script type="text/javascript">
        function before_submit() {
            let has_checked = $(".file:checked").length;
            if (has_checked.length < 1) {
                alert("You must select some files to continue");
            } else {
                let row_selected = [];
                $(".file:checked").each(function (i, item) {
                    row_selected.push($(item).val());
                });
                $("#subtype").val(row_selected.join(";"));
            }
            return has_checked > 0;
        }

        $(function () {
            let show_submit = function () {
                let has_checked = $(".file:checked").length;
                if (has_checked) {
                    $("#submit_inactive").removeClass('hide').show();
                } else {
                    $("#submit_inactive").hide();
                }
            };

            $("#checkAll").click(function () {
                if ($(this).is(":checked")) {
                    $(".file").prop("checked", true);
                } else {
                    $(".file").prop("checked", false);
                }
                show_submit();
            });

            $(".file").change(function () {
                show_submit();
            })
            if (location.hash === '#exception') {
                $("#exceptions").click()
            }
        })
    </script>
@endsection
