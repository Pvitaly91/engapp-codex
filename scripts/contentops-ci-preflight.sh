#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

DOMAINS="${CONTENTOPS_DOMAINS:-v3,page-v3}"
BASE_REF="${CONTENTOPS_BASE_REF:-origin/main}"
HEAD_REF="${CONTENTOPS_HEAD_REF:-HEAD}"
PROFILE="${CONTENTOPS_PROFILE:-ci}"
WITH_RELEASE_CHECK="${CONTENTOPS_WITH_RELEASE_CHECK:-true}"
STRICT="${CONTENTOPS_STRICT:-true}"
TARGET_SHA="${CONTENTOPS_TARGET_SHA:-}"
BOOTSTRAP_SYNC_STATE="${CONTENTOPS_CI_BOOTSTRAP_SYNC_STATE:-true}"
SYNC_REF="${CONTENTOPS_CI_SYNC_REF:-}"
OUTPUT_DIR="storage/app/contentops-ci"
DOCTOR_JSON="${OUTPUT_DIR}/content-doctor.json"
GATE_JSON="${OUTPUT_DIR}/content-release-gate.json"
SUMMARY_MD="${OUTPUT_DIR}/summary.md"

mkdir -p "$OUTPUT_DIR"
mkdir -p \
  storage/app/content-changed-plans \
  storage/app/content-changed-apply-reports \
  storage/app/content-doctor-reports \
  storage/app/content-ci-status \
  storage/app/content-lock-status \
  storage/app/content-operation-history \
  storage/app/content-operation-replays \
  storage/app/content-operation-runs \
  storage/app/content-release-gates \
  storage/app/content-sync-apply-reports \
  storage/app/content-sync-plans \
  storage/app/content-sync-status \
  storage/app/deployment-content-apply-reports

echo "ContentOps CI preflight"
echo "domains=${DOMAINS}"
echo "base_ref=${BASE_REF}"
echo "head_ref=${HEAD_REF}"
echo "profile=${PROFILE}"
echo "with_release_check=${WITH_RELEASE_CHECK}"
echo "strict=${STRICT}"
if [[ -n "$TARGET_SHA" ]]; then
  echo "target_sha=${TARGET_SHA}"
fi

git rev-parse --verify "${BASE_REF}^{commit}" >/dev/null
git rev-parse --verify "${HEAD_REF}^{commit}" >/dev/null

if [[ -n "$TARGET_SHA" ]]; then
  CURRENT_SHA="$(git rev-parse HEAD)"
  if [[ "$CURRENT_SHA" != "$TARGET_SHA" ]]; then
    echo "Checked-out HEAD (${CURRENT_SHA}) does not match requested target_sha (${TARGET_SHA})." >&2
    exit 1
  fi
fi

php artisan migrate --force

if [[ "${BOOTSTRAP_SYNC_STATE}" == "true" ]]; then
  if [[ -z "$SYNC_REF" ]]; then
    SYNC_REF="$(git rev-parse "${HEAD_REF}^{commit}")"
  fi

  CONTENTOPS_DOMAINS="$DOMAINS" CONTENTOPS_CI_SYNC_REF="$SYNC_REF" php artisan tinker --execute='
    $domains = array_values(array_filter(array_unique(array_map(
        static fn (string $domain): string => str_replace("_", "-", strtolower(trim($domain))),
        explode(",", (string) getenv("CONTENTOPS_DOMAINS"))
    ))));
    $ref = trim((string) getenv("CONTENTOPS_CI_SYNC_REF"));
    foreach ($domains as $domain) {
        if (! in_array($domain, ["v3", "page-v3"], true)) {
            continue;
        }
        \App\Models\ContentSyncState::query()->updateOrCreate(
            ["domain" => $domain],
            [
                "last_successful_ref" => $ref,
                "last_successful_applied_at" => now(),
                "last_attempted_base_ref" => $ref,
                "last_attempted_head_ref" => $ref,
                "last_attempted_status" => "success",
                "last_attempted_at" => now(),
                "last_attempt_meta" => ["ci_bootstrap" => true],
            ]
        );
    }
  '
fi

TEST_FILES=(
  tests/Feature/ContentReleaseGateCommandTest.php
  tests/Unit/ContentReleaseGateServiceTest.php
  tests/Feature/ContentCiStatusCommandTest.php
  tests/Unit/ContentOpsCiStatusServiceTest.php
  tests/Feature/ContentCiDispatchCommandTest.php
  tests/Unit/ContentOpsCiDispatchServiceTest.php
  tests/Feature/ContentDoctorCommandTest.php
  tests/Unit/ContentOperationsDoctorServiceTest.php
  tests/Feature/ContentLockStatusCommandTest.php
  tests/Feature/ContentSyncStatusCommandTest.php
  tests/Feature/ContentHistoryCommandTest.php
  tests/Feature/DeploymentContentReleaseGateTest.php
  tests/Feature/DeploymentContentCiStatusTest.php
  tests/Feature/DeploymentContentCiDispatchTest.php
  tests/Feature/DeploymentContentDoctorTest.php
  tests/Feature/DeploymentContentLockGateTest.php
  tests/Feature/DeploymentContentGateTest.php
)

PRESENT_TESTS=()
for test_file in "${TEST_FILES[@]}"; do
  if [[ -f "$test_file" ]]; then
    PRESENT_TESTS+=("$test_file")
  else
    echo "Skipping missing optional test file: ${test_file}"
  fi
done

if [[ "${#PRESENT_TESTS[@]}" -gt 0 ]]; then
  php artisan test --stop-on-failure "${PRESENT_TESTS[@]}"
else
  echo "No targeted ContentOps tests were found." >&2
  exit 1
fi

DOCTOR_ARGS=(
  content:doctor
  "--domains=${DOMAINS}"
  --with-git
  --with-package-roots
  --with-dry-plan
  --write-report
  --json
)

if [[ "$STRICT" == "true" ]]; then
  DOCTOR_ARGS+=(--strict)
fi

set +e
php artisan "${DOCTOR_ARGS[@]}" > "$DOCTOR_JSON"
DOCTOR_EXIT=$?
set -e

GATE_ARGS=(
  content:release-gate
  "--profile=${PROFILE}"
  "--base=${BASE_REF}"
  "--head=${HEAD_REF}"
  "--domains=${DOMAINS}"
  --write-report
  --json
)

if [[ "$WITH_RELEASE_CHECK" == "true" ]]; then
  GATE_ARGS+=(--with-release-check)
fi

if [[ "$STRICT" == "true" ]]; then
  GATE_ARGS+=(--strict)
fi

set +e
php artisan "${GATE_ARGS[@]}" > "$GATE_JSON"
GATE_EXIT=$?
set -e

php -r '
    function contentops_value(array $payload, string $path, mixed $default = ""): mixed {
        $value = $payload;
        foreach (explode(".", $path) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
    $doctor = json_decode((string) @file_get_contents("storage/app/contentops-ci/content-doctor.json"), true);
    $gate = json_decode((string) @file_get_contents("storage/app/contentops-ci/content-release-gate.json"), true);
    $lines = [];
    $lines[] = "## ContentOps CI Preflight";
    $lines[] = "";
    if (trim((string) getenv("CONTENTOPS_TARGET_SHA")) !== "") {
        $lines[] = "Target SHA: `" . trim((string) getenv("CONTENTOPS_TARGET_SHA")) . "`";
        $lines[] = "";
    }
    $lines[] = "| Check | Status | Report |";
    $lines[] = "| --- | --- | --- |";
    $lines[] = sprintf(
        "| content:doctor | `%s` | `%s` |",
        is_array($doctor) ? (string) ($doctor["overall_status"] ?? "unknown") : "invalid-json",
        is_array($doctor) ? (string) contentops_value($doctor, "artifacts.report_path") : ""
    );
    $lines[] = sprintf(
        "| content:release-gate | `%s` | `%s` |",
        is_array($gate) ? (string) contentops_value($gate, "gate.overall_status", "unknown") : "invalid-json",
        is_array($gate) ? (string) contentops_value($gate, "artifacts.report_path") : ""
    );
    if (is_array($gate)) {
        $lines[] = "";
        $lines[] = sprintf(
            "Changed packages: `%d`; blocked packages: `%d`; warnings: `%d`.",
            (int) contentops_value($gate, "summary.changed_packages", 0),
            (int) contentops_value($gate, "summary.blocked_packages", 0),
            (int) contentops_value($gate, "summary.warnings", 0)
        );
        $recommendations = array_slice((array) ($gate["recommendations"] ?? []), 0, 5);
        if ($recommendations !== []) {
            $lines[] = "";
            $lines[] = "Top recommendations:";
            foreach ($recommendations as $recommendation) {
                $lines[] = "- " . (string) $recommendation;
            }
        }
    }
    file_put_contents("storage/app/contentops-ci/summary.md", implode(PHP_EOL, $lines) . PHP_EOL);
'

cat "$SUMMARY_MD"

if [[ -n "${GITHUB_STEP_SUMMARY:-}" ]]; then
  cat "$SUMMARY_MD" >> "$GITHUB_STEP_SUMMARY"
fi

if [[ "$DOCTOR_EXIT" -ne 0 ]]; then
  echo "content:doctor failed with exit code ${DOCTOR_EXIT}" >&2
fi

if [[ "$GATE_EXIT" -ne 0 ]]; then
  echo "content:release-gate failed with exit code ${GATE_EXIT}" >&2
fi

if [[ "$DOCTOR_EXIT" -ne 0 || "$GATE_EXIT" -ne 0 ]]; then
  exit 1
fi
