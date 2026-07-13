<p class="rating-legend">
    @foreach (\App\Services\ScoreCalculator::ratingBands() as $band)
        {{ $band['min'] }}%–{{ $band['max'] }}% {{ $band['label'] }}@if (! $loop->last) &nbsp;·&nbsp; @endif
    @endforeach
</p>
