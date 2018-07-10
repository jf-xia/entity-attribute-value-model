@extends('errors::layout')

@section('title', '419 '.trans('eav::eav.Page Expired.')'')

@section('message')
    The page has expired due to inactivity.
    <br/><br/>
    Please refresh and try again.
@stop
