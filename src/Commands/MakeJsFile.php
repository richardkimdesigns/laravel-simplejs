<?php

namespace JsTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeJsFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:js {name} {--standalone : Register as separate Vite input instead of app.js import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a JS file in resources/js. Use --standalone for standalone JS files.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $standalone = $this->option('standalone');

        $jsFile = resource_path("js/{$name}.js");

        if (File::exists($jsFile)) {
            $this->error("File already exists: {$jsFile}");
            return 1;
        }

        File::put($jsFile, "// JS entry: {$name}.js\nconsole.log('{$name} loaded');");
        $this->info("JS file created: {$jsFile}");

        if ($standalone) {
            $this->addStandaloneViteInput($name);
        } else {
            $this->importIntoAppJs($name);
        }

        return 0;
    }

    protected function importIntoAppJs(string $name)
    {
        $appJs = resource_path('js/app.js');

        if (!File::exists($appJs)) {
            File::put($appJs, '');
        }

        $content = File::get($appJs);
        $importLine = "import './{$name}.js';";

        if (!str_contains($content, $importLine)) {
            File::append($appJs, "\n" . $importLine);
            $this->info("Imported './{$name}.js' into app.js");
        } else {
            $this->comment("'./{$name}.js' already imported in app.js");
        }
    }

    protected function addStandaloneViteInput(string $name)
    {
        $vitePath = base_path('vite.config.js');

        if (!File::exists($vitePath)) {
            $this->warn("vite.config.js not found.");
            return;
        }

        $viteContent = File::get($vitePath);
        $jsPath = "resources/js/{$name}.js";

        if (str_contains($viteContent, $jsPath)) {
            $this->comment("Already registered in vite.config.js");
            return;
        }

        // Add to input array
        $viteContent = preg_replace_callback(
            "/input:\s*\[([^\]]*)\]/",
            fn($matches) => "input: [{$matches[1]}, '{$jsPath}']",
            $viteContent
        );

        File::put($vitePath, $viteContent);
        $this->info("Added '{$jsPath}' as a standalone Vite input.");
    }
}
