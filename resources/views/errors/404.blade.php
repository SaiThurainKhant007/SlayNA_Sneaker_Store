@extends('errors.layout')

@section('code', '404')
@section('title', 'Page not found')
@section('message', 'This pair has walked off. The page may have moved, or the product is no longer available.')

@section('actions')
    <a href="{{ url('/products') }}" class="btn btn-dark">Browse sneakers</a>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Go to home page</a>
@endsection
