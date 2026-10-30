@extends('layouts.app')

@section('title', 'Saved Cards')

@section('content')
<h1 class="h4 mb-3">Saved Payment Cards</h1>

<div class="row">
    <div class="col-md-6">
        @forelse ($cards as $card)
            <div class="card dashboard-card p-3 mb-2 d-flex flex-row justify-content-between align-items-center">
                <div>
                    <strong>{{ $card->card_type }}</strong> ending in <strong>{{ $card->card_last4 }}</strong><br>
                    <span class="text-muted small">{{ $card->card_holder }} &middot; Exp {{ sprintf('%02d', $card->expiry_month) }}/{{ $card->expiry_year }}</span>
                </div>
                <form method="POST" action="{{ route('account.saved-cards.destroy', $card) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
            </div>
        @empty
            <p class="text-muted">No saved cards yet.</p>
        @endforelse
    </div>
    <div class="col-md-6">
        <div class="card dashboard-card p-3">
            <h2 class="h6">Add a Card</h2>
            <form method="POST" action="{{ route('account.saved-cards.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Cardholder Name</label>
                    <input type="text" name="card_holder" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Card Number</label>
                    <input type="text" name="card_number" class="form-control form-control-sm" inputmode="numeric" maxlength="19" placeholder="Only last 4 digits are stored" required>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col">
                        <label class="form-label small">Type</label>
                        <select name="card_type" class="form-select form-select-sm">
                            <option>Visa</option>
                            <option>Mastercard</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label small">Exp. Month</label>
                        <input type="number" name="expiry_month" class="form-control form-control-sm" min="1" max="12" required>
                    </div>
                    <div class="col">
                        <label class="form-label small">Exp. Year</label>
                        <input type="number" name="expiry_year" class="form-control form-control-sm" min="{{ date('Y') }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark btn-sm">Save Card</button>
            </form>
        </div>
    </div>
</div>
@endsection
