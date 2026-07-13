<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laravel Maintainability Score')</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #fdfdfc;
            --fg: #1b1b18;
            --muted: #706f6c;
            --border: #e3e3e0;
            --accent: #f53003;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0a0a0a;
                --fg: #ededec;
                --muted: #a1a09a;
                --border: #3e3e3a;
                --accent: #ff4433;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            background: var(--bg);
            color: var(--fg);
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        main {
            width: 100%;
            max-width: 560px;
        }
        h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        p.subtitle {
            color: var(--muted);
            margin-top: 0;
            margin-bottom: 2rem;
        }
        form {
            display: flex;
            gap: 0.5rem;
        }
        input[type="url"], input[type="text"] {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            background: transparent;
            color: var(--fg);
            font-size: 1rem;
        }
        button {
            padding: 0.75rem 1.25rem;
            border: 1px solid var(--fg);
            border-radius: 0.5rem;
            background: var(--fg);
            color: var(--bg);
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover { opacity: 0.85; }
        .errors {
            margin-top: 1rem;
            color: var(--accent);
            font-size: 0.9rem;
        }
        .card {
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
        }
        .score-row {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .score {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
        }
        .rating {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid var(--border);
        }
        .rating-legend {
            margin-top: 0.75rem;
            font-size: 0.8rem;
            color: var(--muted);
        }
        .metrics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin: 1.5rem 0;
        }
        .metric {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.75rem;
        }
        .metric .label {
            color: var(--muted);
            font-size: 0.8rem;
        }
        .metric .value {
            font-size: 1.25rem;
            font-weight: 600;
        }
        ul.recommendations {
            padding-left: 1.25rem;
        }
        ul.recommendations li {
            margin-bottom: 0.5rem;
        }
        .status-pill {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border);
        }
        footer {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main>
        @yield('content')
        <footer>Inspired on maintainability and simplicity.</footer>
    </main>
</body>
</html>
