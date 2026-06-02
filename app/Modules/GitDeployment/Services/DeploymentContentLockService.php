<?php

namespace App\Modules\GitDeployment\Services;

use App\Services\ContentDeployment\ContentOperationLockService;

class DeploymentContentLockService
{
    public function __construct(
        private readonly ContentOperationLockService $contentOperationLockService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function reserve(array $preview, array $options = []): array
    {
        if (! (bool) ($options['requested'] ?? true)) {
            return $this->notRequiredPayload();
        }

        $lease = $this->contentOperationLockService->acquire(
            $this->lockContext($preview, $options),
            (bool) ($options['takeover_stale_lock'] ?? false)
        );

        return $this->reservationPayload($lease, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function previewStatus(bool $requiredForContentApply): array
    {
        $status = $this->contentOperationLockService->status();
        $currentStatus = (string) ($status['status'] ?? 'unknown');

        return [
            'required_for_content_apply' => $requiredForContentApply,
            'current_status' => $currentStatus,
            'blocked' => $requiredForContentApply && in_array($currentStatus, ['active', 'stale'], true),
            'stale_takeover_possible' => $requiredForContentApply && $currentStatus === 'stale',
            'takeover_requested' => false,
            'takeover_performed' => false,
            'snapshot' => $status['lock'] ?? null,
            'status' => $currentStatus,
            'warnings' => $currentStatus === 'stale' ? ['stale_lock_present'] : [],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $reservation
     */
    public function heartbeat(?array $reservation): void
    {
        $this->contentOperationLockService->heartbeat($this->ownerToken($reservation));
    }

    /**
     * @param  array<string, mixed>|null  $reservation
     */
    public function release(?array $reservation): void
    {
        $this->contentOperationLockService->release($this->ownerToken($reservation));
    }

    /**
     * @param  array<string, mixed>|null  $reservation
     * @return array<string, mixed>
     */
    public function applyOptions(?array $reservation): array
    {
        if (! is_array($reservation) || ! (bool) ($reservation['acquired'] ?? false)) {
            return [];
        }

        return [
            'content_lock_lease' => $reservation['lease'] ?? null,
            'release_content_lock' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function lockContext(array $preview, array $options): array
    {
        return [
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => (string) ($options['trigger_source'] ?? $this->triggerSource($preview)),
            'domains' => data_get($preview, 'deployment.scope.domains', ['v3', 'page-v3']),
            'content_operation_run_id' => $options['content_operation_run_id'] ?? null,
            'operator_user_id' => $options['operator_user_id'] ?? null,
            'meta' => array_merge((array) ($options['meta'] ?? []), [
                'deployment_lock_reservation' => true,
                'deployment_mode' => data_get($preview, 'deployment.mode'),
                'source_kind' => data_get($preview, 'deployment.source_kind'),
                'base_ref' => data_get($preview, 'deployment.base_ref'),
                'head_ref' => data_get($preview, 'deployment.head_ref'),
                'branch' => data_get($preview, 'deployment.branch'),
                'commit' => data_get($preview, 'deployment.commit'),
                'phase' => 'pre_code_update',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $lease
     * @return array<string, mixed>
     */
    private function reservationPayload(array $lease, bool $required): array
    {
        $reason = (string) data_get($lease, 'error.reason', '');
        $status = (string) ($lease['status'] ?? 'unknown');

        return [
            'required_for_content_apply' => $required,
            'acquired' => (bool) ($lease['acquired'] ?? false),
            'blocked' => ! (bool) ($lease['acquired'] ?? false),
            'current_status' => $status,
            'status' => $status,
            'owner_token' => $lease['owner_token'] ?? null,
            'stale_takeover_possible' => $reason === 'stale_lock_takeover_required' || $status === 'stale',
            'takeover_requested' => (bool) data_get($lease, 'takeover.requested', false),
            'takeover_performed' => (bool) data_get($lease, 'takeover.performed', false),
            'warnings' => array_values((array) ($lease['warnings'] ?? [])),
            'snapshot' => $lease['lock'] ?? null,
            'lock' => $lease['lock'] ?? null,
            'lease' => $lease,
            'error' => $lease['error'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function notRequiredPayload(): array
    {
        return [
            'required_for_content_apply' => false,
            'acquired' => false,
            'blocked' => false,
            'current_status' => 'not_required',
            'status' => 'not_required',
            'owner_token' => null,
            'stale_takeover_possible' => false,
            'takeover_requested' => false,
            'takeover_performed' => false,
            'warnings' => [],
            'snapshot' => null,
            'lock' => null,
            'lease' => null,
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $reservation
     */
    private function ownerToken(?array $reservation): ?string
    {
        $token = $reservation['owner_token'] ?? data_get($reservation, 'lease.owner_token');

        return is_string($token) && trim($token) !== '' ? $token : null;
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    private function triggerSource(array $preview): string
    {
        return (string) data_get($preview, 'deployment.mode') === 'native'
            ? 'native_deployment_ui'
            : 'deployment_ui';
    }
}
