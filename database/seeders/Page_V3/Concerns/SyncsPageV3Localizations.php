<?php

namespace Database\Seeders\Page_V3\Concerns;

use App\Support\Database\JsonPageLocalizationManager;
use Illuminate\Support\Facades\File;
use RuntimeException;

trait SyncsPageV3Localizations
{
    protected function localizationDefinitionPath(): ?string
    {
        return null;
    }

    protected function syncPageV3Localizations(): void
    {
        $path = $this->localizationDefinitionPath();

        if ($path === null || trim($path) === '') {
            return;
        }

        if (! File::exists($path)) {
            throw new RuntimeException(sprintf('Page_V3 localization definition not found: %s', $path));
        }

        $definition = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($definition)) {
            throw new RuntimeException(sprintf('Invalid Page_V3 localization definition: %s', $path));
        }

        app(JsonPageLocalizationManager::class)->syncDefinitionLocalizations(
            $definition,
            $path,
            static::class
        );
    }
}
