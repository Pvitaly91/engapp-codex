<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOperationsDoctorService;
use Illuminate\Console\Command;
use Throwable;

class ContentDoctorCommand extends Command
{
    protected $signature = 'content:doctor
        {--json : Output the ContentOps readiness report as JSON}
        {--write-report : Write a Markdown report into storage/app/content-doctor-reports}
        {--strict : Treat warnings as a failing readiness result}
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--with-git : Include read-only current git ref probing}
        {--with-artifacts : Validate recent content operation history artifacts}
        {--with-deployment : Validate GitDeployment ContentOps route/service wiring}
        {--with-package-roots : Validate V3/Page_V3 package roots}
        {--with-dry-plan : Run a harmless read-only changed-content working-tree plan}';

    protected $description = 'Run read-only ContentOps readiness diagnostics for changed-content deployment operations.';

    public function __construct(
        private readonly ContentOperationsDoctorService $contentOperationsDoctorService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->contentOperationsDoctorService->run([
                'domains' => $this->option('domains'),
                'strict' => (bool) $this->option('strict'),
                'with_git' => (bool) $this->option('with-git'),
                'with_artifacts' => (bool) $this->option('with-artifacts'),
                'with_deployment' => (bool) $this->option('with-deployment'),
                'with_package_roots' => (bool) $this->option('with-package-roots'),
                'with_dry_plan' => (bool) $this->option('with-dry-plan'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($exception);
        }

        if ($this->option('write-report')) {
            try {
                $path = $this->contentOperationsDoctorService->writeReport($result);
                $result['artifacts']['report_path'] = storage_path('app/' . $path);
            } catch (Throwable $exception) {
                $result['checks'][] = [
                    'code' => 'content_doctor_report_write',
                    'label' => 'ContentOps doctor report',
                    'status' => 'fail',
                    'message' => 'Unable to write ContentOps doctor report: ' . $exception->getMessage(),
                    'meta' => ['exception_class' => $exception::class],
                    'recommendation' => 'Fix storage/app permissions and rerun content:doctor --write-report.',
                ];
                $result['summary'] = $this->summary((array) ($result['checks'] ?? []));
                $result['overall_status'] = 'fail';
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCode($result);
        }

        $this->renderHumanOutput($result);

        return $this->exitCode($result);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        $this->line('Command: content:doctor');
        $this->line('Overall Status: ' . (string) ($result['overall_status'] ?? 'fail'));
        $this->line('Domains: ' . implode(', ', (array) data_get($result, 'options.domains', [])));
        $this->line(sprintf(
            'Summary: total=%d; pass=%d; warn=%d; fail=%d',
            (int) data_get($result, 'summary.total', 0),
            (int) data_get($result, 'summary.pass', 0),
            (int) data_get($result, 'summary.warn', 0),
            (int) data_get($result, 'summary.fail', 0)
        ));

        if (is_array($result['error'] ?? null)) {
            $this->newLine();
            $this->error((string) data_get($result, 'error.message', 'ContentOps doctor failed.'));
        }

        $this->newLine();

        foreach ((array) ($result['checks'] ?? []) as $check) {
            $check = (array) $check;
            $status = strtoupper((string) ($check['status'] ?? 'fail'));
            $this->line(sprintf(
                '[%s] %s - %s',
                $status,
                (string) ($check['code'] ?? 'unknown'),
                (string) ($check['message'] ?? '')
            ));

            if (! empty($check['recommendation'])) {
                $this->line('  recommendation: ' . (string) $check['recommendation']);
            }
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCode(array $result): int
    {
        if (is_array($result['error'] ?? null)) {
            return self::FAILURE;
        }

        if ((int) data_get($result, 'summary.fail', 0) > 0) {
            return self::FAILURE;
        }

        if ((bool) data_get($result, 'options.strict', false) && (int) data_get($result, 'summary.warn', 0) > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResult(Throwable $exception): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'overall_status' => 'fail',
            'summary' => [
                'total' => 0,
                'pass' => 0,
                'warn' => 0,
                'fail' => 1,
            ],
            'options' => [
                'domains' => [],
                'strict' => (bool) $this->option('strict'),
                'with_git' => (bool) $this->option('with-git'),
                'with_artifacts' => (bool) $this->option('with-artifacts'),
                'with_deployment' => (bool) $this->option('with-deployment'),
                'with_package_roots' => (bool) $this->option('with-package-roots'),
                'with_dry_plan' => (bool) $this->option('with-dry-plan'),
            ],
            'checks' => [
                [
                    'code' => 'content_doctor_exception',
                    'label' => 'ContentOps doctor',
                    'status' => 'fail',
                    'message' => $exception->getMessage(),
                    'meta' => ['exception_class' => $exception::class],
                    'recommendation' => 'Fix the reported ContentOps doctor error and rerun the command.',
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'content_doctor',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $checks
     * @return array<string, int>
     */
    private function summary(array $checks): array
    {
        return [
            'total' => count($checks),
            'pass' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'pass')),
            'warn' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'warn')),
            'fail' => count(array_filter($checks, static fn (array $check): bool => ($check['status'] ?? null) === 'fail')),
        ];
    }
}
