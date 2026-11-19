{{-- Order details page: star rating and optional text for one product. --}}
@php $isOldInput = (int) old('review_product_id') === $product->id; @endphp
<form method="POST" action="{{ route('products.reviews.store', $product) }}">
    @csrf
    <input type="hidden" name="review_product_id" value="{{ $product->id }}">
    <fieldset class="mb-2">
        <legend class="form-label small mb-1">Your rating</legend>
        @include('partials._star_input', ['idPrefix' => 'rating-'.$product->id, 'selected' => $isOldInput ? (int) old('rating') : null])
    </fieldset>
    <div class="mb-2">
        <label for="review-comment-{{ $product->id }}" class="form-label small">Your review (optional)</label>
        <textarea id="review-comment-{{ $product->id }}" name="comment" class="form-control form-control-sm" rows="2" maxlength="2000" placeholder="Share your thoughts on this product...">{{ $isOldInput ? old('comment') : '' }}</textarea>
    </div>
    <button type="submit" class="btn btn-dark btn-sm">Submit Review</button>
</form>
