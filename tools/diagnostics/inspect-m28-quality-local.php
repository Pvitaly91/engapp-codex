<?php
// Read-only local evidence. No route, cache clearing, seeding or database mutation.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows') { exit(1); }
$root = 'D:/DEV/htdocs/gramlyze.loc';
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (realpath(base_path()) !== realpath($root)) { throw new RuntimeException('Wrong working root.'); }
$db = Illuminate\Support\Facades\DB::connection();
$physical = $db->selectOne('SELECT DATABASE() AS db, @@port AS port');
if ($db->getDriverName() !== 'mysql' || !in_array($db->getConfig('host'), ['localhost','127.0.0.1'], true)
    || $physical->db !== 'gr2' || (int)$physical->port !== 3306) { throw new RuntimeException('Unexpected local read target.'); }
$inventory=[]; $removed=[];
foreach (['m27-m11-linking-words','m28-m12-emphasis-inversion'] as $file) {
    $old=json_decode(file_get_contents(dirname(__DIR__,2).'/database/content-patches/'.$file.'.v1.json'),true,flags:JSON_THROW_ON_ERROR);
    $new=json_decode(file_get_contents(dirname(__DIR__,2).'/database/content-patches/'.$file.'.v2.json'),true,flags:JSON_THROW_ON_ERROR);
    foreach ($new['targets'] as $i=>$t) {
        if ($t['after'] !== $old['targets'][$i]['after']) { throw new RuntimeException('Educational payload changed.'); }
        $page=$db->table('pages')->where('seeder',$t['identity'])->sole();
        $blocks=$db->table('text_blocks')->where('page_id',$page->id)->orderBy('id')->get();
        foreach ($t['after']['page']['blocks'] as $j=>$source) {
            $uuid=App\Support\M26DetailPackage::uuid($t['identity'],$source,$j+1);
            $row=$blocks->firstWhere('uuid',$uuid);
            if (!$row || $row->body !== $source['body'] || $row->type !== $source['type'] || (int)$row->sort_order !== $j+1) {
                throw new RuntimeException('Local DB does not contain exact accepted body: '.$t['slug'].' / '.($j+1));
            }
        }
        $questions=$db->table('questions')->join('question_theory_text_blocks','questions.uuid','=','question_theory_text_blocks.question_uuid')
            ->whereIn('text_block_uuid',$blocks->pluck('uuid'))->distinct()->orderBy('questions.id')->get(['questions.*']);
        $links=$db->table('question_theory_text_blocks')->whereIn('text_block_uuid',$blocks->pluck('uuid'))->orderBy('id')->get();
        $inventory[]=['slug'=>$t['slug'],'page_id'=>$page->id,'page_sha256'=>hash('sha256',json_encode($page)),
            'blocks_sha256'=>hash('sha256',json_encode($blocks)),'questions_sha256'=>hash('sha256',json_encode($questions)),
            'links_sha256'=>hash('sha256',json_encode($links)), 'block_count'=>$blocks->count(),
            'linked_bank_ids'=>$questions->groupBy('seeder')->map(fn($q)=>$q->pluck('id')->values()->all()),'exact_source'=>true];
        foreach($t['plans'] as $j=>$p)foreach($p['points'] as $k=>$point)
            if ($old['targets'][$i]['plans'][$j]['points'][$k]['detail'] !== '' && $point['detail'] === '')
                $removed[]='block-'.$p['key'].'-point-'.($k+1).'-detail';
    }
}
// Inspect educational rows, not user data. Match references, excluding the anchor's own id.
$refs=[];
foreach (['text_blocks'=>'body','pages'=>'text'] as $table=>$field)
    foreach($db->table($table)->select(['id',$field])->orderBy('id')->get() as $row) {
        $body=str_replace('\\"','"',(string)$row->$field);
        foreach($removed as $id)if(preg_match('~(?:href\s*=\s*["\x27][^"\x27]*#|["\x27](?:target|anchor)["\x27]\s*:\s*["\x27]#?)'.preg_quote($id,'~').'~u',$body))$refs[]=['table'=>$table,'id'=>$row->id,'anchor'=>$id];
    }
echo json_encode(['at'=>gmdate('c'),'target'=>['root'=>$root,'driver'=>'mysql','host'=>$db->getConfig('host'),'port'=>$physical->port,'database'=>$physical->db],
    'targets'=>$inventory,'removed_anchor_references'=>$refs],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
