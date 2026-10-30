@extends('errors.layout')

@section('code', '419')
@section('title', 'Your session expired')
@section('message', 'For your security, forms stop working after a while without activity. Go back, refresh the page and try again. Nothing was charged.')

@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-dark">Go back and try again</a>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Go to home page</a>
@endsection
