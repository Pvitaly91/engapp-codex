<?php

namespace App\Modules\GitDeployment\Services;

use Symfony\Component\Process\Process;

class DeploymentGitRefProbe
{
    public function currentHeadCommit(): ?string
    {
        return $this->resolveCommit('HEAD');
    }

    public function resolveCommit(string $ref): ?string
    {
        $normalizedRef = trim($ref);

        if ($normalizedRef === '') {
            return null;
        }

        $result = $this->runGitProcess(['rev-parse', '--verify', $normalizedRef . '^{commit}']);

        if ($result['exit_code'] !== 0) {
            return null;
        }

        $commit = trim($result['stdout']);

        return $commit !== '' ? $commit : null;
    }

    public function commitExists(string $commit): bool
    {
        $normalizedCommit = trim($commit);

        if ($normalizedCommit === '') {
            return false;
        }

        $result = $this->runGitProcess(['cat-file', '-e', $normalizedCommit . '^{commit}']);

        return $result['exit_code'] === 0;
    }

    public function remoteBranchSha(string $branch): ?string
    {
        $normalizedBranch = trim($branch);

        if ($normalizedBranch === '') {
            return null;
        }

        $result = $this->runGitProcess(['ls-remote', '--exit-code', 'origin', 'refs/heads/' . $normalizedBranch]);

        if ($result['exit_code'] !== 0) {
            return null;
        }

        $output = trim($result['stdout']);

        if ($output === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $output);
        $sha = trim((string) ($parts[0] ?? ''));

        return $sha !== '' ? $sha : null;
    }

    /**
     * @param  list<string>  $arguments
     * @return array{stdout:string,stderr:string,exit_code:int}
     */
    protected function runGitProcess(array $arguments): array
    {
        $process = new Process(array_merge(['git'], $arguments), base_path());
        $process->setTimeout(30);
        $process->run();

        return [
            'stdout' => trim($process->getOutput()),
            'stderr' => trim($process->getErrorOutput()),
            'exit_code' => (int) $process->getExitCode(),
        ];
    }
}
