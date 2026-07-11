@extends('layout')

@section('title', $analysis->repo_owner . '/' . $analysis->repo_name . ' — Maintainability Score')

@section('content')
    <h1>{{ $analysis->repo_owner }}/{{ $analysis->repo_name }}</h1>

    <div class="card" id="result-card" data-status="{{ $analysis->status }}">
        <p>
            <span class="status-pill" id="status-pill">{{ $analysis->status }}</span>
        </p>

        <div id="pending-state" style="{{ in_array($analysis->status, ['pending', 'analyzing']) ? '' : 'display:none;' }}">
            <p>Analizando repo... esto puede tomar unos minutos.</p>
        </div>

        <div id="failed-state" style="{{ $analysis->status === 'failed' ? '' : 'display:none;' }}">
            <p>El análisis falló. Puedes intentar de nuevo desde la <a href="{{ route('home') }}">página principal</a>.</p>
        </div>

        <div id="completed-state" style="{{ $analysis->status === 'completed' ? '' : 'display:none;' }}">
            <div class="score" id="score-value">{{ $analysis->score }}</div>
            <p>/100</p>

            <div class="metrics" id="metrics-grid">
                @if ($analysis->metrics_json)
                    @foreach ($analysis->metrics_json as $label => $value)
                        <div class="metric">
                            <div class="label">{{ str_replace('_', ' ', $label) }}</div>
                            <div class="value">{{ $value }}</div>
                        </div>
                    @endforeach
                @endif
            </div>

            <h3>Top recomendaciones</h3>
            <ul class="recommendations" id="recommendations-list">
                @if ($analysis->recommendations_json)
                    @foreach ($analysis->recommendations_json as $recommendation)
                        <li>{{ $recommendation }}</li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <p><a href="{{ route('home') }}">← Analizar otro repo</a></p>

    @if (in_array($analysis->status, ['pending', 'analyzing']))
        <script>
            (function () {
                const pollUrl = window.location.href;

                function render(data) {
                    document.getElementById('result-card').dataset.status = data.status;
                    document.getElementById('status-pill').textContent = data.status;

                    if (data.status === 'completed' || data.status === 'failed') {
                        document.getElementById('pending-state').style.display = 'none';
                    }

                    if (data.status === 'failed') {
                        document.getElementById('failed-state').style.display = '';
                        return;
                    }

                    if (data.status === 'completed') {
                        document.getElementById('completed-state').style.display = '';
                        document.getElementById('score-value').textContent = data.score;

                        const metricsGrid = document.getElementById('metrics-grid');
                        metricsGrid.innerHTML = '';
                        Object.entries(data.metrics || {}).forEach(([label, value]) => {
                            const div = document.createElement('div');
                            div.className = 'metric';
                            div.innerHTML = `<div class="label">${label.replace(/_/g, ' ')}</div><div class="value">${value}</div>`;
                            metricsGrid.appendChild(div);
                        });

                        const list = document.getElementById('recommendations-list');
                        list.innerHTML = '';
                        (data.recommendations || []).forEach((rec) => {
                            const li = document.createElement('li');
                            li.textContent = rec;
                            list.appendChild(li);
                        });
                    }
                }

                function poll() {
                    fetch(pollUrl, { headers: { Accept: 'application/json' } })
                        .then((res) => res.json())
                        .then((data) => {
                            render(data);
                            if (data.status === 'pending' || data.status === 'analyzing') {
                                setTimeout(poll, 4000);
                            }
                        })
                        .catch(() => setTimeout(poll, 8000));
                }

                setTimeout(poll, 4000);
            })();
        </script>
    @endif
@endsection
