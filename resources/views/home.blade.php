@extends('layout')

@section('title', 'Laravel Maintainability Score')

@section('content')
    <h1>Laravel Maintainability Score</h1>
    <p class="subtitle">In 1 minute, find out how maintainable your Laravel repo is and what concrete changes will raise your score.</p>

    <form method="POST" action="{{ route('analyze.store') }}">
        @csrf
        <input
            type="url"
            name="repo_url"
            placeholder="https://github.com/owner/repo"
            value="{{ old('repo_url') }}"
            required
        >
        <button type="submit">Analyze</button>
    </form>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="score-explainer">
        <h2>How we calculate your score</h2>

        <div class="factor-grid">
            <div class="factor-card">
                <span class="factor-weight">25%</span>
                <span class="factor-name">Code</span>
                <span class="factor-desc">Duplication, dead code, and overall code quality.</span>
            </div>
            <div class="factor-card">
                <span class="factor-weight">25%</span>
                <span class="factor-name">Complexity</span>
                <span class="factor-desc">Cyclomatic complexity and how deeply nested your logic is.</span>
            </div>
            <div class="factor-card">
                <span class="factor-weight">25%</span>
                <span class="factor-name">Architecture</span>
                <span class="factor-desc">Separation of concerns and dependency structure.</span>
            </div>
            <div class="factor-card">
                <span class="factor-weight">25%</span>
                <span class="factor-name">Style</span>
                <span class="factor-desc">How closely your code follows PSR-12 coding standards.</span>
            </div>
        </div>

        <div class="explainer-notes">
            <p><strong>Static analysis errors</strong> (via PHPStan) subtract points — the more errors, the bigger the penalty, up to 30 points.</p>
            <p><strong>Code smells</strong> like oversized controllers, overloaded models, and views with embedded PHP don't change the score directly, but become your top recommendations below.</p>
        </div>
    </div>

    @include('partials.rating-legend')
@endsection
