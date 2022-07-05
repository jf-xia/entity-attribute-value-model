<div class="box-body table-responsive no-padding">
    {{--<h3 class="box-title"></h3>--}}

    <div class="pull-right">
        {!! $grid->renderFilter() !!}
        {!! $grid->renderExportButton() !!}
        {!! $grid->renderCreateButton() !!}
    </div>

    <span>
        {!! $grid->renderHeaderTools() !!}
    </span>
    <table class="table table-hover">
        <tr>
            @foreach($grid->columns() as $column)
            <th>{{$column->getLabel()}}{!! $column->sorter() !!}</th>
            @endforeach
        </tr>

        @foreach($grid->rows() as $row)
        <tr {!! $row->getRowAttributes() !!}>
            @foreach($grid->columnNames as $name)
            <td {!! $row->getColumnAttributes($name) !!}>
                {!! $row->column($name) !!}
            </td>
            @endforeach
        </tr>
        @endforeach

        {!! $grid->renderFooter() !!}
    </table>
</div>
<div class="box-footer clearfix">
    {!! $grid->paginator() !!}
    {!! $grid->renderBoxFooter() !!}
</div>