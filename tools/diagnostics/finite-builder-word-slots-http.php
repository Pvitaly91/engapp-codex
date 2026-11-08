<?php
declare(strict_types=1);

// Explicit read-only HTTP acceptance for all registered finite Sentence Builder banks.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $e): void {
    fwrite(STDERR, json_encode(['status'=>'failed', 'stage'=>$GLOBALS['audit_stage'] ?? 'startup',
        'exception'=>get_class($e)], JSON_THROW_ON_ERROR)."\n"); exit(1);
});
App\Services\M49LocalTargetGuard::assertConfiguredDatabase(Illuminate\Support\Facades\DB::connection());
config(['cache.default'=>'array']);
$app->setLocale('uk');
$receipt = ['status'=>'verified', 'banks'=>[], 'builders'=>0, 'database_writes'=>0];
$failures = [];
foreach (App\Support\FinitePracticeQualityCatalogs::CATALOGS as $catalog) {
    foreach (array_keys($catalog::TOPICS) as $topic) {
        $GLOBALS['audit_stage'] = $catalog.'/'.$topic;
        $bank = $catalog::mapping($topic)['builder'];
        $test = App\Models\SavedGrammarTest::where('uuid', $bank['old_saved_test_uuid'])->firstOrFail();
        $slug = App\Support\SentenceBuilderBranding::canonicalLessonSlug((string)$test->slug);
        if (!preg_match('~^[a-z0-9/-]+$~D', $slug)) { throw new RuntimeException('Invalid local test slug.'); }
        $url = 'http://gramlyze.loc/test/'.$slug.'/questions';
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_PROXY=>'',
            CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'], CURLOPT_CONNECTTIMEOUT=>4, CURLOPT_TIMEOUT=>60,
            CURLOPT_HTTPHEADER=>['Accept: application/json']]);
        $body = curl_exec($curl); $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $local = curl_getinfo($curl, CURLINFO_PRIMARY_IP) === '127.0.0.1'; curl_close($curl);
        if ($status !== 200 || !$local || !is_string($body)) {
            $failures[] = [$catalog,$topic,'http',$status]; continue;
        }
        $payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        $actual = array_column($payload['questions'] ?? [], null, 'uuid');
        $expected = array_column($catalog::compile($topic,'builder')['questions'], null, 'uuid');
        $a=array_keys($actual); $b=array_keys($expected); sort($a); sort($b);
        if ($a !== $b) { $failures[] = [$catalog,$topic,'question-set']; continue; }
        foreach ($expected as $uuid=>$source) {
            $row=$actual[$uuid]; $policy=$row['finite_word_slots'] ?? null;
            if (($row['question'] ?? null)!==$source['question']
                || array_values($row['answer_map'] ?? []) !== array_column($source['markers'],'answer')
                || !is_array($policy) || ($policy['sentences'] ?? null)!==$source['accepted_target_texts']
                || ($policy['revision'] ?? null)!==($row['psp_content_revision'] ?? null)) {
                $failures[] = [$catalog,$topic,$uuid,'word-policy'];
            }
            ++$receipt['builders'];
        }
        $receipt['banks'][]=['catalog'=>$catalog,'topic'=>$topic,'url'=>'http://gramlyze.loc/test/'.$slug,
            'status'=>$status,'builders'=>count($actual),'sha256'=>hash('sha256',$body)];
        echo json_encode(['bank'=>count($receipt['banks']),'topic'=>$topic,'questions'=>count($actual),'failures'=>count($failures)])."\n";
    }
}
$receipt['failures']=$failures;
$receipt['status']=$failures===[]?'verified':'failed';
$path=dirname(__DIR__,2).'/storage/app/finite-builder-word-slots/http-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
$handle=fopen($path,'x');
if (!$handle) { throw new RuntimeException('Exclusive receipt unavailable.'); }
fwrite($handle,json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n"); fclose($handle);
echo json_encode(['status'=>$receipt['status'],'banks'=>count($receipt['banks']),'builders'=>$receipt['builders'],
    'failures'=>count($failures),'receipt'=>$path],JSON_UNESCAPED_SLASHES)."\n";
exit($failures===[]?0:1);
