<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScoreController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function show(Request $request, string $owner, string $repo): View|JsonResponse
    {
        $analysis = Analysis::where('repo_owner', $owner)
            ->where('repo_name', $repo)
            ->latest('created_at')
            ->firstOrFail();

        if ($request->wantsJson()) {
            return response()->json($this->publicPayload($analysis));
        }

        return view('score', ['analysis' => $analysis]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function publicPayload(Analysis $analysis): array
    {
        return [
            'status' => $analysis->status,
            'score' => $analysis->score,
            'metrics' => $analysis->metrics_json,
            'recommendations' => $analysis->recommendations_json,
        ];
    }
}
