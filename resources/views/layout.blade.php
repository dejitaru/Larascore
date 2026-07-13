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
            --space-1: 4px;
            --space-2: 8px;
            --space-3: 12px;
            --space-4: 16px;
            --space-6: 24px;
            --space-8: 32px;
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
        .form-hint {
            margin: var(--space-2) 0 0;
            font-size: 0.8rem;
            color: var(--muted);
        }
        .errors {
            margin-top: 1rem;
            color: var(--accent);
            font-size: 0.9rem;
        }
        .score-explainer {
            margin-top: var(--space-8);
            padding: var(--space-6);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            background: color-mix(in srgb, var(--fg) 3%, transparent);
        }
        .score-explainer h2 {
            margin: 0 0 var(--space-4);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .factor-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-4) var(--space-6);
        }
        .factor-heading {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: var(--space-2);
        }
        .factor-name {
            font-size: 0.9rem;
            font-weight: 600;
        }
        .factor-weight {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
        }
        .factor-desc {
            margin: var(--space-1) 0 0;
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.5;
        }
        .explainer-note {
            margin: var(--space-6) 0 0;
            padding-top: var(--space-4);
            border-top: 1px solid var(--border);
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.6;
        }
        .explainer-note strong {
            color: var(--fg);
            font-weight: 600;
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
        .rating,
        .rating-chip {
            --chip-h: 0;
            --chip-s: 70%;
            --chip-l: 45%;
            display: inline-flex;
            align-items: baseline;
            gap: 0.4em;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            background: hsl(var(--chip-h) var(--chip-s) var(--chip-l) / 0.12);
            border: 1px solid hsl(var(--chip-h) var(--chip-s) var(--chip-l) / 0.35);
            color: hsl(var(--chip-h) var(--chip-s) var(--chip-l));
        }
        .rating-chip-range {
            font-size: 0.8em;
            font-weight: 500;
            opacity: 0.8;
            white-space: nowrap;
        }
        .is-excellent { --chip-h: 142; --chip-s: 65%; --chip-l: 38%; }
        .is-good { --chip-h: 172; --chip-s: 55%; --chip-l: 35%; }
        .is-fair { --chip-h: 43; --chip-s: 90%; --chip-l: 40%; }
        .is-needs-improvement { --chip-h: 25; --chip-s: 90%; --chip-l: 45%; }
        .is-poor { --chip-h: 4; --chip-s: 80%; --chip-l: 48%; }
        @media (prefers-color-scheme: dark) {
            .is-excellent { --chip-l: 58%; }
            .is-good { --chip-l: 55%; }
            .is-fair { --chip-l: 58%; }
            .is-needs-improvement { --chip-l: 62%; }
            .is-poor { --chip-l: 64%; }
        }
        .rating-scale {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
            margin-top: var(--space-6);
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
            text-align: center;
        }
        .footer-credits {
            margin: var(--space-1) 0 0;
        }
        .footer-word {
            display: inline-block;
            font-weight: 600;
            color: var(--fg);
            transition: opacity 0.25s ease;
        }
        .footer-word.is-swapping {
            opacity: 0;
        }
    </style>
</head>
<body>
    <main>
        @yield('content')
        <footer>
            Inspired on maintainability and simplicity.
            <p class="footer-credits">Built with <span class="footer-word" id="footer-word-a">love</span> and <span class="footer-word" id="footer-word-b">coffee</span>.</p>
        </footer>
    </main>
    <script>
        (function () {
            const words = ['love', 'codex', 'coffee', 'beer', 'patience', 'claude', 'red bull', 'whisky', 'faith'];
            const wordA = document.getElementById('footer-word-a');
            const wordB = document.getElementById('footer-word-b');

            function pickTwoDistinct() {
                const a = words[Math.floor(Math.random() * words.length)];
                let b = a;
                while (b === a) {
                    b = words[Math.floor(Math.random() * words.length)];
                }
                return [a, b];
            }

            function swap() {
                const [a, b] = pickTwoDistinct();

                [wordA, wordB].forEach((el) => el.classList.add('is-swapping'));

                setTimeout(() => {
                    wordA.textContent = a;
                    wordB.textContent = b;
                    [wordA, wordB].forEach((el) => el.classList.remove('is-swapping'));
                }, 250);
            }

            setInterval(swap, 2800);
        })();
    </script>
</body>
</html>
