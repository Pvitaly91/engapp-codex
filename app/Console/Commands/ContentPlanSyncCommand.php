<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentSyncPlanService;
use Illuminate\Console\Command;
use Throwable;

class ContentPlanSyncCommand extends Command
{
    protected $signature = 'content:plan-sync
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--json : Output the content sync repair plan as JSON}
        {--write-report : Write a Markdown report into storage/app/content-sync-plans}
        {--with-release-check : Add release-check summaries for drifted initialized domains}
        {--check-profile=release : scaffold | release}
        {--strict : Treat planner warnings and bootstrap-required states as failures}';

    protected $description = 'Plan content drift reconciliation from persisted per-domain content sync refs to the current deployed code ref.';

    public function __construct(
        private readonly ContentSyncPlanService $contentSyncPlanService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->contentSyncPlanService->run([
                'domains' => $this->option('domains'),
                'with_release_check' => (bool) $this->option('with-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($exception);
        }

        if ($this->option('write-report')) {
            $path = $this->contentSyncPlanService->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return empty($result['error']) ? self::SUCCESS : self::FAILURE;
        }

        $this->renderHumanOutput($result);

        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        if (! empty($result['error']['message'])) {
            $this->error((string) $result['error']['message']);
            $this->newLine();
        }

        $this->line('Command: content:plan-sync');
        $this->line('Current Deployed Ref: ' . (string) (($result['deployment_refs']['current_deployed_ref'] ?? null) ?: 'unavailable'));
        $this->line('Domains: ' . implode(', ', array_keys((array) ($result['domains'] ?? []))));
        $this->newLine();

        foreach ((array) ($result['domains'] ?? []) as $domain => $domainState) {
            $domainState = (array) $domainState;
            $this->line(sprintf(
                '[%s] status=%s | sync_ref=%s | current_deployed_ref=%s | bootstrap_required=%s',
                $domain,
                (string) ($domainState['status'] ?? 'uninitialized'),
                (string) (($domainState['sync_state_ref'] ?? null) ?: '—'),
                (string) (($domainState['current_deployed_ref'] ?? null) ?: '—'),
                ((bool) ($domainState['bootstrap_required'] ?? false)) ? 'true' : 'false'
            ));
            $this->line(sprintf(
                '  effective_base=%s | last_attempt_status=%s | last_attempted_at=%s',
                (string) (($domainState['effective_base_ref'] ?? null) ?: '—'),
                (string) (($domainState['last_attempted_status'] ?? null) ?: '—'),
                (string) (($domainState['last_attempted_at'] ?? null) ?: '—')
            ));
        }

        $this->newLine();
        $this->line(sprintf(
            'Summary: synced-domains=%d; drifted-domains=%d; uninitialized-domains=%d; changed=%d; deleted-cleanup=%d; seed=%d; refresh=%d; blocked=%d; warnings=%d',
            (int) ($result['summary']['synced_domains'] ?? 0),
            (int) ($result['summary']['drifted_domains'] ?? 0),
            (int) ($result['summary']['uninitialized_domains'] ?? 0),
            (int) ($result['summary']['changed_packages'] ?? 0),
            (int) ($result['summary']['deleted_cleanup_candidates'] ?? 0),
            (int) ($result['summary']['seed_candidates'] ?? 0),
            (int) ($result['summary']['refresh_candidates'] ?? 0),
            (int) ($result['summary']['blocked'] ?? 0),
            (int) ($result['summary']['warnings'] ?? 0)
        ));

        $this->newLine();
        $this->line('Deleted Packages Cleanup Phase');
        $this->renderPackageLines((array) ($result['content_plan']['phases']['cleanup_deleted'] ?? []));
        $this->newLine();
        $this->line('Current Packages Upsert Phase');
        $this->renderPackageLines((array) ($result['content_plan']['phases']['upsert_present'] ?? []));

        $this->newLine();
        $this->line('Next Steps:');
        $this->line('php artisan content:apply-sync --dry-run' . $this->suffixForResult($result));
        if ((array) ($result['bootstrap']['required_domains'] ?? []) !== []) {
            $this->line('php artisan content:apply-sync --dry-run --bootstrap-uninitialized' . $this->suffixForResult($result));
        }
        $this->line('php artisan content:apply-sync --force' . $this->suffixForResult($result));

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     */
    private function renderPackageLines(array $packages): void
    {
        if ($packages === []) {
            $this->line('- None.');

            return;
        }

        foreach ($packages as $package) {
            $this->line(sprintf(
                '- [%s] %s | type=%s | state=%s | action=%s',
                (string) ($package['domain'] ?? 'unknown'),
                (string) (($package['relative_path'] ?? null) ?: ($package['package_key'] ?? '')),
                (string) ($package['package_type'] ?? 'unknown'),
                (string) ($package['package_state'] ?? 'unknown'),
                (string) ($package['recommended_action'] ?? 'skip')
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function suffixForResult(array $result): string
    {
        $parts = [];
        $domains = array_keys((array) ($result['domains'] ?? []));

        if ($domains !== ['v3', 'page-v3']) {
            $parts[] = '--domains=' . implode(',', $domains);
        }

        if ((bool) ($result['options']['with_release_check'] ?? false)) {
            $parts[] = '--with-release-check';
        }

        if (($result['options']['check_profile'] ?? 'release') !== 'release') {
            $parts[] = '--check-profile=' . (string) $result['options']['check_profile'];
        }

        if ((bool) ($result['options']['strict'] ?? false)) {
            $parts[] = '--strict';
        }

        return $parts === [] ? '' : ' ' . implode(' ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResult(Throwable $exception): array
    {
        return [
            'deployment_refs' => [
                'current_deployed_ref' => null,
            ],
            'domains' => [],
            'summary' => [
                'synced_domains' => 0,
                'drifted_domains' => 0,
                'uninitialized_domains' => 0,
                'blocked' => 0,
                'warnings' => 0,
                'changed_packages' => 0,
                'deleted_cleanup_candidates' => 0,
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
            ],
            'content_plan' => [
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
            ],
            'bootstrap' => [
                'required_domains' => [],
            ],
            'options' => [
                'domains' => [],
                'with_release_check' => (bool) $this->option('with-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'sync_plan',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }
}
