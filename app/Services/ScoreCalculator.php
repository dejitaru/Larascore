<?php

namespace App\Services;

class ScoreCalculator
{
    /**
     * @return array{score: int, metrics: array<string, mixed>, recommendations: array<int, string>}
     */
    public function calculate(?array $phpstan, ?array $insights, ?array $taylorRules = null): array
    {
        // Estructura asumida del JSON de PHP Insights (summary.*) y PHPStan (totals.file_errors);
        // ajustar estas rutas si el formato real difiere al probar contra un análisis real.
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

    /**
     * @param  array<string, mixed>  $metrics
     * @param  array<string, array<int, array<string, mixed>>>  $taylorRules
     * @return array<int, string>
     */
    protected function recommendationsFor(array $metrics, array $taylorRules): array
    {
        $recommendations = [];

        foreach ($this->worstOffenders($taylorRules['oversized_controllers'], 'lines', 1) as $controller) {
            $recommendations[] = "El controller {$controller['file']} tiene {$controller['lines']} líneas; divídelo en Action classes más pequeñas.";
        }

        foreach ($this->worstOffenders($taylorRules['overloaded_models'], 'methods', 1) as $model) {
            $recommendations[] = "El modelo {$model['file']} tiene {$model['methods']} métodos públicos; mueve lógica de negocio a Services/Actions.";
        }

        foreach ($this->worstOffenders($taylorRules['views_with_embedded_php'], 'php_lines', 1) as $view) {
            $recommendations[] = "La vista {$view['file']} tiene ~{$view['php_lines']} líneas de PHP embebido; múevelas a un componente o view model.";
        }

        if ($metrics['phpstan_errors'] > 20) {
            $recommendations[] = "Resuelve los {$metrics['phpstan_errors']} errores reportados por PHPStan; son la mayor fuente de riesgo de bugs.";
        } elseif ($metrics['phpstan_errors'] > 0) {
            $recommendations[] = "Corrige los {$metrics['phpstan_errors']} errores reportados por PHPStan.";
        }

        if ($metrics['insights_complexity'] < 70) {
            $recommendations[] = 'Reduce la complejidad ciclomática: divide métodos largos y evita anidamiento profundo.';
        }

        if ($metrics['insights_architecture'] < 70) {
            $recommendations[] = 'Revisa la arquitectura: separa lógica de negocio en Services/Actions en vez de Controllers/Models.';
        }

        if ($metrics['insights_style'] < 70) {
            $recommendations[] = 'Aplica un formateador de estilo (Laravel Pint) para cumplir con las convenciones de código.';
        }

        if ($metrics['insights_code'] < 70) {
            $recommendations[] = 'Mejora la calidad general del código: revisa duplicación y código muerto.';
        }

        if (empty($recommendations)) {
            $recommendations[] = '¡Buen trabajo! No se detectaron problemas prioritarios en este análisis.';
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
