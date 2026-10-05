<?php

declare(strict_types=1);

/** Default preview, --write for the five finite banks, --check for drift. No DB. */
require_once __DIR__.'/lib/ppc_quality_builder.php';

$root = dirname(__DIR__);
$write = in_array('--write', $argv, true);
$check = in_array('--check', $argv, true);
$failed = false;
$total = 0;
$prompts = [];
$targets = [];
$writes = [];
foreach (ppcQualityBuilderBanks() as $bank => $levels) {
    $name = $bank === 'BasicsB2' ? 'PolyglotPastPerfectContinuousBasicsB2LessonSeeder' : 'PolyglotPastPerfectContinuous'.$bank.'AllLevelsLessonSeeder';
    $path = $root.'/database/seeders/V3/Polyglot/'.$name.'/definition.json';
    $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $positions = [];
    $changed = 0;
    foreach ($data['questions'] as &$question) {
        $level = $question['level'];
        $index = $positions[$level] ?? 0;
        $row = $levels[$level][$index] ?? null;
        if ($row === null) {
            throw new RuntimeException('Missing authored row for '.$question['uuid']);
        }
        $expectedUuid = $bank === 'BasicsB2'
            ? sprintf('polyglot-past-perfect-continuous-basics-b2-q%02d', $index + 1)
            : sprintf('pastpc-%s-poly-%s-%02d', strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $bank)), strtolower($level), $index + 1);
        if ($question['uuid'] !== $expectedUuid) {
            throw new RuntimeException('Unexpected slot identity '.$question['uuid'].'; expected '.$expectedUuid);
        }
        $projected = ppcQualityBuilderProjection($bank, $question, $row);
        $changed += $question !== $projected ? 1 : 0;
        $question = $projected;
        $positions[$level] = $index + 1;
        foreach (['question' => &$prompts, 'target_text' => &$targets] as $field => &$seen) {
            $normal = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $question[$field]));
            if (isset($seen[$normal])) {
                throw new RuntimeException('Normalized author duplicate: '.$seen[$normal].' / '.$question['uuid']);
            }
            $seen[$normal] = $question['uuid'];
        }
        unset($seen);
    }
    unset($question);
    foreach ($levels as $level => $rows) {
        if (($positions[$level] ?? 0) !== count($rows)) {
            throw new RuntimeException('Unused author rows for '.$bank.'/'.$level);
        }
    }
    if ($write && $changed > 0) {
        $writes[$path] = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    }
    $failed = $failed || ($check && $changed > 0);
    $total += count($data['questions']);
    echo $bank.': '.count($data['questions']).' reviewed; '.$changed.' '.($write ? 'updated' : 'pending').'; '.json_encode($positions).PHP_EOL;
    if ($bank === 'BasicsB2') {
        foreach (['uk', 'en', 'pl'] as $locale) {
            $localizationPath = dirname($path).'/localizations/'.$locale.'.json';
            $companion = json_decode((string) file_get_contents($localizationPath), true, 512, JSON_THROW_ON_ERROR);
            $expected = array_map(static fn (array $question): array => [
                'id' => $question['id'],
                'uuid' => $question['uuid'],
                'source_text' => $question['localizations'][$locale]['source_text'],
                'hints' => $question['localizations'][$locale]['hints'],
                'explanations' => [],
            ], $data['questions']);
            $companionChanged = $companion['questions'] !== $expected;
            $companion['questions'] = $expected;
            if ($write && $companionChanged) {
                $writes[$localizationPath] = json_encode($companion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
            }
            $failed = $failed || ($check && $companionChanged);
            echo 'Basics companion '.$locale.': 48 reviewed; '.($companionChanged ? '48 ' : '0 ').($write ? 'updated' : 'pending').PHP_EOL;
        }
    }
}
// Validate every slot, lexical basis and duplicate before touching any file.
foreach ($writes as $path => $json) {
    file_put_contents($path, $json);
}
echo 'Total '.$total.'; normalized prompt/target duplicates 0; '.($write ? 'definitions written' : ($check ? 'drift checked' : 'preview only')).PHP_EOL;
exit($failed ? 1 : 0);
