<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LaravelRepoValidator
{
    public function isLaravelRepo(string $owner, string $repo): bool
    {
        foreach (['main', 'master'] as $branch) {
            $response = Http::get("https://raw.githubusercontent.com/{$owner}/{$repo}/{$branch}/composer.json");

            if (! $response->ok()) {
                continue;
            }

            $composer = $response->json();

            return isset($composer['require']['laravel/framework'])
                || isset($composer['require-dev']['laravel/framework']);
        }

        return false;
    }
}
