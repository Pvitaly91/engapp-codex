<?php

namespace Tests\Feature;

use App\Support\M26DetailPackage as Digest;
use App\Support\M77OnePrepositionsPhrasal as Package;
use App\Support\TheoryNativeContentRelease;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Frozen public teaching sources only. No application, SQL, HTTP, browser or private row fixtures. */
class M77OnePrepositionsPhrasalContentTest extends TestCase
{
    private function root(): string { return dirname(__DIR__, 2); }

    private function release(): array
    {
        $release = Package::load($this->root());
        Package::validate($release[0], $release[1], $release[2], $release[3]);
        return $release;
    }

    private function refuses(callable $call, string $case): void
    {
        $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, $case);
    }

    private function practice(array $master, int $i): array
    {
        return $master['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'];
    }

    public function test_exact_six_owner_scope_preserves_forty_one_continuing_slots_and_adds_only_five_practices(): void
    {
        [$before, $patch, $master, $contract] = $this->release(); $profile = Package::profile();
        self::assertSame('5270494e0f999eee2a3d1fba6fb7c3f46a914481', Package::BASE_SHA);
        self::assertSame([123,120,119,118,115,116], Package::PAGE_IDS);
        self::assertSame([20,19,19,19,19,19], Package::CATEGORY_IDS);
        self::assertSame([11,6,6,6,6,6], Package::CONTINUING_COUNTS);
        self::assertSame([8,6,6,6,6,6], Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([null,'A1','A1','A2','B1','B1'], Package::LEVELS);
        self::assertSame(['A2','A1','A1','A2','B1','B1'], Package::FOCUS_LEVELS);
        self::assertSame(Package::OWNERS, array_column($before['targets'], 'identity'));
        self::assertSame(Package::OWNERS, array_column($patch['targets'], 'identity'));
        self::assertSame(Package::OWNERS, array_column($master['lessons'], 'identity'));
        self::assertSame(Package::LEVELS, array_column($contract['targets'], 'level'));
        $levels = [array_fill(0,11,null), ['A1',null,null,null,null,null], ['A1',null,null,null,null,null],
            ['A2',null,null,null,null,null], ['B1',null,null,null,null,null], ['B1',null,null,null,null,null]];
        $primitive = new TheoryNativeContentRelease($profile); $continuing = $added = $total = 0;
        foreach ($patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $snapshot = $old; $lesson = $master['lessons'][$i];
            $projected = $primitive->projectedBefore($old);
            self::assertSame($snapshot, $old, 'Metadata projection cannot mutate raw independent BEFORE.');
            self::assertSame(Package::PAGE_IDS[$i], $target['page_binding']['id']);
            self::assertSame(Package::CATEGORY_IDS[$i], $target['page_binding']['page_category_id']);
            self::assertSame('uk', $old['page']['locale']); self::assertSame('uk', $lesson['locale']);
            self::assertSame(Package::SLUGS[$i], $lesson['slug']); self::assertSame(Package::ANCESTRIES[$i], $target['ancestry']);
            self::assertSame(Package::RENDERED_TITLES[$i], $lesson['preserved_h1']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['display_level']);
            self::assertSame(Package::DISPLAY_LEVELS[$i], $lesson['blocks'][0]['data']['level']);
            self::assertSame(Package::LEVELS[$i], $lesson['technical_block_level']);
            self::assertSame(Package::FOCUS_LEVELS[$i], $lesson['intended_own_practice_level']);
            self::assertSame($target['after'], Package::definition($lesson, $old));
            self::assertSame($target['after'], json_decode(file_get_contents($this->root().'/'.$target['path']), true, flags: JSON_THROW_ON_ERROR));
            $protected = $target['after']; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_html','subtitle_text'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'Raw title, slug, category, tags and excluded metadata stay exact.');
            self::assertCount($i === 0 ? 11 : 7, $lesson['blocks']);
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'practice-set'));
            self::assertCount(1, array_filter($lesson['blocks'], static fn ($b) => $b['type'] === 'navigation-chips'));
            foreach ($old['page']['blocks'] as $slot => $source) {
                ++$continuing; $new = $target['after']['page']['blocks'][$slot];
                self::assertSame($levels[$i][$slot], $source['level'] ?? null);
                self::assertSame($levels[$i][$slot], $new['level'] ?? null);
                self::assertSame($source['column'], $new['column']); self::assertSame($slot, $lesson['blocks'][$slot]['existing_source_index']);
                self::assertSame(Digest::uuid($target['identity'],$source,$slot+1), Digest::uuid($target['identity'],$new,$slot+1));
                $metadata = $projected['page']['blocks'][$slot]; unset($metadata['body'], $new['body']);
                self::assertSame($metadata, $new, 'Only the exact finite metadata transition may consume a historical box.');
            }
            if ($i > 0) {
                ++$added; self::assertSame([], $profile['bindings'][$i]['legacy_box_projection'] ?? []);
                self::assertSame(['header','left','left','right','right','footer','left'], array_column($lesson['blocks'],'column'));
                self::assertSame(['hero',$lesson['blocks'][1]['type'],'comparison-table','mistakes-grid','summary-list','navigation-chips','practice-set'], array_column($lesson['blocks'],'type'));
                self::assertNull($lesson['blocks'][6]['existing_source_index']);
            } else {
                self::assertSame('3e5975ed-f03b-5639-b9fe-f5f0f051f734', Digest::uuid($target['identity'],$target['after']['page']['blocks'][8],9));
                self::assertSame([], $profile['bindings'][$i]['appended_blocks']);
            }
            foreach ($target['after']['page']['blocks'] as $source) {
                ++$total; self::assertLessThanOrEqual(65535, strlen($source['body']));
                foreach (['layout','hero_image','content','columns','exercises','mistakes','summary_points','links'] as $field) {
                    self::assertArrayNotHasKey($field, $source, 'No historical secondary payload can compete with native body.');
                }
            }
            $slot = Package::PRACTICE_SOURCE_INDICES[$i]; $practice = $target['after']['page']['blocks'][$slot];
            self::assertSame('left', $practice['column']); self::assertSame(Package::LEVELS[$i], $practice['level'] ?? null);
            self::assertNotSame($lesson['display_level'], $practice['level'] ?? null, 'Display ranges are not exact SQL filters.');
        }
        self::assertSame(41, $continuing); self::assertSame(5, $added); self::assertSame(46, $total);
        foreach ($profile['artifacts'] as $path => $sha) {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $sha); self::assertNotSame(str_repeat('0',64), $sha);
            self::assertSame($sha, hash_file('sha256', $this->root().'/'.$path));
        }
    }

    public function test_forty_two_graded_tasks_transfer_eight_one_functions_without_reinventing_their_absent_ids(): void
    {
        [$before, , $master, $contract] = $this->release(); $graded = $transferred = 0; $ids = [];
        $oldOne = $before['targets'][0]['before']['page']['blocks'][8];
        self::assertSame('box', $oldOne['type']); self::assertSame(8, substr_count($oldOne['body'],'<li>'));
        foreach ($master['lessons'] as $i => $lesson) {
            $data = $this->practice($master,$i);
            foreach (['selects' => $i === 0 ? 8 : 2, 'choices' => 2, 'inputs' => 2, 'rephrase' => 0] as $group => $count) {
                self::assertCount($count, $data[$group]);
                foreach ($data[$group] as $task) {
                    ++$graded; self::assertArrayNotHasKey('example_original',$task); self::assertArrayNotHasKey('source_index',$task);
                    self::assertContains($task['answer'],$task['accepted']); self::assertNotSame('',Package::comparisonKey($task['answer']));
                    if ($i === 0 && $group === 'selects') { ++$transferred; self::assertArrayNotHasKey('id',$task); }
                    else { self::assertMatchesRegularExpression('/\Am77-[a-z0-9-]+\z/',$task['id']); $ids[]=$task['id']; }
                    foreach (['strict','punctuation_sensitive','m38_semantic_checks'] as $field) { self::assertArrayNotHasKey($field,$task); }
                    foreach ([...$task['accepted'], ...($task['options']??[])] as $value) {
                        $key = mb_strtolower(trim($value)); self::assertArrayHasKey($key,$task['feedback']);
                        self::assertNotSame('',trim($task['feedback'][$key]));
                        self::assertDoesNotMatchRegularExpression('/<(?:\/?[a-z]|[!?])|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{a\d+\}/iu',$task['feedback'][$key]);
                    }
                    if ($group !== 'inputs') {
                        $keys=array_map(Package::comparisonKey(...),$task['options']);
                        self::assertNotContains('',$keys); self::assertCount(count($keys),array_unique($keys));
                    }
                }
            }
            self::assertSame('Вправа 4. Побудуй речення',$data['linked_practice']['title']);
            self::assertSame(['4'],$data['linked_practice']['question_types']); self::assertSame('theory_links',$data['linked_practice']['source']);
            self::assertSame($contract['targets'][$i]['bank_seeders'],$data['linked_practice']['seeder_classes']);
            self::assertCount(1,$data['linked_practice']['seeder_classes']);
            self::assertDoesNotMatchRegularExpression('/поліглот/iu',implode(' ',array_intersect_key($data,array_flip(['title','select_title','choice_title','input_title']))));
            foreach ($data['inputs'] as $task) {
                self::assertArrayNotHasKey('prompt',$task); self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u',$task['after']);
                $groups=array_map('trim',explode('/',$task['before'])); self::assertGreaterThanOrEqual(2,count($groups));
                foreach ($groups as $group) { $words=preg_split('/\s+/u',$group,flags:PREG_SPLIT_NO_EMPTY); self::assertGreaterThanOrEqual(1,count($words)); self::assertLessThanOrEqual(3,count($words)); }
                self::assertNotSame(Package::comparisonKey($task['answer']),Package::comparisonKey(implode(' ',$groups)),'Authored groups start shuffled.');
            }
        }
        self::assertSame(42,$graded); self::assertSame(8,$transferred); self::assertCount(34,$ids); self::assertCount(34,array_unique($ids));
        self::assertSame(['one','ones','one','one','yours','ones','some','this one'],array_column($this->practice($master,0)['selects'],'answer'));
        self::assertContains('on',$this->practice($master,3)['selects'][0]['accepted']);
        self::assertContains('in',$this->practice($master,3)['selects'][1]['accepted']);
        self::assertContains('We walked toward the station.',$this->practice($master,3)['inputs'][0]['accepted']);
        self::assertContains('The kids are at home.',$this->practice($master,2)['inputs'][1]['accepted']);
        self::assertContains('Do you visit your grandparents on the weekend?',$this->practice($master,1)['inputs'][1]['accepted']);
        self::assertContains('On what does the decision depend?',$this->practice($master,4)['inputs'][0]['accepted']);
        self::assertContains('She picked the bag up.',$this->practice($master,5)['inputs'][1]['accepted']);
    }

    public function test_all_essential_teaching_stays_visible_basic_without_package_chosen_design_or_artificial_details(): void
    {
        [, , $master] = $this->release();
        foreach ($master['lessons'] as $lesson) {
            self::assertSame([],$lesson['details']); self::assertNotEmpty($lesson['point_detail_decisions']);
            foreach ($lesson['blocks'] as $block) {
                self::assertDoesNotMatchRegularExpression('/\Am[0-9]+-/',$block['type']);
                $walk=function(array $data) use (&$walk):void { foreach($data as $key=>$value) {
                    if(is_array($value)){$walk($value);continue;} if(!is_string($value)){continue;}
                    self::assertNotContains(mb_strtolower((string)$key),['style','class','css_class','stylesheet','view','native_view']);
                    self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong|em)\b|<\/?(?:script|style|iframe)\b|<[^>]+\s(?:class|style|on[a-z]+)\s*=/iu',$value);
                }}; $walk($block['data']);
            }
        }
    }

    public function test_mutated_scope_metadata_content_and_practice_fail_closed(): void
    {
        [$before,$patch,$master,$contract]=$this->release(); $original=[$before,$patch,$master,$contract];
        $mutations=[
            'missing-owner'=>static function(&$b,&$p,&$m,&$c):void{unset($m['lessons'][2]['identity']);},
            'foreign-locale'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][3]['locale']='en';},
            'foreign-category'=>static function(&$b,&$p,&$m,&$c):void{$p['targets'][1]['page_binding']['page_category_id']=20;},
            'range-as-filter'=>static function(&$b,&$p,&$m,&$c):void{$c['targets'][1]['level']='A1-A2';},
            'continuing-null-level-filled'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][2]['blocks'][3]['level']='A1';},
            'right-summary-moved-left'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][3]['blocks'][4]['column']='left';},
            'old-one-id-invented'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][0]['blocks'][8]['data']['selects'][0]['id']='m77-invented-old';},
            'old-one-position-lost'=>static function(&$b,&$p,&$m,&$c):void{array_pop($m['lessons'][0]['blocks'][8]['data']['selects']);},
            'duplicate-practice'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][4]['blocks'][]=$m['lessons'][4]['blocks'][6];},
            'appended-claimed-old'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][5]['blocks'][6]['existing_source_index']=5;},
            'secondary-source-drift'=>static function(&$b,&$p,&$m,&$c):void{$b['targets'][0]['before']['page']['blocks'][0]['content']['html'].=' Changed.';},
            'option-normalization-collision'=>static function(&$b,&$p,&$m,&$c):void{$t=&$m['lessons'][2]['blocks'][6]['data']['selects'][0];$t['options'][]='IN!';$t['feedback']['in!']='Повторення тієї самої форми.';},
            'empty-key'=>static function(&$b,&$p,&$m,&$c):void{$t=&$m['lessons'][1]['blocks'][6]['data']['inputs'][0];$t['answer']=' ';$t['accepted']=[' '];},
            'empty-omission'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][1]['blocks'][6]['data']['choices'][0]['accepted'][]='';},
            'literal-feedback-missing'=>static function(&$b,&$p,&$m,&$c):void{unset($m['lessons'][3]['blocks'][6]['data']['selects'][0]['feedback']['on']);},
            'html-feedback'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][2]['blocks'][6]['data']['selects'][0]['feedback']['in']='<b>Правильно.</b>';},
            'hidden-input-condition'=>static function(&$b,&$p,&$m,&$c):void{$t=&$m['lessons'][4]['blocks'][6]['data']['inputs'][0];$t['prompt']=$t['after'];$t['after']='';},
            'strict-grader-opt-in'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][5]['blocks'][6]['data']['inputs'][1]['punctuation_sensitive']=true;},
            'stored-design-selection'=>static function(&$b,&$p,&$m,&$c):void{$m['lessons'][3]['blocks'][1]['data']['stylesheet']='m77-custom.css';},
            'stale-after-body'=>static function(&$b,&$p,&$m,&$c):void{$p['targets'][2]['after']['page']['blocks'][1]['body'].="\n";},
        ];
        foreach($mutations as $case=>$change){$b=$before;$p=$patch;$m=$master;$c=$contract;$change($b,$p,$m,$c);
            $this->refuses(static function()use($case,$b,$p,$m,$c):void{
                if(!in_array($case,['stale-after-body','foreign-category'],true)){
                    foreach($m['lessons'] as $i=>$lesson){$p['targets'][$i]['after']=Package::definition($lesson,$b['targets'][$i]['before']);}
                }
                Package::validate($b,$p,$m,$c);
            },$case);
        }
        self::assertSame($original,array_slice($this->release(),0,4));
        $profile=Package::profile(); $first=array_key_first($profile['artifacts']);$profile['artifacts'][$first]=str_repeat('0',64);
        $this->refuses(fn()=>(new TheoryNativeContentRelease($profile))->load($this->root()),'stale raw artifact pin');
    }
}
