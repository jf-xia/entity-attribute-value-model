
<div class="row">
    <div class="{{$viewClass['label']}}"><h3 class="pull-right">{{ $label }}</h3></div>
    {{--<div class="{{$viewClass['field']}}"></div>--}}
</div>

<hr style="margin-top: 0px;">

<div id="has-many-{{$column}}" class="has-many-{{$column}}">

    <div class="col-md-12 table-responsive no-padding">

        <table class="table has-many-{{$column}}-forms subForm">
        @foreach($forms as $pk => $form)
            @if($loop->first)
                <tr>
                    <th><div class="add btn btn-success btn-sm pull-right"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}</div></th>
                    @foreach($form->fields() as $field)
                        @if(! $field instanceof \Encore\Admin\Form\Field\Hidden)
                            <th>{!! $field->label() !!}</th>
                        @endif
                    @endforeach
                </tr>
            @endif
            <tr class="has-many-{{$column}}-form">
                <td><div class="remove btn btn-warning btn-sm pull-right"><i class="fa fa-trash"></i> &nbsp;{{ trans('admin.remove') }}</div></td>
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
        </table>
    </div>

    <template class="{{$column}}-tpl">
        <tr class="has-many-{{$column}}-form">
            {!! ($template) !!}
        </tr>
    </template>

</div>

<style type="text/css">.subForm .input-group-addon, .subForm label { display: none; }.subForm td { min-width: 100px; }</style>
