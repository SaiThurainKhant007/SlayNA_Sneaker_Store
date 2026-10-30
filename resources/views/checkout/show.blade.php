@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<h1 class="h4 mb-3">Checkout</h1>

<div class="row">
    <div class="col-md-7">
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card dashboard-card p-3 mb-3">
                <h2 class="h6">Shipping Address</h2>
                <textarea name="shipping_address" class="form-control" rows="3" required>{{ old('shipping_address', auth()->user()->address) }}</textarea>
            </div>

            @php $selected = old('payment_method', array_key_first($wallets)); @endphp
            <div class="card dashboard-card p-3 mb-3" id="sl-pay">
                <h2 class="h6">Payment Method</h2>
                @error('payment_method')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                <div class="sl-pay-options mb-3">
                    @foreach ($wallets as $key => $wallet)
                        <label class="sl-pay-option" style="--sl-wallet: {{ $wallet['color'] }}">
                            <input type="radio" name="payment_method" value="{{ $key }}" @checked($selected === $key)>
                            <i class="bi bi-phone" aria-hidden="true"></i>
                            <span><strong>{{ $wallet['short'] }}</strong><small>Scan QR or pay to phone</small></span>
                        </label>
                    @endforeach
                    <label class="sl-pay-option">
                        <input type="radio" name="payment_method" value="cod" @checked($selected === 'cod')>
                        <i class="bi bi-cash-coin" aria-hidden="true"></i>
                        <span><strong>Cash on Delivery</strong><small>Pay when it arrives</small></span>
                    </label>
                    @if ($savedCards->isNotEmpty())
                        <label class="sl-pay-option">
                            <input type="radio" name="payment_method" value="saved_card" @checked($selected === 'saved_card')>
                            <i class="bi bi-credit-card" aria-hidden="true"></i>
                            <span><strong>Saved Card</strong><small>Ending in {{ $savedCards->first()->card_last4 }}</small></span>
                        </label>
                    @endif
                </div>

                {{-- Shown when a mobile wallet is chosen --}}
                <div class="sl-wallet-box" data-wallet-box>
                    @foreach ($wallets as $key => $wallet)
                        <div class="sl-wallet-panel" data-wallet="{{ $key }}" style="--sl-wallet: {{ $wallet['color'] }}">
                            <div class="sl-wallet-qr">
                                @if (file_exists(public_path($wallet['qr'])))
                                    <img src="{{ asset($wallet['qr']) }}" alt="{{ $wallet['name'] }} QR code for {{ $wallet['account_name'] }}">
                                @else
                                    <div class="sl-wallet-qr-missing"><i class="bi bi-qr-code" aria-hidden="true"></i><span>QR code not available, please pay to the phone number</span></div>
                                @endif
                            </div>
                            <div class="sl-wallet-info">
                                <p class="sl-wallet-name mb-1">{{ $wallet['name'] }}</p>
                                <p class="small text-muted mb-2">{{ $wallet['account_name'] }}</p>
                                <div class="sl-wallet-phone mb-2">
                                    <span>{{ $wallet['phone'] }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-copy="{{ $wallet['phone'] }}"><i class="bi bi-copy" aria-hidden="true"></i> Copy</button>
                                </div>
                                <p class="mb-2">Amount to send: <strong class="sl-wallet-amount">{{ number_format($total, 0) }} MMK</strong></p>
                                <ol class="small text-muted ps-3 mb-0">
                                    <li>Open {{ $wallet['short'] }} and scan the QR code, or send to the phone number above.</li>
                                    <li>Send exactly <strong>{{ number_format($total, 0) }} MMK</strong>.</li>
                                    <li>Take a screenshot of the success screen and upload it below.</li>
                                </ol>
                            </div>
                        </div>
                    @endforeach

                    <div class="row g-2 mt-1">
                        <div class="col-sm-7">
                            <label class="form-label small" for="proof_image">Transfer screenshot <span class="text-danger">*</span></label>
                            <input type="file" id="proof_image" name="proof_image" data-crop="free" data-crop-max="2000" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm @error('proof_image') is-invalid @enderror">
                            @error('proof_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label small" for="transaction_ref">Transaction ID (optional)</label>
                            <input type="text" id="transaction_ref" name="transaction_ref" maxlength="40" class="form-control form-control-sm" value="{{ old('transaction_ref') }}" placeholder="From your payment receipt">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark">Place Order</button>
        </form>
    </div>

    <div class="col-md-5">
        <div class="card dashboard-card p-3">
            <h2 class="h6">Order Summary</h2>
            <ul class="list-group list-group-flush mb-2">
                @php $savings = 0; @endphp
                @foreach ($cartItems as $item)
                    @php
                        $product = $item->variant->product;
                        $promo = $product->livePromotion;
                        $fullSubtotal = (float) $product->price * $item->quantity;
                        if ($promo) { $savings += $fullSubtotal - $item->subtotal(); }
                    @endphp
                    <li class="list-group-item d-flex justify-content-between gap-2 px-0">
                        <span>
                            {{ $product->name }} &times; {{ $item->quantity }}
                            @if ($promo)
                                <br><span class="badge text-bg-danger">-{{ $promo->discount_percent }}%</span>
                                <small class="text-muted">{{ $promo->title }}</small>
                            @endif
                        </span>
                        <span class="text-end text-nowrap">
                            @if ($promo)
                                <small class="sl-promo-was">{{ number_format($fullSubtotal, 0) }} MMK</small><br>
                            @endif
                            {{ number_format($item->subtotal(), 0) }} MMK
                        </span>
                    </li>
                @endforeach
            </ul>
            @if ($savings > 0)
                <div class="d-flex justify-content-between small text-success mb-1">
                    <span>Promotion discount (applied automatically)</span>
                    <span>&minus;{{ number_format($savings, 0) }} MMK</span>
                </div>
            @endif
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span>{{ number_format($total, 0) }} MMK</span>
            </div>
        </div>
    </div>
</div>

<script>
// Show the wallet panel for the chosen method; the screenshot is only required for KPay / AYA Pay
(() => {
    const box = document.querySelector("[data-wallet-box]");
    const panels = box.querySelectorAll("[data-wallet]");
    const proof = document.getElementById("proof_image");
    const sync = () => {
        const method = document.querySelector("input[name=payment_method]:checked")?.value;
        const isWallet = [...panels].some(p => p.dataset.wallet === method);
        box.hidden = !isWallet;
        proof.required = isWallet;
        panels.forEach(p => p.hidden = p.dataset.wallet !== method);
    };
    document.querySelectorAll("input[name=payment_method]").forEach(r => r.addEventListener("change", sync));
    sync();

    document.querySelectorAll("[data-copy]").forEach(btn => btn.addEventListener("click", async () => {
        try { await navigator.clipboard.writeText(btn.dataset.copy); } catch { return; }
        const label = btn.innerHTML;
        btn.innerHTML = "<i class=\"bi bi-check2\" aria-hidden=\"true\"></i> Copied";
        setTimeout(() => btn.innerHTML = label, 1500);
    }));
})();
</script>
@include('partials.image-cropper')
@endsection
