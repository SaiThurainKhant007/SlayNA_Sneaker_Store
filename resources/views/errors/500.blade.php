@extends('errors.layout')

@section('code', '500')
@section('title', 'Something went wrong')
@section('message', 'Our store hit a problem on our side. Please try again in a few minutes. If you were placing an order, check My Orders before paying again.')

@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-dark">Try again</a>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Go to home page</a>
@endsection
