<hr style="margin-top: 0px;">

<div id="has-many-{{$column}}" class="has-many-{{$column}}">
    <div class="col-md-12 table-responsive no-padding">
        <h3 class="pull-left">{{ $label }} &nbsp; </h3>

        <table class="table has-many-{{$column}}-forms subForm">
            <thead><tr>
                <th width="100px"><div class="add btn btn-success btn-sm pull-right"><i class="fa fa-plus-square"></i> &nbsp; {{ trans('admin.new') }}</div></th>
                @foreach($nestedForm->fields() as $field)
                    @if(! $field instanceof \Encore\Admin\Form\Field\Hidden)
                        <th>{!! $field->label() !!}</th>
                    @endif
                @endforeach
            </tr></thead><tbody>
        @foreach($forms as $pk => $form)
            <tr class="has-many-{{$column}}-form fields-group">
                <td><div class="remove btn btn-warning btn-sm pull-right"><i class="fa fa-trash"></i> @lang('admin.remove')</div></td>
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
        <tr class="has-many-{{$column}}-form fields-group">
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

<style type="text/css">
    .subForm .input-group-addon, .subForm label { display: none; }
    .subForm td { min-width: 100px; }
    .subForm { width: 98%; }
</style>
