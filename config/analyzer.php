<?php

return [
    'github_token' => env('GITHUB_ANALYZER_PAT'),
    'owner' => env('GITHUB_ANALYZER_OWNER'),
    'repo' => env('GITHUB_ANALYZER_REPO'),
    'workflow' => env('GITHUB_ANALYZER_WORKFLOW', 'analyze-repo.yml'),
    'ref' => env('GITHUB_ANALYZER_REF', 'main'),
    'secret' => env('APP_ANALYZER_SECRET'),
    'stale_after_minutes' => (int) env('ANALYZER_STALE_AFTER_MINUTES', 25),
];
