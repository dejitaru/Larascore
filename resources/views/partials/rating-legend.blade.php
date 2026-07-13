<p class="rating-legend">
    @foreach (\App\Services\ScoreCalculator::ratingBands() as $band)
        <span class="rating-legend-item">{{ $band['min'] }}%–{{ $band['max'] }}% {{ $band['label'] }}</span>@if (! $loop->last) &nbsp;·&nbsp; @endif
    @endforeach
</p>
