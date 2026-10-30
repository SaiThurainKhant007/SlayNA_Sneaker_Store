{{-- Item list for staff; promotional lines show the promotion, original price and discount --}}
<details class="small mt-1" @if ($order->hasPromotion()) open @endif>
    <summary class="text-muted">{{ $order->items->sum('quantity') }} {{ Str::plural('item', $order->items->sum('quantity')) }}</summary>
    <ul class="list-unstyled mb-0 mt-1">
        @foreach ($order->items as $item)
            <li class="mb-1 @if ($item->isPromotional()) sl-staff-promo-line @endif">
                {{ $item->variant->product->name }} (EU {{ $item->variant->size->label }}) &times; {{ $item->quantity }}
                &mdash; {{ number_format((float) $item->unit_price, 0) }} MMK
                @if ($item->isPromotional())
                    <br>
                    <span class="badge text-bg-danger">PROMO -{{ $item->discount_percent }}%</span>
                    <span class="text-muted">{{ $item->promotion_title }} &middot; was</span>
                    <span class="sl-promo-was">{{ number_format((float) $item->original_price, 0) }} MMK</span>
                @endif
            </li>
        @endforeach
    </ul>
</details>
