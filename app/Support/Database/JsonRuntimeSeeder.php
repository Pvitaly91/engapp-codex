<?php

namespace App\Support\Database;

class JsonRuntimeSeeder extends JsonTestSeeder
{
    public function __construct(
        private readonly string $path,
        private readonly ?string $forcedSeederClass = null,
    ) {
    }

    protected function definitionPath(): string
    {
        return $this->path;
    }

    public function readDefinition(): array
    {
        return $this->loadDefinitionFromFile($this->path);
    }

    public function seedFile(): void
    {
        $definition = $this->readDefinition();
        $seederClass = $this->forcedSeederClass ?? $this->resolveSeederClassName($definition);

        $this->seedDefinition($definition, $seederClass);
    }
}
