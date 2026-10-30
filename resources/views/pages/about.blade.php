@extends('layouts.app')

@section('title', 'About Us')

@section('hero')
<section class="sl-page-head">
    <div class="container">
        <p class="sl-eyebrow">Who we are</p>
        <h1 class="sl-page-title">About <span>SlayNa</span></h1>
        <p class="sl-page-sub">A Yangon sneaker store bringing authentic kicks from the world's top brands to your door.</p>
    </div>
</section>
@endsection

@section('content')
<div class="row g-4 align-items-center mb-5">
    <div class="col-lg-6">
        <h2 class="sl-section-title">Our Story</h2>
        <p>SlayNa Sneaker Store started with a simple idea: everyone in Myanmar should be able to buy genuine sneakers without guesswork. We source directly from trusted distributors of Nike, Adidas, Puma, New Balance and more, so every pair you order is 100% authentic.</p>
        <p class="mb-0">Browse online, order in a few taps and track your kicks all the way from our store to your doorstep.</p>
    </div>
    <div class="col-lg-6">
        <div class="row row-cols-2 g-3">
            <div class="col"><div class="sl-stat"><strong>100%</strong><span>Authentic products</span></div></div>
            <div class="col"><div class="sl-stat"><strong>Top</strong><span>Global brands</span></div></div>
            <div class="col"><div class="sl-stat"><strong>Fast</strong><span>Delivery in Yangon</span></div></div>
            <div class="col"><div class="sl-stat"><strong>Easy</strong><span>Online ordering &amp; tracking</span></div></div>
        </div>
    </div>
</div>

<h2 class="sl-section-title">Why Shop With Us</h2>
<div class="row row-cols-1 row-cols-md-3 g-3 g-md-4 mb-4">
    <div class="col">
        <div class="sl-feature">
            <i class="bi bi-patch-check-fill"></i>
            <h3>Guaranteed Authentic</h3>
            <p>Every pair comes from verified suppliers. No fakes, ever.</p>
        </div>
    </div>
    <div class="col">
        <div class="sl-feature">
            <i class="bi bi-truck"></i>
            <h3>Order Tracking</h3>
            <p>Follow your order from confirmation to delivery in your account.</p>
        </div>
    </div>
    <div class="col">
        <div class="sl-feature">
            <i class="bi bi-star-fill"></i>
            <h3>Real Reviews</h3>
            <p>Read honest ratings from customers who bought the same pair.</p>
        </div>
    </div>
</div>

<div class="text-center">
    <a href="{{ route('products.index') }}" class="btn sl-btn-red">Start Shopping &rarr;</a>
</div>
@endsection
