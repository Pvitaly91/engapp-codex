<?php

namespace App\Support\Console;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class CodexPromptConsolePresenter
{
    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<int, array<string, mixed>>  $prompts
     */
    public function renderHumanOutput(string $headline, array $sections, array $prompts): string
    {
        $lines = [
            $headline,
            str_repeat('=', max(12, strlen($headline))),
        ];

        foreach ($sections as $section) {
            $sectionLines = array_values(array_filter(
                array_map(
                    static fn (mixed $line): ?string => filled($line) ? (string) $line : null,
                    (array) ($section['lines'] ?? [])
                )
            ));

            if ($sectionLines === []) {
                continue;
            }

            $lines[] = '';
            $lines[] = (string) ($section['title'] ?? 'Section');
            $lines[] = str_repeat('-', max(8, strlen((string) ($section['title'] ?? 'Section'))));

            foreach ($sectionLines as $line) {
                $lines[] = $line;
            }
        }

        foreach (array_values($prompts) as $index => $prompt) {
            $lines[] = '';
            $lines[] = $this->renderPromptCard((array) $prompt, $index + 1);
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $prompt
     */
    public function renderPromptCard(array $prompt, int $position): string
    {
        $title = trim((string) ($prompt['title'] ?? 'Generated prompt'));
        $key = trim((string) ($prompt['key'] ?? 'prompt'));
        $promptIdText = trim((string) ($prompt['prompt_id_text'] ?? ''));
        $summaryTopText = trim((string) ($prompt['summary_top_text'] ?? ''));
        $summaryBottomText = trim((string) ($prompt['summary_bottom_text'] ?? ''));
        $promptText = trim((string) ($prompt['text'] ?? ''));

        $lines = [
            str_repeat('=', 72),
            sprintf('Prompt Card %d: %s', $position, $title),
            'Key: ' . $key,
        ];

        if ($promptIdText !== '') {
            $lines[] = $promptIdText;
        }

        if ($summaryTopText !== '') {
            $lines[] = '';
            $lines[] = $summaryTopText;
        }

        if ($promptText !== '') {
            $lines[] = '';
            $lines[] = 'Wrapped Prompt Text:';
            $lines[] = $promptText;
        }

        if ($summaryBottomText !== '') {
            $lines[] = '';
            $lines[] = $summaryBottomText;
        }

        if ($promptIdText !== '') {
            $lines[] = '';
            $lines[] = $promptIdText;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function renderJsonOutput(array $payload): string
    {
        return (string) json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    public function resolvePath(mixed $value): ?string
    {
        $path = trim((string) ($value ?? ''));

        if ($path === '') {
            return null;
        }

        $resolved = $this->isAbsolutePath($path) ? $path : base_path($path);
        $this->assertNotPublicPath($resolved);

        return $resolved;
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    public function existingPaths(array $paths): array
    {
        return array_values(array_filter($paths, static fn (string $path): bool => File::exists($path)));
    }

    public function writeFile(string $path, string $contents): void
    {
        $this->assertNotPublicPath($path);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    /**
     * @param  array<int, array<string, mixed>>  $prompts
     * @return array<int, string>
     */
    public function plannedPromptFilePaths(string $directory, array $prompts, string $extension = 'txt'): array
    {
        $this->assertNotPublicPath($directory);

        return array_map(
            fn (array $prompt, int $index): string => $directory . DIRECTORY_SEPARATOR . $this->promptFileName($prompt, $index, $extension),
            array_values($prompts),
            array_keys(array_values($prompts))
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $prompts
     * @return array<int, array{path:string,relative_path:string,key:string,prompt_id:string}>
     */
    public function writePromptFiles(string $directory, array $prompts, string $extension = 'txt'): array
    {
        $paths = $this->plannedPromptFilePaths($directory, $prompts, $extension);
        File::ensureDirectoryExists($directory);

        $written = [];

        foreach (array_values($prompts) as $index => $prompt) {
            $path = $paths[$index];
            $contents = (string) ($prompt['text'] ?? '');
            $this->writeFile($path, $contents);
            $written[] = [
                'path' => $path,
                'relative_path' => $this->relativePath($path),
                'key' => (string) ($prompt['key'] ?? 'prompt'),
                'prompt_id' => (string) ($prompt['prompt_id'] ?? ''),
            ];
        }

        return $written;
    }

    public function relativePath(string $absolutePath): string
    {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim((string) Str::after($normalizedPath, $normalizedRoot), '/');
    }

    /**
     * @param  array<string, mixed>  $prompt
     */
    private function promptFileName(array $prompt, int $index, string $extension): string
    {
        $key = Str::slug((string) ($prompt['key'] ?? 'prompt'));
        $promptId = Str::slug((string) ($prompt['prompt_id'] ?? 'no-prompt-id'));
        $normalizedExtension = ltrim(trim($extension), '.');

        return sprintf(
            '%02d-%s-%s.%s',
            $index + 1,
            $key !== '' ? $key : 'prompt',
            $promptId !== '' ? $promptId : 'no-prompt-id',
            $normalizedExtension !== '' ? $normalizedExtension : 'txt'
        );
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:[\\\\\\/]|\\\\\\\\)/', $path) === 1;
    }

    private function assertNotPublicPath(string $path): void
    {
        $normalizedPath = rtrim(str_replace('\\', '/', $path), '/');
        $normalizedPublic = rtrim(str_replace('\\', '/', public_path()), '/');

        if ($normalizedPath === $normalizedPublic || str_starts_with($normalizedPath, $normalizedPublic . '/')) {
            throw new RuntimeException('Writing generated prompt output into public paths is not supported.');
        }
    }
}
