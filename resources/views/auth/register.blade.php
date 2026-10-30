@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="sl-auth sl-auth--register">
    @include('auth._showcase', ['variant' => 'register'])

    <div class="sl-auth-panel">
        <p class="sl-eyebrow mb-1">Join the crew</p>
        <h1 class="sl-auth-title">Create <span>account</span></h1>
        <ul class="sl-auth-perks">
            <li><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Early access to drops</li>
            <li><i class="bi bi-tag-fill" aria-hidden="true"></i> Promotion prices</li>
            <li><i class="bi bi-truck" aria-hidden="true"></i> Order tracking</li>
        </ul>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label" for="name">Full name</label>
                    <div class="sl-input">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="name" placeholder="Your full name" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="phone">Phone <span class="text-muted small">(optional)</span></label>
                    <div class="sl-input">
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" autocomplete="tel" placeholder="Your phone number">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="email">Email</label>
                    <div class="sl-input">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" placeholder="Your email address" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="address">Shipping address <span class="text-muted small">(optional)</span></label>
                    <div class="sl-input sl-input--area">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2" autocomplete="street-address" placeholder="No., street, township, city">{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="password">Password</label>
                    <div class="sl-input">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" placeholder="8+ characters" required>
                        <button type="button" class="sl-input-eye" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                    </div>
                    <div class="sl-strength" data-strength-for="password" aria-live="polite"><span></span><small></small></div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <div class="sl-input">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password" placeholder="Type it again" required>
                        <button type="button" class="sl-input-eye" data-toggle-password="password_confirmation" aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn sl-btn-red w-100 sl-auth-submit mt-4">Create Account <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>

        <p class="mt-4 mb-0 text-center">Already a member? <a href="{{ route('login') }}" class="sl-auth-link">Log in</a></p>
    </div>
</div>
@include('auth._password_toggle')
@endsection
