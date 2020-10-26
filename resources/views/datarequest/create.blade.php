<div class="modal fade" tabindex="-1" role="dialog" id="new_request">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">New request</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="new-request" action="" method="POST">
                    <div class="form-group">
                        <label for="name" class="control-label">Name:</label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="Enter something that can describe the requests">
                    </div>

                    <div class="form-group">
                        <label for="type" class="control-label">Type:</label>
                        <select class="form-control" name="type" id="type">
                            <option value="">Please select</option>
                            @foreach ($types as $k=>$v)
                                <option value="{{$v}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subtype" class="control-label">Subtype Option:</label>
                        <select class="form-control" name="subtype" id="subtype">
                            <option value="">Please select</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="download" class="control-label">Download from Email:</label>
                        <select class="form-control" name="download" id="download">
                            <option value="">Please select</option>
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
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
                    {{ csrf_field() }}
                </form>
            </div>
            <div class="modal-footer">
                <div class="level">
                    <div class="flex">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success" id="do_continue">Continue</button>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
