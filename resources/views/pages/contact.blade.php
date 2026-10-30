@extends('layouts.app')

@section('title', 'Contact')

{{-- Contact details live in config/shop.php so the footer and this page always match --}}
@php $contact = config('shop'); @endphp

@section('hero')
<section class="sl-page-head">
    <div class="container">
        <p class="sl-eyebrow">We're here to help</p>
        <h1 class="sl-page-title">Contact <span>Us</span></h1>
        <p class="sl-page-sub">Questions about sizing, an order or a product? Reach out and our team will get back to you.</p>
    </div>
</section>
@endsection

@section('content')
@php
    // Opening status in the shop's own timezone (Yangon)
    $nowLocal = now(config('app.store_timezone'));
    $todayName = $nowLocal->format('l');
    $todayHours = $contact['week'][$todayName] ?? null;
    $openNow = $todayHours && $nowLocal->format('H:i') >= $todayHours[0] && $nowLocal->format('H:i') < $todayHours[1];
    $fmtTime = fn (string $t) => \Illuminate\Support\Carbon::createFromFormat('H:i', $t)->format('g:i A');
    $mapsLink = 'https://www.google.com/maps/search/?api=1&query='.urlencode($contact['map_query']);
@endphp

{{-- Zig-zag rows: text and a visual side by side, swapping sides on every other row --}}
<div class="sl-zigzag">
    {{-- 1. Visit Us + map --}}
    <section class="sl-zigzag-row" aria-labelledby="visit-title">
        <div class="sl-zigzag-text">
            <p class="sl-eyebrow mb-1"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Visit Us</p>
            <h2 id="visit-title" class="sl-zigzag-title">Come try them on</h2>
            <p class="sl-zigzag-lead">Our store is in {{ $contact['address'] }}. Drop by to check sizes, see new arrivals in person, or pick up your order.</p>
            <p class="sl-zigzag-detail"><i class="bi bi-pin-map-fill" aria-hidden="true"></i> {{ $contact['address'] }}</p>
            <a href="{{ $mapsLink }}" target="_blank" rel="noopener" class="btn sl-btn-red">Get Directions <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="sl-zigzag-visual sl-zigzag-map">
            <iframe src="https://www.google.com/maps?q={{ urlencode($contact['map_query']) }}&amp;z=12&amp;output=embed"
                    title="Map of {{ $contact['map_query'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    </section>

    {{-- 2. Call Us + phone card --}}
    <section class="sl-zigzag-row" aria-labelledby="call-title">
        <div class="sl-zigzag-text">
            <p class="sl-eyebrow mb-1"><i class="bi bi-telephone-fill" aria-hidden="true"></i> Call Us</p>
            <h2 id="call-title" class="sl-zigzag-title">Talk to a sneaker expert</h2>
            <p class="sl-zigzag-lead">Not sure about sizing, stock or delivery? Give us a call during opening hours and we'll help you find the right pair.</p>
            <p class="sl-zigzag-detail"><i class="bi bi-clock" aria-hidden="true"></i> {{ $contact['hours'] }}</p>
        </div>
        <div class="sl-zigzag-visual sl-zigzag-card">
            <i class="bi bi-phone-vibrate sl-zigzag-card-icon" aria-hidden="true"></i>
            <p class="sl-zigzag-card-label">Phone</p>
            <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="sl-zigzag-card-value">{{ $contact['phone'] }}</a>
            <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="btn sl-btn-red mt-3"><i class="bi bi-telephone-outbound-fill" aria-hidden="true"></i> Call Now</a>
        </div>
    </section>

    {{-- 3. Email Us + email card --}}
    <section class="sl-zigzag-row" aria-labelledby="email-title">
        <div class="sl-zigzag-text">
            <p class="sl-eyebrow mb-1"><i class="bi bi-envelope-fill" aria-hidden="true"></i> Email Us</p>
            <h2 id="email-title" class="sl-zigzag-title">Drop us a line</h2>
            <p class="sl-zigzag-lead">Questions about an order, returns or a product? Send us an email with your order number and we'll reply within one working day.</p>
            <p class="sl-zigzag-detail"><i class="bi bi-reply-fill" aria-hidden="true"></i> Replies within 1 working day</p>
        </div>
        <div class="sl-zigzag-visual sl-zigzag-card">
            <i class="bi bi-envelope-paper-heart sl-zigzag-card-icon" aria-hidden="true"></i>
            <p class="sl-zigzag-card-label">Email</p>
            <a href="mailto:{{ $contact['email'] }}" class="sl-zigzag-card-value">{{ $contact['email'] }}</a>
            <a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Question for SlayNa') }}" class="btn sl-btn-red mt-3"><i class="bi bi-send-fill" aria-hidden="true"></i> Write to Us</a>
        </div>
    </section>

    {{-- 4. Opening Hours + weekly schedule --}}
    <section class="sl-zigzag-row" aria-labelledby="hours-title">
        <div class="sl-zigzag-text">
            <p class="sl-eyebrow mb-1"><i class="bi bi-clock-fill" aria-hidden="true"></i> Opening Hours</p>
            <h2 id="hours-title" class="sl-zigzag-title">When to find us</h2>
            <p class="sl-zigzag-lead">The store and phone line are open {{ $contact['hours'] }}. You can order online any time.</p>
            <p class="sl-zigzag-status {{ $openNow ? 'is-open' : 'is-closed' }}">
                <span class="sl-zigzag-dot" aria-hidden="true"></span>
                {{ $openNow ? 'Open now' : 'Closed now' }}
                <small>&middot; Yangon time {{ $nowLocal->format('g:i A') }}</small>
            </p>
        </div>
        <div class="sl-zigzag-visual sl-zigzag-card sl-zigzag-hours">
            <table class="sl-hours-table">
                <caption class="visually-hidden">Weekly opening hours</caption>
                <tbody>
                    @foreach ($contact['week'] as $day => $hours)
                        <tr @class(['is-today' => $day === $todayName])>
                            <th scope="row">{{ $day }}@if ($day === $todayName) <span class="sl-hours-today">Today</span>@endif</th>
                            <td>{{ $hours ? $fmtTime($hours[0]).' – '.$fmtTime($hours[1]) : 'Closed' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="sl-cta-box">
    <div>
        <h2>Already ordered?</h2>
        <p>Check your order status anytime from your account.</p>
    </div>
    @guest
        <a href="{{ route('login') }}" class="btn sl-btn-red">Log In &rarr;</a>
    @else
        @if (auth()->user()->isCustomer())
            <a href="{{ route('account.dashboard') }}" class="btn sl-btn-red">My Account &rarr;</a>
        @endif
    @endguest
</div>
@endsection
