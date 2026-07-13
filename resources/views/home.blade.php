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

    @include('partials.rating-legend')
@endsection
