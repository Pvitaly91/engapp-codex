<?php

// SELECT-only inventory for the physical working .loc database. Private evidence contains no credentials.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
$physical = (array) $db->selectOne('SELECT DATABASE() AS db, @@hostname AS server, @@port AS port');
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost','127.0.0.1'], true)
    || $physical['db'] !== 'gr2' || (int) $physical['port'] !== 3306 || strcasecmp($physical['server'], gethostname()) !== 0) {
    throw new RuntimeException('Unexpected read-only local target.');
}
$targets = []; $banks = []; $removed = [];
$names = ['ParticipleClausesBasicsTheorySeeder','ParticipleClausesTheorySeeder','AdvancedParticipleAndAbsoluteClausesTheorySeeder'];
foreach ($names as $i => $short) {
    $name = 'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\'.$short;
    $page = $db->table('pages')->where('seeder', $name)->sole();
    $blocks = $db->table('text_blocks')->where('page_id', $page->id)->orderBy('id')->get();
    $questions = $db->table('questions')->join('question_theory_text_blocks','questions.uuid','=','question_theory_text_blocks.question_uuid')
        ->whereIn('text_block_uuid',$blocks->pluck('uuid'))->distinct()->orderBy('questions.id')
        ->get(['questions.id','questions.seeder','questions.type','questions.level']);
    $groups = $questions->groupBy(fn($q) => $q->seeder.'|'.$q->type.'|'.$q->level);
    $globalLevels=[];
    foreach($questions->pluck('seeder')->unique() as $seeder){
        $globalLevels[$seeder]=$db->table('questions')->where('seeder',$seeder)->distinct()->pluck('level')->all();
    }
    // Choose the actual dedicated single-level bank, not the separate all-level bank.
    $primary = $groups->filter(fn($q) => (string)$q[0]->type === '4' && $q[0]->level === ['B2','C1','C2'][$i]
        && count($globalLevels[$q[0]->seeder]) === 1);
    if ($primary->count() !== 1) { throw new RuntimeException('Missing/ambiguous exact M14 primary bank: '.json_encode($groups->map(fn($q)=>$q->count())->all(),JSON_UNESCAPED_SLASHES)); }
    $q = $primary->first();
    $banks[$name] = ['seeder_class'=>$q[0]->seeder, 'question_type'=>(string)$q[0]->type, 'level'=>$q[0]->level,
        'question_ids'=>$q->pluck('id')->all()];
    $targets[] = ['identity'=>$name, 'slug'=>$page->slug, 'page_id'=>$page->id,
        'blocks'=>$blocks->map(fn($b)=>['id'=>$b->id,'uuid'=>$b->uuid,'type'=>$b->type,'locale'=>$b->locale,'order'=>$b->sort_order,'body_sha256'=>hash('sha256',$b->body)])->all(),
        'linked_bank_groups'=>$groups->map(fn($q)=>$q->count())->all(),
        'linked_bank_ids'=>$questions->groupBy('seeder')->map(fn($q)=>$q->pluck('id')->all())->all()];
}
$m29 = json_decode(file_get_contents($source.'/database/content-patches/m29-m13-sentence-structure.v1.json'), true, flags: JSON_THROW_ON_ERROR);
$m29Inventory = [];
foreach ($m29['targets'] as $t) {
    $page = $db->table('pages')->where('seeder',$t['identity'])->sole();
    if($page->type!=='theory'||$page->slug!==$t['slug']||$page->title!==$t['after']['page']['title']||$page->text!==$t['after']['page']['subtitle_text']){
        throw new RuntimeException('M29 protected owner metadata differs.');
    }
    $blocks = $db->table('text_blocks')->where('page_id',$page->id)->orderBy('id')->get();
    if($blocks->where('locale','uk')->count()!==count($t['after']['page']['blocks'])+1){throw new RuntimeException('M29 unexpected UK rows.');}
    foreach ($t['after']['page']['blocks'] as $j=>$b) {
        $uuid = App\Support\M26DetailPackage::uuid($t['identity'],$b,$j+1);
        $r = $blocks->firstWhere('uuid',$uuid);
        if (!$r || $r->body !== $b['body'] || $r->type !== $b['type'] || (int)$r->sort_order !== $j+1
            || $r->locale!=='uk' || $r->seeder!==$t['identity'] || $r->page_id!==$page->id || $r->page_category_id!==$page->page_category_id) {
            throw new RuntimeException('M29 working body differs from full accepted native payload.');
        }
    }
    $m29Inventory[] = ['slug'=>$t['slug'],'page_id'=>$page->id,'native_blocks'=>$blocks->whereNotIn('type',['subtitle','hero'])->count(),
        'page_sha256'=>hash('sha256',json_encode($page)), 'blocks_sha256'=>hash('sha256',json_encode($blocks)),
        'exact_full_author_payload'=>true,'required_db_updates'=>0,'required_db_inserts'=>0,'required_db_deletes'=>0];
    foreach ($t['plans'] as $p) foreach ($p['points'] as $k=>$point) if ($point['detail'] !== '') {
        $removed[] = 'block-'.$p['key'].'-point-'.($k+1).'-detail';
    }
}
$references = [];
foreach (['text_blocks'=>'body','pages'=>'text'] as $table=>$field) {
    foreach ($db->table($table)->select(['id',$field])->orderBy('id')->cursor() as $r) {
        $body = str_replace('\\"','"',(string)$r->$field);
        foreach ($removed as $id) if (preg_match('~(?:href\s*=\s*["\x27][^"\x27]*#|["\x27](?:target|anchor)["\x27]\s*:\s*["\x27]#?)'.preg_quote($id,'~').'~u',$body)) {
            $references[] = ['table'=>$table,'id'=>$r->id,'anchor'=>$id];
        }
    }
}
$fingerprints = [];
foreach (['pages','page_categories','text_blocks','tags','page_tag','page_category_tag','tag_text_block','questions','question_answers','question_options','question_option_question','question_theory_text_blocks','question_tag','question_marker_tag','verb_hints','question_hints','question_variants','saved_grammar_tests','saved_grammar_test_questions','seed_runs'] as $table) {
    $query = $db->table($table); $columns = $db->getSchemaBuilder()->getColumnListing($table);
    foreach (in_array('id',$columns,true)?['id']:$columns as $column) { $query->orderBy($column); }
    $hash = hash_init('sha256'); $count = 0;
    foreach ($query->cursor() as $r) { hash_update($hash, App\Services\PronounContentRepair::digest((array)$r)."\n"); $count++; }
    $fingerprints[$table] = ['count'=>$count,'sha256'=>hash_final($hash)];
}
$record = ['at'=>gmdate('c'),'target'=>['root'=>$root,'driver'=>'mysql','host'=>$db->getConfig('host'),'port'=>$physical['port'],'database'=>$physical['db'],'environment'=>app()->environment()],
    'targets'=>$targets,'linked_banks'=>$banks,'m29'=>$m29Inventory,'removed_anchor_references'=>$references,'fingerprints'=>$fingerprints];
echo json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$dir = storage_path('app/seo-m30-local'); if (!is_dir($dir)) { mkdir($dir,0700,true); }
if (($argv[1]??'') === '--save') {
    $basename = $argv[2]??'';
    if (!preg_match('/^[a-z0-9-]+\.json$/D',$basename)) { throw new RuntimeException('Invalid private evidence basename.'); }
    $f = fopen($dir.'/'.$basename,'x'); if (!$f) { throw new RuntimeException('Evidence already exists.'); }
    fwrite($f,json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); fclose($f);
}
if (($argv[1]??'') === '--proof') {
    $nonce = bin2hex(random_bytes(16)); $basename = 'local-proof-'.$nonce.'.json';
    $f = fopen($dir.'/'.$basename,'x'); if (!$f) { throw new RuntimeException('Proof already exists.'); }
    fwrite($f,json_encode(['target'=>'gramlyze.loc','nonce'=>$nonce])); fclose($f);
    echo json_encode(['proof'=>$basename,'nonce'=>$nonce])."\n";
}
