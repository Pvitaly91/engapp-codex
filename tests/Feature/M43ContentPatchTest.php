<?php

namespace Tests\Feature;

use App\Services\M43ContentPatch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M43AuthoredTenseUsagePackage as Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M43ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root; private string $private;
    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        $this->root=storage_path('app/m43-fixture-'.bin2hex(random_bytes(8))); $this->private=$this->root.'/evidence';
        File::makeDirectory($this->private,0700,true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$before,$package]=Package::load();
        DB::table('page_categories')->insert(['slug'=>'fixture-parent','title'=>'Unrelated parent','language'=>'uk','type'=>'theory']);
        foreach (M43ContentPatch::sourcePaths($before,$package) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true);
            File::copy(base_path($path),$this->root.'/'.$path);
        }
        foreach ($package['targets'] as $i=>$t) {
            $path=$this->root.'/'.$t['path']; File::makeDirectory(dirname($path),0700,true,true);
            File::put($path,Package::json($before['targets'][$i]['before']));
            (new class($path) extends JsonPageSeeder {
                public function __construct(private string $path) {}
                protected function definitionPath(): string { return $this->path; }
            })->run();
            File::put($path,Package::json($t['after']));
        }
        $row=(array)DB::table('text_blocks')->first(); unset($row['id']); $row['uuid']='protected-locale'; $row['locale']='pl'; DB::table('text_blocks')->insert($row);
        $row['uuid']='protected-english';$row['locale']='en';DB::table('text_blocks')->insert($row);
        $this->seedProtectedBanks($package);
        DB::table('questions')->insert(['uuid'=>'protected-bank','question'=>'Protected {a1}']);
        foreach(['content_sync_states','question_review_results','user_polyglot_answer_attempts','user_polyglot_lesson_progress','unrelated_arbitrary_payloads'] as $table){
            if(!DB::connection()->getSchemaBuilder()->hasTable($table)){
                DB::connection()->getSchemaBuilder()->create($table,function(\Illuminate\Database\Schema\Blueprint $schema){$schema->id();$schema->text('fixture');});
            }
            DB::table($table)->insert(['fixture'=>'Private fixture data, never returned']);
        }
    }
    private function service(): M43ContentPatch { return new M43ContentPatch(DB::connection(),$this->root.'/database',$this->private); }
    private function path(string $n): string { return $this->private.'/'.$n.'.json'; }
    private function snapshot(): array
    {
        $tables=DB::connection()->getSchemaBuilder()->getTableListing(schema:'main',schemaQualified:false);sort($tables);$out=[];
        foreach($tables as $table){
            $columns=DB::connection()->getSchemaBuilder()->getColumnListing($table);$query=DB::table($table);
            foreach(in_array('id',$columns,true)?['id']:$columns as $column)$query->orderBy($column);
            $out[$table]=$query->get()->map(fn($row)=>(array)$row)->all();
        }
        return $out;
    }
    private function refuses(callable $f): void
    {
        $before=$this->snapshot(); try { $f(); self::fail('Unsafe operation accepted.'); } catch (RuntimeException) {} self::assertSame($before,$this->snapshot());
    }
    public function test_preview_apply_exclusive_backup_noop_restore_preserve_every_protected_row(): void
    {
        $p=$this->service(); $before=$this->snapshot(); $plan=$p->savePlan($this->path('preview'));
        self::assertSame($before,$this->snapshot()); self::assertNotEmpty($plan['updates']); self::assertCount(7,$plan['inserts']);
        self::assertSame([1,3,3],array_map(fn($name)=>count(array_filter($plan['inserts'],fn($insert)=>$insert['seeder']===$name)),M43ContentPatch::NAMES));
        $r=$p->apply($this->path('preview'),$this->path('backup')); self::assertSame('applied',$r['status']);
        self::assertSame($plan,json_decode(File::get($this->path('backup')),true)); self::assertSame($plan['protected'],$p->plan()['protected']);
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$p->apply($this->path('preview'),$this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status'=>'restored','updated'=>count($plan['updates']),'deleted'=>count($plan['inserts'])],$p->restore($this->path('backup'))); self::assertSame($before,$this->snapshot());
        File::put($this->path('exists'),'{}'); $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('exists')));
    }
    public static function conflicts(): array { return array_map(fn($x)=>[$x],['source','master','mapping','before','package','code','arbitrary','new_table','bank_link','saved_test','progress','attempt','review','owner','locale','category','category_swap','category_language','category_type','parent','order','heading','level','css_class','body','unknown','stale']); }
    #[DataProvider('conflicts')]
    public function test_conflicts_stop_without_writes(string $kind): void
    {
        $p=$this->service(); $p->savePlan($this->path('preview'));
        $row=DB::table('text_blocks')->where('sort_order',2)->first();
        if ($kind==='source') { [, $pack]=Package::load(); File::append($this->root.'/'.$pack['targets'][0]['path'],'broken'); }
        elseif ($kind==='master') { File::append($this->root.'/'.Package::MASTER_PATH, ' '); }
        elseif ($kind==='mapping') { File::append($this->root.'/'.Package::MAPPING_PATH, ' '); }
        elseif ($kind==='before') { File::append($this->root.'/'.Package::BEFORE, ' '); }
        elseif ($kind==='package') { File::append($this->root.'/'.Package::SOURCE, ' '); }
        elseif ($kind==='code') { File::append($this->root.'/app/Services/M43ContentPatch.php', ' '); }
        elseif ($kind==='arbitrary') { DB::table('unrelated_arbitrary_payloads')->update(['fixture'=>'Changed']); }
        elseif ($kind==='new_table') { DB::connection()->getSchemaBuilder()->create('new_unrelated_table',fn($schema)=>$schema->id()); }
        elseif ($kind==='bank_link') { DB::table('question_theory_text_blocks')->where('id',DB::table('question_theory_text_blocks')->value('id'))->update(['position'=>99]); }
        elseif ($kind==='saved_test') { DB::table('saved_grammar_tests')->update(['name'=>'Concurrent saved test edit']); }
        elseif ($kind==='progress') { DB::table('user_polyglot_lesson_progress')->update(['fixture'=>'Concurrent change']); }
        elseif ($kind==='attempt') { DB::table('user_polyglot_answer_attempts')->update(['fixture'=>'Concurrent change']); }
        elseif ($kind==='review') { DB::table('question_review_results')->update(['fixture'=>'Concurrent change']); }
        elseif ($kind==='owner') { DB::table('pages')->where('id',$row->page_id)->update(['seeder'=>'Foreign']); }
        elseif ($kind==='category') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['slug'=>'foreign']); }
        elseif ($kind==='category_swap') {
            $other = DB::table('page_categories')->where('slug', 'fixture-parent')->value('id');
            self::assertNotNull($other); self::assertNotSame($row->page_category_id, $other);
            DB::table('pages')->where('id', $row->page_id)->update(['page_category_id' => $other]);
            DB::table('text_blocks')->where('page_id', $row->page_id)->update(['page_category_id' => $other]);
        }
        elseif ($kind==='category_language') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['language'=>'pl']); }
        elseif ($kind==='category_type') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['type'=>'foreign']); }
        elseif ($kind==='parent') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['parent_id'=>DB::table('page_categories')->where('slug','fixture-parent')->value('id')]); }
        elseif ($kind==='unknown') { $new=(array)$row; unset($new['id']); $new['uuid']='unknown'; DB::table('text_blocks')->insert($new); }
        elseif ($kind==='stale') { DB::table('questions')->where('uuid','protected-bank')->update(['question'=>'Changed after preview']); }
        else { DB::table('text_blocks')->where('id',$row->id)->update(match($kind) {'locale'=>['locale'=>'pl'],'order'=>['sort_order'=>99],'heading'=>['heading'=>'Manual heading'],'level'=>['level'=>'A1'],'css_class'=>['css_class'=>'manual-class'],default=>['body'=>'Manual']}); }
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup'))); self::assertFileDoesNotExist($this->path('backup'));
    }
    public function test_mid_transaction_failure_rolls_back(): void
    {
        $p=new class(DB::connection(),$this->root.'/database',$this->private) extends M43ContentPatch {
            protected function afterUpdate(int $i): void { throw new RuntimeException('Injected failure.'); }
        };
        $p->savePlan($this->path('preview')); $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup'))); self::assertFileExists($this->path('backup'));
    }
    public function test_insert_failure_rolls_back_updates_and_new_native_rows(): void
    {
        $p=new class(DB::connection(),$this->root.'/database',$this->private) extends M43ContentPatch {
            protected function afterInsert(int $index): void { if ($index === 1) { throw new RuntimeException('Injected insert failure.'); } }
        };
        $p->savePlan($this->path('preview'));
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));
        self::assertFileExists($this->path('backup'));
    }
    public function test_production_profile_without_verified_working_target_never_writes(): void
    {
        $p=$this->service(); $p->savePlan($this->path('preview'));
        $this->app->detectEnvironment(fn()=>'production');
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_preview_covers_all_actual_tables_and_every_non_target_block():void
    {
        $before=$this->snapshot();$plan=$this->service()->plan();self::assertSame($before,$this->snapshot());
        self::assertSame(array_keys($before),array_keys($plan['protected']));
        self::assertArrayHasKey('unrelated_arbitrary_payloads',$plan['protected']);
        $ids=DB::table('text_blocks')->whereIn('seeder',M43ContentPatch::NAMES)->where('locale','uk')->pluck('id')->all();
        $hash=hash_init('sha256');$count=0;
        foreach(DB::table('text_blocks')->whereNotIn('id',$ids)->orderBy('id')->cursor() as $row){
            hash_update($hash,M43ContentPatch::digest((array)$row)."\n");$count++;
        }
        self::assertSame(['count'=>$count,'sha256'=>hash_final($hash)],$plan['protected']['text_blocks']);
        self::assertGreaterThanOrEqual(2,$count);
    }

    public function test_finite_three_owner_mapping_keeps_one_exact_tenses_root(): void
    {
        $plan = $this->service()->plan();
        self::assertSame(['tenses', 'tenses', 'tenses'], array_values(M43ContentPatch::CATEGORIES));
        self::assertSame([['tenses'], ['tenses'], ['tenses']], array_values(M43ContentPatch::ANCESTRIES));
        foreach (M43ContentPatch::NAMES as $identity) {
            self::assertSame(M43ContentPatch::ANCESTRIES[$identity], array_column($plan['pages'][$identity]['category_ancestry'], 'slug'));
        }
        self::assertCount(1, array_unique(array_column(array_column($plan['pages'], 'page'), 'page_category_id')));
    }

    public static function ownerIndices(): array { return [[0], [1], [2]]; }

    #[DataProvider('ownerIndices')]
    public function test_another_allowed_m43_category_is_foreign_for_each_exact_owner(int $index): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        $identity = M43ContentPatch::NAMES[$index];
        $page = DB::table('pages')->where('seeder', $identity)->sole();
        $foreign = 'fixture-parent';
        $category = DB::table('page_categories')->where('slug', $foreign)->value('id');
        self::assertNotSame($page->page_category_id, $category);
        DB::table('pages')->where('id', $page->id)->update(['page_category_id' => $category]);
        DB::table('text_blocks')->where('page_id', $page->id)->update(['page_category_id' => $category]);
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_a_non_target_localised_body_edit_after_preview_refuses_apply(): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        DB::table('text_blocks')->where('uuid', 'protected-locale')->update(['body' => 'Unrelated manual content']);
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public static function rootConflicts(): array
    {
        $out = [];
        foreach ([0, 1, 2] as $index) {
            foreach (['parent', 'cycle', 'language', 'type'] as $kind) { $out[] = [$index, $kind]; }
        }
        return $out;
    }

    #[DataProvider('rootConflicts')]
    public function test_both_target_roots_reject_wrong_ancestry_language_type_or_cycle(int $index, string $kind): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        $slug = ['tenses', 'tenses', 'tenses'][$index];
        $category = DB::table('page_categories')->where('slug', $slug)->sole();
        $parent = DB::table('page_categories')->where('slug', 'fixture-parent')->sole();
        DB::table('page_categories')->where('id', $category->id)->update(match ($kind) {
            'parent' => ['parent_id' => $parent->id], 'cycle' => ['parent_id' => $category->id],
            'language' => ['language' => 'pl'], default => ['type' => 'foreign'],
        });
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_all_existing_native_and_foreign_locale_rows_remain_exact(): void
    {
        $patch=$this->service();$before=$this->snapshot();$plan=$patch->savePlan($this->path('preview'));
        $patch->apply($this->path('preview'),$this->path('backup'));$after=$this->snapshot();
        $byId=array_column($after['text_blocks'],null,'id');$changes=array_column(array_filter($plan['updates'],fn($change)=>$change['table']==='text_blocks'),null,'id');
        foreach($before['pages'] as $row){
            $expected=$row;foreach($plan['updates'] as $change)if($change['table']==='pages'&&$change['id']===$row['id'])$expected=array_replace($expected,$change['after']);
            self::assertSame($expected,array_column($after['pages'],null,'id')[$row['id']]);
        }
        foreach($before['text_blocks'] as $row){
            $expected=$row;if(isset($changes[$row['id']]))foreach($changes[$row['id']]['after'] as $field=>$value)$expected[$field]=$value;
            self::assertSame($expected,$byId[$row['id']]);
        }
        foreach(['content_sync_states','question_review_results','user_polyglot_answer_attempts','user_polyglot_lesson_progress','unrelated_arbitrary_payloads'] as $table)self::assertSame($before[$table],$after[$table]);
    }

    public function test_category_title_changed_after_preview_is_a_stale_conflict_before_backup(): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        DB::table('page_categories')->where('slug', 'tenses')->update(['title' => 'Concurrent title edit']);
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_plan_binds_every_frozen_source_byte():void
    {
        [$before,$package]=Package::load();$plan=$this->service()->plan();
        self::assertSame(M43ContentPatch::sourcePaths($before,$package,$this->root),array_keys($plan['sources']));
        foreach($plan['sources'] as $path=>$sha)self::assertSame(hash_file('sha256',$this->root.'/'.$path),$sha);
    }
    public function test_default_parent_page_updates_are_denied_and_m43_is_exact_text_only(): void
    {
        $parent=new \App\Services\M26ContentPatch(DB::connection(),$this->root.'/database',$this->private);
        $fields=new \ReflectionMethod(\App\Services\M26ContentPatch::class,'allowedPageUpdateFields');
        self::assertSame([],$fields->invoke($parent));
        self::assertSame(['text'],(new \ReflectionMethod(M43ContentPatch::class,'allowedPageUpdateFields'))->invoke($this->service()));
        $update=new \ReflectionMethod(\App\Services\M26ContentPatch::class,'update');
        $row=(array)DB::table('pages')->first();$before=$this->snapshot();
        try{$update->invoke($parent,['table'=>'pages','id'=>$row['id'],'seeder'=>$row['seeder'],'before'=>['text'=>$row['text']],'after'=>['text'=>'Forbidden']],false);self::fail('Default parent page write accepted.');}catch(RuntimeException){}
        self::assertSame($before,$this->snapshot());
    }

    public static function pageFieldConflicts(): array
    {
        $cases=[];foreach([0,1,2] as $index)foreach(['text','title'] as $field)$cases[]=[$index,$field];return $cases;
    }
    #[DataProvider('pageFieldConflicts')]
    public function test_page_subtitle_or_protected_title_changed_after_preview_refuses_before_backup(int $index,string $field):void
    {
        $p=$this->service();$p->savePlan($this->path('preview'));
        DB::table('pages')->where('seeder',M43ContentPatch::NAMES[$index])->update([$field=>'Concurrent manual page edit']);
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    private function seedProtectedBanks(array $package):void
    {
        $firstQuestion=null;
        foreach($package['targets'] as $index=>$target){
            $block=DB::table('text_blocks')->where('seeder',$target['identity'])->where('locale','uk')->where('sort_order',1)->sole();
            foreach(['primary-'.$index=>$index===0?'0':'0',$target['bank']['seeder_class']=>'4'] as $seeder=>$type){
                $rows=[];for($i=0;$i<72;$i++)$rows[]=['uuid'=>'fixture-'.$index.'-'.$type.'-'.$i,'question'=>'Preserved fixture {a1}',
                    'type'=>$type,'seeder'=>$seeder,'theory_text_block_uuid'=>$block->uuid];
                DB::table('questions')->insert($rows);
                $links=array_map(fn($row)=>['question_uuid'=>$row['uuid'],'text_block_uuid'=>$block->uuid,'position'=>0],$rows);
                DB::table('question_theory_text_blocks')->insert($links);
                $firstQuestion??=$rows[0]['uuid'];
            }
        }
        $usedBlock=DB::table('text_blocks')->where('seeder',M43ContentPatch::NAMES[2])->where('locale','uk')->where('sort_order',1)->sole();
        for($i=0;$i<24;$i++){
            $uuid='legacy-used-'.$i;
            DB::table('questions')->insert(['uuid'=>$uuid,'question'=>'Legacy {a1}','type'=>'4','seeder'=>'legacy-used-bank']);
            DB::table('question_theory_text_blocks')->insert(['question_uuid'=>$uuid,'text_block_uuid'=>$usedBlock->uuid,'position'=>1]);
        }
        $id=DB::table('questions')->where('uuid',$firstQuestion)->value('id');
        $option=DB::table('question_options')->insertGetId(['option'=>'preserved-option']);
        DB::table('question_answers')->insert(['question_id'=>$id,'option_id'=>$option,'marker'=>'a1']);
        DB::table('question_option_question')->insert(['question_id'=>$id,'option_id'=>$option,'flag'=>1]);
        DB::table('verb_hints')->insert(['question_id'=>$id,'option_id'=>$option,'marker'=>'a1']);
        DB::table('question_hints')->insert(['question_id'=>$id,'provider'=>'fixture','locale'=>'uk','hint'=>'Protected hint']);
        DB::table('question_variants')->insert(['question_id'=>$id,'text'=>'Protected variant']);
        $test=DB::table('saved_grammar_tests')->insertGetId(['uuid'=>'saved-fixture','slug'=>'saved-fixture','name'=>'Protected saved test']);
        DB::table('saved_grammar_test_questions')->insert(['saved_grammar_test_id'=>$test,'question_uuid'=>$firstQuestion,'position'=>0]);
    }

    public function test_exact_shared_hunks_keep_foreign_root_changes_and_crlf_bytes():void
    {
        $base="first\nanchor\nold\nend\n";
        $wt="first\nanchor\nnew\nend\n";
        $hunks=[['before'=>"anchor\nold\nend\n",'after'=>"anchor\nnew\nend\n"]];
        self::assertSame($wt,M43ContentPatch::projectSharedFragments($base,$hunks));
        self::assertSame("foreign\r\nanchor\r\nnew\r\nend\r\n",
            M43ContentPatch::projectSharedFragments("foreign\r\nanchor\r\nold\r\nend\r\n",$hunks,true));
    }

    public static function sharedConflicts():array
    {
        return [["anchor\nmanual\nend\n"],["anchor\nold\nend\nanchor\nold\nend\n"],
            ["anchor\nold\nend\nanchor\r\nold\r\nend\r\n"],
            ["anchor\nold\nend\nanchor\r\nold\nend\r\n"]];
    }

    public function test_exact_shared_hunk_preserves_mixed_root_line_endings():void
    {
        $hunks=[['before'=>"anchor\nold\nend\n",'after'=>"anchor\nnew\nend\n"]];
        $root="foreign-prefix\nanchor\r\nold\r\nend\r\nforeign-suffix\n";
        self::assertSame("foreign-prefix\nanchor\r\nnew\r\nend\r\nforeign-suffix\n",
            M43ContentPatch::projectSharedFragments($root,$hunks,true));
    }

    public function test_shared_hunk_preserves_unchanged_lines_with_mixed_endings_inside_hunk():void
    {
        $hunks=[['before'=>"anchor\nold\nend\n",'after'=>"anchor\nnew\nend\n"]];
        $root="foreign-prefix\nanchor\r\nold\nend\nforeign-suffix\r\n";
        self::assertSame("foreign-prefix\nanchor\r\nnew\r\nend\nforeign-suffix\r\n",
            M43ContentPatch::projectSharedFragments($root,$hunks,true));
    }

    #[DataProvider('sharedConflicts')]
    public function test_shared_hunk_never_overwrites_conflicting_or_ambiguous_root_bytes(string $root):void
    {
        $this->expectException(RuntimeException::class);
        M43ContentPatch::projectSharedFragments($root,[['before'=>"anchor\nold\nend\n",'after'=>"anchor\nnew\nend\n"]],true);
    }

    public function test_postcondition_rolls_back_writes_if_any_arbitrary_table_changes_during_apply():void
    {
        $patch=new class(DB::connection(),$this->root.'/database',$this->private) extends M43ContentPatch {
            protected function afterUpdate(int $index):void
            {
                if($index===0)$this->db->table('unrelated_arbitrary_payloads')->update(['fixture'=>'Unexpected side effect']);
            }
        };
        $patch->savePlan($this->path('preview'));
        $this->refuses(fn()=>$patch->apply($this->path('preview'),$this->path('backup')));
        self::assertFileExists($this->path('backup'));
    }

    public function test_fresh_after_plan_is_a_zero_diff_and_original_before_plan_is_repeatable():void
    {
        $patch=$this->service();$patch->savePlan($this->path('preview'));$patch->apply($this->path('preview'),$this->path('backup'));
        $after=$patch->savePlan($this->path('after'));
        self::assertSame('after',$after['state']);self::assertSame([],$after['updates']);self::assertSame([],$after['inserts']);
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$patch->apply($this->path('after'),$this->path('after-unused')));
        self::assertFileDoesNotExist($this->path('after-unused'));
    }

}
