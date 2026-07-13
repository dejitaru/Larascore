<div class="rating-scale">
    @foreach (\App\Services\ScoreCalculator::ratingBands() as $band)
        <span class="rating-chip is-{{ $band['slug'] }}">
            <span class="rating-chip-label">{{ $band['label'] }}</span>
            <span class="rating-chip-range">{{ $band['min'] }}–{{ $band['max'] }}%</span>
        </span>
    @endforeach
</div>
