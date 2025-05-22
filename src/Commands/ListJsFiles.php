<?php

namespace JsTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListJsFiles extends Command
{
    protected $signature = 'js:list {--json} {--unused}';
    protected $description = 'List JS/TS files and where they are used (Blade, Vue, or across repo)';

    public function handle()
    {
        $jsRoot = resource_path('js');
        $allJsFiles = collect(File::allFiles($jsRoot))
            ->filter(fn($file) => in_array($file->getExtension(), ['js', 'ts']))
            ->map(fn($file) => str_replace($jsRoot . '/', '', $file->getRealPath()))
            ->values();

        $searchRoots = [
            resource_path('views'),
            resource_path('js'),
            base_path('resources'),
            base_path('app'),
            base_path('routes'),
        ];

        $fileUsages = [];

        foreach ($allJsFiles as $file) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            $regexPatterns = [
                preg_quote("resources/js/{$file}", '/'),
                preg_quote("./{$file}", '/'),
                preg_quote($file, '/'),
                preg_quote("{$basename}.js", '/'),
                preg_quote("{$basename}.ts", '/'),
            ];

            $matchingPaths = collect($searchRoots)
                ->flatMap(fn($dir) => File::allFiles($dir))
                ->filter(fn($f) => in_array($f->getExtension(), ['php', 'js', 'ts', 'vue', 'blade.php']))
                ->filter(function ($scanFile) use ($regexPatterns) {
                    $contents = File::get($scanFile->getRealPath());
                    foreach ($regexPatterns as $pattern) {
                        if (preg_match("/{$pattern}/i", $contents)) {
                            return true;
                        }
                    }
                    return false;
                })
                ->map(fn($match) => str_replace(base_path() . '/', '', $match->getRealPath()))
                ->values();

            $fileUsages[] = [
                'file' => $file,
                'status' => $this->getStatus($file, $matchingPaths),
                'used_in' => $matchingPaths->toArray(),
            ];
        }

        if ($this->option('unused')) {
            $fileUsages = collect($fileUsages)->filter(fn($entry) => $entry['status'] === 'Unused')->values();
        }

        if ($this->option('json')) {
            $this->line(json_encode($fileUsages, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->table(
            ['📦 JS File', 'Status', 'Used In'],
            collect($fileUsages)->map(fn($f) => [
                $f['file'],
                $f['status'],
                $f['used_in'] ? implode("\n", $f['used_in']) : '—',
            ])
        );

        return 0;
    }

    protected function getStatus(string $file, $matches): string
    {
        $viteConfig = File::exists(base_path('vite.config.js')) ? File::get(base_path('vite.config.js')) : '';
        $appJs = File::exists(resource_path('js/app.js')) ? File::get(resource_path('js/app.js')) : '';

        $inVite = str_contains($viteConfig, "resources/js/{$file}");
        $inApp = str_contains($appJs, "./{$file}");

        return $inVite ? 'Standalone'
             : ($inApp ? 'Imported'
             : ($matches->count() > 0 ? 'Referenced' : 'Unused'));
    }
}
