

<div class="modal fade" id="attr-option-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="remove btn btn-warning btn-sm pull-left" data-toggle="modal" data-target="#attr-option-modal"><i class="fa fa-check-square"></i></div>
    
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">{{ trans('eav::eav.attribute option') }}</h4>
            </div>
            <form action="{!! $action !!}" method="get" pjax-container>
                <div class="modal-body">
                    <div class="form">
                            <div class="form-group">
                                222
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary submit">{{ trans('admin.submit') }}</button>
                    <button type="reset" class="btn btn-warning pull-left">{{ trans('admin.reset') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
