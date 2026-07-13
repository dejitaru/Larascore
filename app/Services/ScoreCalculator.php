<?php

namespace App\Services;

class ScoreCalculator
{
    /**
     * @var array<int, array{min: int, label: string}>
     */
    private const RATING_BANDS = [
        ['min' => 90, 'label' => 'Excellent'],
        ['min' => 75, 'label' => 'Good'],
        ['min' => 60, 'label' => 'Fair'],
        ['min' => 40, 'label' => 'Needs Improvement'],
        ['min' => 0, 'label' => 'Poor'],
    ];

    /**
     * @return array{score: int, metrics: array<string, mixed>, recommendations: array<int, string>}
     */
    public function calculate(?array $phpstan, ?array $insights, ?array $taylorRules = null): array
    {
        // Assumed shape of PHP Insights JSON (summary.*) and PHPStan's (totals.file_errors);
        // adjust these paths if the real format differs once tested against a live analysis.
        $insightsCode = (float) ($insights['summary']['code'] ?? 0);
        $insightsComplexity = (float) ($insights['summary']['complexity'] ?? 0);
        $insightsArchitecture = (float) ($insights['summary']['architecture'] ?? 0);
        $insightsStyle = (float) ($insights['summary']['style'] ?? 0);
        $phpstanErrors = (int) ($phpstan['totals']['file_errors'] ?? 0);

        $baseScore = $insightsCode * 0.25
            + $insightsComplexity * 0.25
            + $insightsArchitecture * 0.25
            + $insightsStyle * 0.25;

        $phpstanPenalty = min(30, $phpstanErrors * 0.3);
        $complexityPenalty = max(0, (100 - $insightsComplexity) * 0.2);

        $score = (int) round(max(0, min(100, $baseScore - $phpstanPenalty - $complexityPenalty)));

        $oversizedControllers = $taylorRules['oversized_controllers'] ?? [];
        $overloadedModels = $taylorRules['overloaded_models'] ?? [];
        $viewsWithEmbeddedPhp = $taylorRules['views_with_embedded_php'] ?? [];

        $metrics = [
            'insights_code' => $insightsCode,
            'insights_complexity' => $insightsComplexity,
            'insights_architecture' => $insightsArchitecture,
            'insights_style' => $insightsStyle,
            'phpstan_errors' => $phpstanErrors,
            'oversized_controllers' => count($oversizedControllers),
            'overloaded_models' => count($overloadedModels),
            'views_with_embedded_php' => count($viewsWithEmbeddedPhp),
        ];

        return [
            'score' => $score,
            'metrics' => $metrics,
            'recommendations' => $this->recommendationsFor($metrics, [
                'oversized_controllers' => $oversizedControllers,
                'overloaded_models' => $overloadedModels,
                'views_with_embedded_php' => $viewsWithEmbeddedPhp,
            ]),
        ];
    }

    public static function ratingFor(int $score): string
    {
        foreach (self::RATING_BANDS as $band) {
            if ($score >= $band['min']) {
                return $band['label'];
            }
        }

        return 'Poor';
    }

    /**
     * @return array<int, array{min: int, max: int, label: string}>
     */
    public static function ratingBands(): array
    {
        $bands = [];
        $previousMin = 101;

        foreach (self::RATING_BANDS as $band) {
            $bands[] = ['min' => $band['min'], 'max' => $previousMin - 1, 'label' => $band['label']];
            $previousMin = $band['min'];
        }

        return $bands;
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @param  array<string, array<int, array<string, mixed>>>  $taylorRules
     * @return array<int, string>
     */
    protected function recommendationsFor(array $metrics, array $taylorRules): array
    {
        $recommendations = [];

        foreach ($this->worstOffenders($taylorRules['oversized_controllers'], 'lines', 1) as $controller) {
            $recommendations[] = "Controller {$controller['file']} has {$controller['lines']} lines; split it into smaller Action classes.";
        }

        foreach ($this->worstOffenders($taylorRules['overloaded_models'], 'methods', 1) as $model) {
            $recommendations[] = "Model {$model['file']} has {$model['methods']} public methods; move business logic into Services/Actions.";
        }

        foreach ($this->worstOffenders($taylorRules['views_with_embedded_php'], 'php_lines', 1) as $view) {
            $recommendations[] = "View {$view['file']} has ~{$view['php_lines']} lines of embedded PHP; move it into a component or view model.";
        }

        if ($metrics['phpstan_errors'] > 20) {
            $recommendations[] = "Fix the {$metrics['phpstan_errors']} errors reported by PHPStan; they're the biggest source of bug risk.";
        } elseif ($metrics['phpstan_errors'] > 0) {
            $recommendations[] = "Fix the {$metrics['phpstan_errors']} errors reported by PHPStan.";
        }

        if ($metrics['insights_complexity'] < 70) {
            $recommendations[] = 'Reduce cyclomatic complexity: split long methods and avoid deep nesting.';
        }

        if ($metrics['insights_architecture'] < 70) {
            $recommendations[] = 'Review the architecture: move business logic into Services/Actions instead of Controllers/Models.';
        }

        if ($metrics['insights_style'] < 70) {
            $recommendations[] = 'Apply a code style formatter (Laravel Pint) to follow coding conventions.';
        }

        if ($metrics['insights_code'] < 70) {
            $recommendations[] = 'Improve overall code quality: check for duplication and dead code.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Nice work! No priority issues were found in this analysis.';
        }

        return array_slice($recommendations, 0, 5);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function worstOffenders(array $items, string $key, int $limit): array
    {
        usort($items, fn (array $a, array $b) => $b[$key] <=> $a[$key]);

        return array_slice($items, 0, $limit);
    }
}
