<?php

namespace App\Services;

use App\Models\Analysis;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubActionsService
{
    public function dispatch(Analysis $analysis): void
    {
        $owner = config('analyzer.owner');
        $repo = config('analyzer.repo');
        $workflow = config('analyzer.workflow');

        $response = Http::withToken(config('analyzer.github_token'))
            ->acceptJson()
            ->post("https://api.github.com/repos/{$owner}/{$repo}/actions/workflows/{$workflow}/dispatches", [
                'ref' => config('analyzer.ref'),
                'inputs' => [
                    'analysis_id' => $analysis->id,
                    'repo_owner' => $analysis->repo_owner,
                    'repo_name' => $analysis->repo_name,
                    'callback_token' => $analysis->callback_token,
                    'callback_url' => route('api.analysis-result'),
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Failed to dispatch analyzer workflow: {$response->status()} {$response->body()}");
        }
    }
}
