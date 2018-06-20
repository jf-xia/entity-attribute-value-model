<hr style="margin-top: 0px;">

<div id="has-many-{{$column}}" class="has-many-{{$column}}">
    <div class="col-md-12 table-responsive no-padding">
        <div class="{{$viewClass['label']}}">
            <h3 class="pull-right">{{ $label }} &nbsp; </h3>
        </div>

        <table class="table has-many-{{$column}}-forms subForm">
            <thead><tr>
                <th><div class="add btn btn-success btn-sm pull-right"><i class="fa fa-plus-square"></i> &nbsp; {{ trans('admin.new') }}</div></th>
                @foreach($nestedForm->fields() as $field)
                    @if(! $field instanceof \Encore\Admin\Form\Field\Hidden)
                        <th>{!! $field->label() !!}</th>
                    @endif
                @endforeach
            </tr></thead><tbody>
        @foreach($forms as $pk => $form)
            <tr class="has-many-{{$column}}-form">
                <td>
                    <div class="remove btn btn-warning btn-sm pull-right"><i class="fa fa-trash"></i></div>
                    <div class="remove btn btn-warning btn-sm pull-right" data-toggle="modal" data-target="#attr-option-modal"><i class="fa fa-trash"></i></div>
                    <a href="" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i>&nbsp;&nbsp;{{ trans('admin.filter') }}</a>
                </td>
                @foreach($form->fields() as $field)
                    @if(! $field instanceof \Encore\Admin\Form\Field\Hidden)
                        <?php $field->setWidth(12,0);?>
                        <td>{!! $field->render() !!}</td>
                    @else
                        {!! $field->render() !!}
                    @endif
                @endforeach
            </tr>
        @endforeach
            </tbody>
        </table>
    </div>

    <template class="{{$column}}-tpl">
        <tr class="has-many-{{$column}}-form">
            <td><div class="remove btn btn-warning btn-sm pull-right"><i class="fa fa-trash"></i> @lang('admin.remove')</div></td>
            @foreach($nestedForm->fields() as $field)
                @if(! $field instanceof \Encore\Admin\Form\Field\Hidden)
                    <?php $field->setWidth(12,0);?>
                    <td>{!! $field->render() !!}</td>
                @else
                    {!! $field->render() !!}
                @endif
            @endforeach
        </tr>
    </template>

</div>


<div class="modal fade" id="attr-option-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<style type="text/css">.subForm .input-group-addon, .subForm label { display: none; }.subForm td { min-width: 100px; }</style>
