<?php

namespace App\Console\Commands;

use App\Services\TheoryMenuBuilder;
use App\Services\TheoryMenuStore;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

final class BuildTheoryMenuCommand extends Command
{
    protected $signature = 'theory:menu-build
        {--locale= : One enabled language; otherwise build all enabled languages}
        {--check : Validate checksums of the active snapshots without rebuilding}
        {--rollback : Reactivate the previous healthy snapshot (requires --locale)}';

    protected $description = 'Build or verify the public theory navigation HTML snapshots';

    public function handle(TheoryMenuBuilder $builder, TheoryMenuStore $store): int
    {
        $locale = $this->option('locale');
        $check = (bool) $this->option('check');
        $rollback = (bool) $this->option('rollback');
        try {
            if (($check && $rollback) || ($rollback && $locale === null)) {
                throw new RuntimeException('Use --check or --rollback --locale=<enabled locale>.');
            }
            $context = $builder->context();
            if ($locale !== null && !in_array($locale, $context['locales'], true)) {
                throw new RuntimeException('Requested theory menu locale is not enabled in this site mode.');
            }
        } catch (Throwable $e) {
            $this->error('failed: '.$this->safeMessage($e));

            return self::FAILURE;
        }
        $mode = $context['mode'];
        $failed = false;
        foreach ($locale === null ? $context['locales'] : [$locale] as $language) {
            $start = hrtime(true);
            try {
                $manifest = $check ? $store->check($mode, $language)
                    : ($rollback ? $store->rollback($mode, $language) : $builder->build($language));
                if ($manifest === null) throw new RuntimeException('Active theory menu snapshot is missing or invalid.');
                $files = [];
                foreach ($manifest['files'] as $variant => $file) $files[] = $variant.'='.$file['bytes'].' B';
                $this->info(sprintf('%s %s: %s revision=%s categories=%d lessons=%d %s elapsed=%.2f ms',
                    $mode, $language, $manifest['status'] ?? 'unchanged', $manifest['revision'],
                    $manifest['counts']['categories'], $manifest['counts']['lessons'], implode(' ', $files), (hrtime(true) - $start) / 1e6));
            } catch (Throwable $e) {
                $failed = true;
                $this->error(sprintf('%s %s: failed (%s; %.2f ms); active snapshot was not replaced by this failure.',
                    $mode, $language, $this->safeMessage($e), (hrtime(true) - $start) / 1e6));
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function safeMessage(Throwable $error): string
    {
        // Database/Blade exceptions can contain SQL, internal paths or source data.
        return get_class($error) === RuntimeException::class ? $error->getMessage()
            : 'Theory menu generation failed ('.$error::class.').';
    }
}
