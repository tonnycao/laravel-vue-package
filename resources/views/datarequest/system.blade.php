<div class="modal fade" tabindex="-1" role="dialog" id="{{ isset($id) ? $id : 'schedule_request' }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ isset($title) ? $title : 'New' }} Schedule request</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="schedule-request" action="" method="POST">
                    <div class="form-group">
                        <label for="name" class="control-label">Name:</label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="Enter something that can describe the schedule">
                        <span class="name-error has-error hidden">Name is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="frequency_type" class="control-label">Frequency Type:</label>
                        <select class="form-control" name="frequency_type" id="frequency_type">
                            <option value="">Please select</option>
                            @foreach (\FDT\DataLoader\Models\SystemSchedule::FREQUENCY_TYPES as $key => $frequency_type)
                                <option value="{{ $key }}">{{ $frequency_type }}</option>
                            @endforeach
                        </select>
                        <span class="frequency_type-error has-error hidden">Frequency type is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="week" class="control-label">Week:</label>
                        <select class="form-control" name="week" id="week">
                            <option value="">Please select</option>
                            @for($i = 1; $i <= 14; $i++)
                                <option value="{{$i}}">{{ $i }}</option>
                            @endfor
                        </select>
                        <span class="week-error has-error hidden">Week is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="day" class="control-label">Day:</label>
                        <select class="form-control" name="day" id="day">
                            <option value="">Please select</option>
                            @for($i = 1; $i <= 7; $i++)
                                <option value="{{$i}}">{{ $i }}</option>
                            @endfor
                        </select>
                        <span class="day-error has-error hidden">Day is a required field</span>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="timezone" class="control-label">Timezone:</label>
                            <select class="form-control" name="timezone" id="timezone">
                                <option value="">Please select</option>
                                @foreach ($timezones as $k => $v)
                                    <option value="{{$k}}">{{$v}}</option>
                                @endforeach
                                <option value="UTC" selected>UTC</option>
                            </select>
                            <span class="timezone-error has-error hidden">Timezone is a required field</span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="hour" class="control-label">Hour:</label>
                            <select class="form-control" name="hour" id="hour">
                                <option value="">Please select</option>
                                @for($i = 0; $i <= 23; $i++)
                                    <option value="{{$i}}">{{ strlen($i) === 1 ? "0$i": $i }}</option>
                                @endfor
                            </select>
                            <span class="hour-error has-error hidden">Hour is a required field</span>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="min" class="control-label">Minute:</label>
                            <select class="form-control" name="min" id="min">
                                <option value="">Please select</option>
                                <option value="00" selected>00</option>
                                @for ($i = 0; $i <= 45; $i+= 15)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <span class="min-error has-error hidden">Minute is a required field</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="type" class="control-label">Type:</label>
                        <select class="form-control" name="type" id="type">
                            <option value="">Please select</option>
                            @foreach ($types as $k=>$v)
                                <option value="{{$v}}">{{$v}}</option>
                            @endforeach
                        </select>
                        <span class="type-error has-error hidden">Type is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="subtype" class="control-label">Subtype Option:</label>
                        <select class="form-control" name="subtype" id="subtype">
                            <option value="">Please select</option>
                        </select>
                        <span class="subtype-error has-error hidden">Subtype is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="download" class="control-label">Download:</label>
                        <select class="form-control" name="download" id="download">
                            <option value="">Please select</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <span class="download-error has-error hidden">Download is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="source" class="control-label">Source:</label>
                        <select class="form-control" name="source" id="source">
                            <option value="">Please select</option>
                            @foreach (\FDT\DataLoader\Models\SystemJob::TEXT_SOURCE as $key => $item)
                                <option value="{{ $key }}">{{ $item }}</option>
                            @endforeach
                        </select>
                        <span class="source-error has-error hidden">Source is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="pattern" class="control-label">Pattern:</label>
                        <input type="text" class="form-control" id="pattern" name="pattern"
                               placeholder="Enter the pattern to match in the file name">
                        <span class="name-error has-error hidden">Pattern is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="date_pattern" class="control-label">Date pattern:</label>
                        <input type="text" class="form-control" id="date_pattern" name="date_pattern"
                               placeholder="Enter the date pattern to match in the file name">
                        <span class="name-error has-error hidden">Date pattern is a required field</span>
                    </div>

                    <div class="form-group">
                        <label for="box_folder" class="control-label">Box folder:</label>
                        <input type="text" class="form-control" id="box_folder" name="box_folder"
                               placeholder="Enter the box folder where the files should be fetched from">
                    </div>

                    <div class="form-group">
                        <label for="box_file" class="control-label">Box file:</label>
                        <input type="text" class="form-control" id="box_folder" name="box_file"
                               placeholder="Enter the box file which must be loaded">
                    </div>

                    <div class="form-group">
                        <label for="is_active" class="control-label">Is Active:</label>
                        <select class="form-control" name="is_active" id="is_active">
                            <option value="">Please select</option>
                            <option value="{{ \FDT\DataLoader\Models\SystemSchedule::STATUS_ACTIVE }}" selected>
                                Active
                            </option>
                            <option value="{{ \FDT\DataLoader\Models\SystemSchedule::STATUS_INACTIVE }}">
                                Inactive
                            </option>
                        </select>
                        <span class="is_active-error has-error hidden">Is-active is a required field</span>
                    </div>

                    {{ csrf_field() }}
                </form>
            </div>
            <div class="modal-footer">
                <div class="level">
                    <div class="flex">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success"
                            id="{{ isset($submitId) ? $submitId : 'do_continue' }}">Submit
                    </button>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
