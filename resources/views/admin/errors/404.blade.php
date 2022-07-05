@extends('errors::layout')

@section('title', '404 '.trans('eav::eav.Error! Page not found.'))

@section('message')
    @lang('eav::eav.The page you entered was not found!')<a href="{{ url('/') }}" >@lang('eav::eav.Back home')</a> | <a href="javascript:history.back()" >@lang('eav::eav.Return to previous step')</a><br>
    @lang('eav::eav.If you have any questions, please contact system administrator')
@endsection
