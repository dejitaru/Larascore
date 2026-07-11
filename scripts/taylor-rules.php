<?php

/**
 * Escanea un repo Laravel clonado en busca de violaciones simples de las reglas
 * "Taylor-style" (ver proyect.md, sección 5). Corre standalone en el runner de
 * GitHub Actions, sin autoload de Composer ni bootstrap de Laravel.
 *
 * Uso: php taylor-rules.php /ruta/al/repo/clonado
 */

const CONTROLLER_MAX_LINES = 300;
const MODEL_MAX_METHODS = 20;
const VIEW_MAX_EMBEDDED_PHP_LINES = 20;

$root = $argv[1] ?? null;

if (! $root || ! is_dir($root)) {
    fwrite(STDERR, "Uso: php taylor-rules.php <ruta-repo>\n");
    exit(1);
}

$root = rtrim($root, '/');

/**
 * @return array<int, string>
 */
function phpFilesIn(string $dir): array
{
    if (! is_dir($dir)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function relativePath(string $root, string $path): string
{
    return ltrim(str_replace($root, '', $path), '/');
}

// Cuenta "public function" de forma aproximada por regex (sin distinguir relaciones
// Eloquent de lógica de negocio real); excluye métodos mágicos (__construct, etc.).
function countPublicMethods(string $code): int
{
    preg_match_all('/\bpublic\s+function\s+(\w+)\s*\(/', $code, $matches);

    return count(array_filter($matches[1], fn (string $name) => ! str_starts_with($name, '__')));
}

// Cuenta líneas dentro de bloques de PHP embebido (tag largo) y @php...@endphp de un archivo Blade.
function countEmbeddedPhpLines(string $contents): int
{
    preg_match_all('/<\?php.*?\?>|@php.*?@endphp/s', $contents, $matches);

    $lines = 0;
    foreach ($matches[0] as $block) {
        $lines += substr_count($block, "\n") + 1;
    }

    return $lines;
}

$oversizedControllers = [];
foreach (phpFilesIn($root . '/app/Http/Controllers') as $file) {
    $lineCount = count(file($file));

    if ($lineCount > CONTROLLER_MAX_LINES) {
        $oversizedControllers[] = [
            'file' => relativePath($root, $file),
            'lines' => $lineCount,
        ];
    }
}

$overloadedModels = [];
foreach (phpFilesIn($root . '/app/Models') as $file) {
    $methodCount = countPublicMethods(file_get_contents($file));

    if ($methodCount > MODEL_MAX_METHODS) {
        $overloadedModels[] = [
            'file' => relativePath($root, $file),
            'methods' => $methodCount,
        ];
    }
}

$viewsWithEmbeddedPhp = [];
$viewsDir = $root . '/resources/views';

if (is_dir($viewsDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $embeddedLines = countEmbeddedPhpLines(file_get_contents($file->getPathname()));

        if ($embeddedLines > VIEW_MAX_EMBEDDED_PHP_LINES) {
            $viewsWithEmbeddedPhp[] = [
                'file' => relativePath($root, $file->getPathname()),
                'php_lines' => $embeddedLines,
            ];
        }
    }
}

echo json_encode([
    'oversized_controllers' => $oversizedControllers,
    'overloaded_models' => $overloadedModels,
    'views_with_embedded_php' => $viewsWithEmbeddedPhp,
], JSON_PRETTY_PRINT);
