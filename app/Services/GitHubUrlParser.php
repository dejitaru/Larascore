<?php

namespace App\Services;

class GitHubUrlParser
{
    /**
     * @return array{owner: string, repo: string}|null
     */
    public function parse(string $url): ?array
    {
        $pattern = '#^https?://github\.com/([a-zA-Z0-9._-]+)/([a-zA-Z0-9._-]+?)(?:\.git)?/?$#i';

        if (! preg_match($pattern, trim($url), $matches)) {
            return null;
        }

        return ['owner' => $matches[1], 'repo' => $matches[2]];
    }
}
