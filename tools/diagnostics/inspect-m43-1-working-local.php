<?php

// Explicit SELECT-only inventory. Raw learner rows stay in private local evidence.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
$name = $argv[1] ?? '';
if (!preg_match('/^m43-1-(before|after|final)-v[1-9][0-9]*\.json$/D', $name)) { throw new RuntimeException('Exact M43.1 evidence basename required.'); }
$directory = $root.'/storage/app/seo-m43-1-local';
if (!is_dir($directory) || is_link($directory) || file_exists($directory.'/'.$name)) { throw new RuntimeException('Exclusive private directory required.'); }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong application root.'); }
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost','127.0.0.1'], true)
    || $db->getConfig('database') !== 'gr2' || (int)$db->getConfig('port') !== 3306
    || $db->getConfig('url') || $db->getConfig('unix_socket') || $db->getConfig('read') || $db->getConfig('write')) {
    throw new RuntimeException('Unexpected configured local connection.');
}
if ($db->getReadPdo() !== $db->getPdo()) { throw new RuntimeException('Split connection denied.'); }
$physical = (array)$db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($physical['db'] !== 'gr2' || (int)$physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0) { throw new RuntimeException('Physical DB mismatch.'); }
$json = static fn($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$digest = static fn($v) => hash('sha256', $json($v));
$classes = ['TensesPastPerfectVsPastPerfectContinuousTheorySeeder', 'TensesStativeVerbsTheorySeeder', 'TensesUsedToWouldTheorySeeder'];
$targets = []; $allowedPageIds = []; $allowedBlockIds = [];
$db->statement('SET TRANSACTION READ ONLY');
$db->beginTransaction();
try {
    foreach ($classes as $short) {
        $identity = 'Database\\Seeders\\Page_V3\\Tenses\\'.$short;
        $path = 'database/seeders/Page_V3/Tenses/'.$short.'/definition.json';
        $definition = json_decode(file_get_contents($source.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
        $served = json_decode(file_get_contents($root.'/'.$path), true, flags: JSON_THROW_ON_ERROR);
        $owner = (array)$db->table('pages')->where('seeder', $identity)->sole();
        $chain = []; $seen = []; $categoryId = $owner['page_category_id'];
        while ($categoryId !== null) {
            $category = (array)$db->table('page_categories')->where('id', $categoryId)->sole();
            if (isset($seen[$categoryId]) || count($chain) > 8 || $category['language'] !== 'uk' || $category['type'] !== 'theory') { throw new RuntimeException('Invalid category ancestry.'); }
            $seen[$categoryId] = true; array_unshift($chain, $category); $categoryId = $category['parent_id'];
        }
        if ($definition['seeder']['class'] !== $identity || $owner['slug'] !== $definition['slug'] || $owner['title'] !== $definition['page']['title']
            || $owner['type'] !== 'theory' || array_column($chain,'slug') !== ['tenses']) { throw new RuntimeException('Exact owner/category conflict.'); }
        $blocks = $db->table('text_blocks')->where('page_id',$owner['id'])->orderBy('id')->get()->map(fn($r)=>(array)$r)->all();
        $uk = array_values(array_filter($blocks, fn($r)=>$r['locale']==='uk')); usort($uk, fn($a,$b)=>$a['sort_order']<=>$b['sort_order']);
        $configs = [['type'=>'subtitle','column'=>'header','heading'=>null,'level'=>$definition['page']['subtitle_level']??null,'body'=>$definition['page']['subtitle_html'],'uuid_key'=>$definition['page']['subtitle_uuid_key']??'subtitle'], ...$definition['page']['blocks']];
        $exact = $owner['text'] === $definition['page']['subtitle_text'] && count($uk) === count($configs);
        foreach ($configs as $position=>$config) {
            $uuid = App\Support\M26DetailPackage::uuid($identity,$config,$position);
            $found = array_values(array_filter($uk,fn($r)=>$r['uuid']===$uuid));
            if (count($found)!==1) { $exact=false; continue; }
            $r=$found[0];
            foreach (['type','body','column','heading','level','css_class'] as $field) { if ($r[$field]!==($config[$field]??null)) { $exact=false; } }
            if ($r['seeder']!==$identity || (int)$r['sort_order']!==$position || $r['page_category_id']!==$owner['page_category_id']) { $exact=false; }
        }
        if (!$exact || $served !== $definition) { throw new RuntimeException('M43 actual source/served/DB differs: '.$short); }
        $questions=$db->table('questions')->join('question_theory_text_blocks','questions.uuid','=','question_theory_text_blocks.question_uuid')
            ->whereIn('text_block_uuid',array_column($blocks,'uuid'))->distinct()->orderBy('questions.id')->get(['questions.id','questions.seeder','questions.type','questions.level']);
        $banks=[];
        foreach ($questions->groupBy('seeder') as $seeder=>$rows) {
            $global=$db->table('questions')->where('seeder',$seeder)->orderBy('id')->get(['id','type','level']);
            $banks[]=['seeder_class'=>$seeder,'linked_ids'=>$rows->pluck('id')->all(),'linked_count'=>$rows->count(),
                'linked_types'=>$rows->pluck('type')->unique()->values()->all(),'linked_levels'=>$rows->pluck('level')->unique()->sort()->values()->all(),
                'global_ids'=>$global->pluck('id')->all(),'global_count'=>$global->count(),'full_own_bank'=>$rows->pluck('id')->all()===$global->pluck('id')->all()];
        }
        $pageModel=new App\Models\Page; $pageModel->setRawAttributes($owner,true);
        $categoryModel=new App\Models\PageCategory; $categoryModel->setRawAttributes(end($chain),true); $pageModel->setRelation('category',$categoryModel);
        $testSlug=App\Support\TheoryPageTestSlug::forPage($pageModel);
        $targets[]=['identity'=>$identity,'path'=>$path,'url'=>'http://gramlyze.loc/theory/tenses/'.$owner['slug'],
            'page'=>$owner,'category_chain'=>$chain,'blocks'=>$blocks,'source_db_exact'=>true,'definition_sha256'=>$digest($definition),
            'banks'=>$banks,'test_url'=>'http://gramlyze.loc/test/'.$testSlug];
        $allowedPageIds[]=$owner['id']; $allowedBlockIds=[...$allowedBlockIds,...array_column($uk,'id')];
    }
    $tables=$db->getSchemaBuilder()->getTableListing(schema:$physical['db'],schemaQualified:false); sort($tables); $raw=[]; $protected=[];
    foreach ($tables as $table) {
        $columns=$db->getSchemaBuilder()->getColumnListing($table); $q=$db->table($table);
        foreach (in_array('id',$columns,true)?['id']:$columns as $column) { $q->orderBy($column); }
        $allHash=hash_init('sha256'); $protectedHash=hash_init('sha256'); $count=0; $protectedCount=0;
        foreach ($q->cursor() as $r) {
            $row=(array)$r; hash_update($allHash,$digest($row)."\n"); $count++;
            hash_update($protectedHash,$digest($row)."\n"); $protectedCount++;
        }
        $raw[$table]=['count'=>$count,'sha256'=>hash_final($allHash)];
        $protected[$table]=['count'=>$protectedCount,'sha256'=>hash_final($protectedHash)];
    }
    $record=['at'=>gmdate('c'),'target'=>['root'=>$root,'driver'=>'mysql','host'=>$db->getConfig('host'),'port'=>(int)$physical['port'],'database'=>$physical['db']],
        'runtime'=>['environment'=>app()->environment(),'site_mode_for_local_host'=>app(App\Support\SiteMode::class)->forHost('gramlyze.loc'),'application_root'=>realpath(base_path()),'public_root'=>realpath(public_path())],'targets'=>$targets,'raw_tables'=>$raw,'protected_tables'=>$protected];
    $db->rollBack();
} catch (Throwable $e) { if ($db->transactionLevel()>0) { $db->rollBack(); } throw $e; }
$bytes=$json($record)."\n"; $out=fopen($directory.'/'.$name,'xb');
if (!$out) { throw new RuntimeException('Evidence collision.'); }
try { if (fwrite($out,$bytes)!==strlen($bytes)||!fflush($out)) { throw new RuntimeException('Incomplete evidence.'); } } finally { fclose($out); }
echo $json(['evidence'=>$name,'sha256'=>hash('sha256',$bytes),'tables'=>count($raw),'targets'=>array_map(fn($t)=>['title'=>$t['page']['title'],'blocks'=>count($t['blocks']),
    'url'=>$t['url'],'banks'=>array_map(fn($b)=>array_diff_key($b,['linked_ids'=>true,'global_ids'=>true]),$t['banks'])],$targets)])."\n";
