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
                ->withErrors(['repo_url' => 'URL de repositorio de GitHub inválida. Usa el formato https://github.com/owner/repo.'])
                ->withInput();
        }

        ['owner' => $owner, 'repo' => $repoName] = $parsed;

        if (! $repoValidator->isLaravelRepo($owner, $repoName)) {
            return back()
                ->withErrors(['repo_url' => 'El repositorio no parece ser un proyecto Laravel (no se encontró laravel/framework en composer.json).'])
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
                ->withErrors(['repo_url' => 'No se pudo iniciar el análisis. Intenta de nuevo más tarde.'])
                ->withInput();
        }

        return redirect()->route('score.show', ['owner' => $owner, 'repo' => $repoName]);
    }
}
