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
    <p class="form-hint">Only public GitHub repositories can be analyzed. To test a private repo, clone Larascore and run it locally with your own GitHub Actions token.</p>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="score-explainer">
        <h2>How we calculate your score</h2>

        <ul class="factor-list">
            <li>
                <div class="factor-heading">
                    <span class="factor-name">Code</span>
                    <span class="factor-weight">25%</span>
                </div>
                <p class="factor-desc">Duplication, dead code, and overall code quality.</p>
            </li>
            <li>
                <div class="factor-heading">
                    <span class="factor-name">Complexity</span>
                    <span class="factor-weight">25%</span>
                </div>
                <p class="factor-desc">Cyclomatic complexity and how deeply nested your logic is.</p>
            </li>
            <li>
                <div class="factor-heading">
                    <span class="factor-name">Architecture</span>
                    <span class="factor-weight">25%</span>
                </div>
                <p class="factor-desc">Separation of concerns and dependency structure.</p>
            </li>
            <li>
                <div class="factor-heading">
                    <span class="factor-name">Style</span>
                    <span class="factor-weight">25%</span>
                </div>
                <p class="factor-desc">How closely your code follows PSR-12 coding standards.</p>
            </li>
        </ul>

        <p class="explainer-note"><strong>PHPStan errors</strong> subtract up to 30 points. <strong>Code smells</strong> like oversized controllers, overloaded models, and views with embedded PHP don't change the score directly — they become your top recommendations instead.</p>
    </div>

    @include('partials.rating-legend')
@endsection
