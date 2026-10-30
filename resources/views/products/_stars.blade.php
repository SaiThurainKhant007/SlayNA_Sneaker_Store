@php $rounded = (int) round($rating); @endphp
<span class="rating-stars {{ $class ?? '' }}">{{ str_repeat('★', $rounded) . str_repeat('☆', 5 - $rounded) }}</span>
<span class="text-muted">({{ $count }}@if(!($hideWord ?? false)) review{{ $count === 1 ? '' : 's' }}@endif)</span>
