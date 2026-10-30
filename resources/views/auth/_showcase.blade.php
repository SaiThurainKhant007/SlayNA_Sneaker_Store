{{--
    Animated campaign-photo slideshow for the auth pages (CSS only, 4 slides on a 20s loop).
    $variant: 'login'    -> slow zoom-and-crossfade ("Ken Burns")
              'register' -> each photo wipes in diagonally over the last, in a different order
    'pos' is the focal point kept in view when the photo is cropped (e.g. the short banner on phones).
--}}
@php
    $campaigns = [
        'nike'   => ['img' => 'img/auth/nike-air-jordan-1.jpg', 'brand' => 'Nike',        'model' => 'Air Jordan 1',   'pos' => '50% 50%'],
        'adidas' => ['img' => 'img/auth/adidas-superstar.jpg',  'brand' => 'adidas',      'model' => 'Superstar',      'pos' => '55% 72%'],
        'nb'     => ['img' => 'img/auth/new-balance-530.jpg',   'brand' => 'New Balance', 'model' => '530',            'pos' => '60% 78%'],
        'puma'   => ['img' => 'img/auth/puma-speedcat.jpg',     'brand' => 'PUMA',        'model' => 'Speedcat',       'pos' => '50% 55%'],
    ];
    $order = $variant === 'register' ? ['puma', 'nb', 'adidas', 'nike'] : ['nike', 'adidas', 'nb', 'puma'];
@endphp
<div class="sl-auth-show sl-auth-show--{{ $variant }}" aria-hidden="true">
    <div class="sl-auth-show-stage">
        @foreach ($order as $key)
            @php $c = $campaigns[$key]; @endphp
            <figure class="sl-auth-slide" style="--i: {{ $loop->index }}; --pos: {{ $c['pos'] }}">
                <img src="{{ asset($c['img']) }}" alt="" @if (! $loop->first) loading="lazy" @endif>
                <figcaption>
                    <span class="sl-auth-slide-tag">{{ $c['brand'] }}</span>
                    <span class="sl-auth-slide-line">{{ $c['model'] }} &middot; available at SlayNa</span>
                </figcaption>
            </figure>
        @endforeach
    </div>
    <div class="sl-auth-show-brand">SLAY<span>NA</span></div>
    <div class="sl-auth-show-bars">
        @foreach ($order as $key)<span style="--i: {{ $loop->index }}"></span>@endforeach
    </div>
</div>
