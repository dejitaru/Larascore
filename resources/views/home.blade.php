@extends('layout')

@section('title', 'Laravel Maintainability Score')

@section('content')
    <h1>Laravel Maintainability Score</h1>
    <p class="subtitle">En 1 minuto, descubre qué tan mantenible es tu repo Laravel y qué cambios concretos te harán subir el score.</p>

    <form method="POST" action="{{ route('analyze.store') }}">
        @csrf
        <input
            type="url"
            name="repo_url"
            placeholder="https://github.com/owner/repo"
            value="{{ old('repo_url') }}"
            required
        >
        <button type="submit">Analizar</button>
    </form>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
@endsection
