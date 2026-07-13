<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Services\GitHubActionsService;
use App\Services\GitHubUrlParser;
use App\Services\LaravelRepoValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class AnalyzeController extends Controller
{
    public function store(
        Request $request,
        GitHubUrlParser $parser,
        LaravelRepoValidator $repoValidator,
        GitHubActionsService $actions,
    ): RedirectResponse {
        $data = $request->validate([
            'repo_url' => ['required', 'string', 'max:255'],
        ]);

        $parsed = $parser->parse($data['repo_url']);

        if (! $parsed) {
            return back()
                ->withErrors(['repo_url' => 'Invalid GitHub repository URL. Use the format https://github.com/owner/repo.'])
                ->withInput();
        }

        ['owner' => $owner, 'repo' => $repoName] = $parsed;

        if (! $repoValidator->isLaravelRepo($owner, $repoName)) {
            return back()
                ->withErrors(['repo_url' => 'This repository does not look like a Laravel project (laravel/framework was not found in composer.json).'])
                ->withInput();
        }

        $analysis = Analysis::create([
            'repo_owner' => $owner,
            'repo_name' => $repoName,
            'status' => Analysis::STATUS_PENDING,
            'callback_token' => bin2hex(random_bytes(32)),
        ]);

        try {
            $actions->dispatch($analysis);
            $analysis->update(['status' => Analysis::STATUS_ANALYZING]);
        } catch (Throwable $e) {
            $analysis->update(['status' => Analysis::STATUS_FAILED]);
            report($e);

            return back()
                ->withErrors(['repo_url' => 'Could not start the analysis. Please try again later.'])
                ->withInput();
        }

        return redirect()->route('score.show', ['owner' => $owner, 'repo' => $repoName]);
    }
}
