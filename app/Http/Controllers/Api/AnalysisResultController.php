<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Services\ScoreCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalysisResultController extends Controller
{
    public function store(Request $request, ScoreCalculator $calculator): JsonResponse
    {
        $data = $request->validate([
            'analysis_id' => ['required', 'uuid'],
            'callback_token' => ['required', 'string'],
            'status' => ['required', 'in:completed,failed'],
            'repo_owner' => ['required_if:status,completed', 'string'],
            'repo_name' => ['required_if:status,completed', 'string'],
            'phpstan' => ['nullable', 'array'],
            'insights' => ['nullable', 'array'],
            'taylor_rules' => ['nullable', 'array'],
        ]);

        $analysis = Analysis::find($data['analysis_id']);

        if (! $analysis) {
            return response()->json(['message' => 'Analysis not found'], 404);
        }

        if (! hash_equals($analysis->callback_token, $data['callback_token'])) {
            return response()->json(['message' => 'Invalid token'], 403);
        }

        if ($data['status'] === 'failed') {
            $analysis->update(['status' => Analysis::STATUS_FAILED]);

            return response()->json(['message' => 'ok']);
        }

        if ($data['repo_owner'] !== $analysis->repo_owner || $data['repo_name'] !== $analysis->repo_name) {
            return response()->json(['message' => 'Repo mismatch'], 403);
        }

        $result = $calculator->calculate($data['phpstan'] ?? null, $data['insights'] ?? null, $data['taylor_rules'] ?? null);

        $analysis->update([
            'status' => Analysis::STATUS_COMPLETED,
            'score' => $result['score'],
            'metrics_json' => $result['metrics'],
            'recommendations_json' => $result['recommendations'],
        ]);

        return response()->json(['message' => 'ok']);
    }
}
