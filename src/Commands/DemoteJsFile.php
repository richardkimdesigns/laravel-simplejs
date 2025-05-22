<?php

namespace JsTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DemoteJsFile extends Command
{
    protected $signature = 'js:demote {name}';
    protected $description = 'Convert a standalone JS file into one imported in app.js';

    public function handle()
    {
        $name = $this->argument('name');
        $jsPath = "resources/js/{$name}.js";
        $viteConfigPath = base_path('vite.config.js');
        $appJsPath = resource_path('js/app.js');

        // Ensure file exists
        if (!File::exists(resource_path("js/{$name}.js"))) {
            $this->error("JS file does not exist: {$jsPath}");
            return 1;
        }

        // Update vite.config.js
        if (!File::exists($viteConfigPath)) {
            $this->error("vite.config.js not found.");
            return 1;
        }

        $viteContent = File::get($viteConfigPath);

        // Remove file path from input array (with safe comma cleanup)
        $escapedPath = preg_quote($jsPath, '/');

        $viteContent = preg_replace(
            "/['\"]{$escapedPath}['\"],?\s*/",
            '',
            $viteContent
        );
        $viteContent = preg_replace('/,\s*]/', ']', $viteContent);
        $viteContent = preg_replace('/\[\s*,/', '[', $viteContent);

        File::put($viteConfigPath, $viteContent);
        $this->info("Removed '{$jsPath}' from vite.config.js");

        // Add import to app.js
        if (!File::exists($appJsPath)) {
            File::put($appJsPath, '');
        }

        $appJsContent = File::get($appJsPath);
        $importLine = "import './{$name}.js';";

        if (!str_contains($appJsContent, $importLine)) {
            File::append($appJsPath, "\n{$importLine}");
            $this->info("Added import to app.js: {$importLine}");
        } else {
            $this->comment("Already imported in app.js");
        }

        // Rebuild assets
        $this->info("Running: npm run build...");
        exec('npm run build 2>&1', $output, $status);
        $this->line(implode("\n", $output));

        if ($status !== 0) {
            $this->error('npm run build failed.');
            return 1;
        }

        $this->info('Build complete.');
        return 0;
    }
}
