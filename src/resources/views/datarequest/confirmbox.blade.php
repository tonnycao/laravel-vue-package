<div class="modal fade" tabindex="-1" role="dialog" id="confirm_box">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!--
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Operation confirm</h4>
            </div>
            -->
            <div class="modal-body">
                <form class="form-horizontal" id="confirm-form" action="" method="POST">
                    <div class="form-group">
                        <div id="confirm_info" style="margin:20px 10px;font-size:16px;">
                        </div>

                        <div id="confirm_alert"
                             style="margin:20px 10px;font-size:14px;font-style:italic;color:red;display:none">
                        </div>
                    </div>
                    <div class="form-group hide" id="reject-box">
                        <div style="margin:0px 10px">

                            <textarea class="form-control" id="reject-comment" name="comment"></textarea>
                            <span class="help-block">
                                Please provide your comment before you continue ( 10 - 200 characters).
                            </span>
                        </div>
                    </div>
                    {{ csrf_field() }}
                    <input type="hidden" id="action" name="action" value="approve"/>
                </form>
            </div>
            <div class="modal-footer">
                <div class="level">
                    <div class="flex">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success" id="confirm_continue">Continue</button>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
