<?php

namespace JsTools;

use Illuminate\Support\ServiceProvider;

class JsToolsServiceProvider extends ServiceProvider
{
	public function boot(): void
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				\JsTools\Commands\ListJsFiles::class,
				\JsTools\Commands\PromoteJsFile::class,
				\JsTools\Commands\DemoteJsFile::class,
				\JsTools\Commands\MakeJsFile::class,
			]);
		}
	}
}