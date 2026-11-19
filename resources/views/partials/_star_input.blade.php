{{-- Clickable 1-5 star rating. $idPrefix keeps ids unique on the page; $selected is the star to pre-select. --}}
{{-- Radios run 5 to 1 and display reversed, so CSS can light up a star and all those before it --}}
<div class="sl-star-input">
    @for ($i = 5; $i >= 1; $i--)
        <input type="radio" class="visually-hidden" id="{{ $idPrefix }}-{{ $i }}" name="rating" value="{{ $i }}" required @checked($selected === $i)>
        <label for="{{ $idPrefix }}-{{ $i }}" title="{{ $i }} out of 5"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visually-hidden">{{ $i }} out of 5</span></label>
    @endfor
</div>
