@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="sl-auth sl-auth--login">
    @include('auth._showcase', ['variant' => 'login'])

    <div class="sl-auth-panel">
        <p class="sl-eyebrow mb-1">Welcome back</p>
        <h1 class="sl-auth-title">Log <span>in</span></h1>
        <p class="text-muted mb-4">Pick up where you left off: your cart, orders and saved cards are waiting.</p>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <div class="sl-input">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" placeholder="Your email address" required autofocus>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label" for="password">Password</label>
                <div class="sl-input">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" placeholder="Your password" required>
                    <button type="button" class="sl-input-eye" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" @checked(old('remember'))>
                <label class="form-check-label small" for="remember">Keep me logged in</label>
            </div>
            <button type="submit" class="btn sl-btn-red w-100 sl-auth-submit">Log In <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>

        <p class="mt-4 mb-0 text-center">New to SlayNa? <a href="{{ route('register') }}" class="sl-auth-link">Create an account</a></p>
    </div>
</div>
@include('auth._password_toggle')
@endsection
