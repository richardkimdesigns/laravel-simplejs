<?php

namespace JsTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PromoteJsFile extends Command
{
    protected $signature = 'js:promote {name}';
    protected $description = 'Convert a JS file from app.js import into a standalone Vite input';

    public function handle()
    {
        $name = $this->argument('name');
        $jsPath = "resources/js/{$name}.js";
        $viteConfigPath = base_path('vite.config.js');
        $appJsPath = resource_path('js/app.js');

        // Check file existence
        if (!File::exists(resource_path("js/{$name}.js"))) {
            $this->error("JS file does not exist: {$jsPath}");
            return 1;
        }

        // Remove import from app.js
        if (File::exists($appJsPath)) {
            $appJsContent = File::get($appJsPath);
            $importLine = "import './{$name}.js';";

            if (str_contains($appJsContent, $importLine)) {
                $updated = str_replace($importLine, '', $appJsContent);
                $updated = preg_replace("/\n{2,}/", "\n", $updated); // Clean extra blank lines
                File::put($appJsPath, trim($updated));
                $this->info("Removed import from app.js: {$importLine}");
            } else {
                $this->comment("No import found in app.js for './{$name}.js'");
            }
        }

        // Add to Vite input
        if (!File::exists($viteConfigPath)) {
            $this->error("vite.config.js not found.");
            return 1;
        }

        $viteContent = File::get($viteConfigPath);

        if (str_contains($viteContent, $jsPath)) {
            $this->comment("Already present in vite.config.js: {$jsPath}");
        } else {
            $viteContent = preg_replace_callback(
                "/input:\s*\[([^\]]*)\]/",
                fn($matches) => "input: [{$matches[1]}, '{$jsPath}']",
                $viteContent
            );

            File::put($viteConfigPath, $viteContent);
            $this->info("Added '{$jsPath}' as standalone input in vite.config.js");
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
