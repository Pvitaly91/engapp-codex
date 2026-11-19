<?php
declare(strict_types=1);
namespace Tests\Unit;

use App\Support\FinitePracticeQualityCatalog;
use App\Support\QuestionsNegationsPracticeQuality as Quality;
use PHPUnit\Framework\TestCase;

/** Canonical-data checks; all CEFR/grammar/candidate decisions also receive editorial review. */
final class QuestionsNegationsAuthorQualityTest extends TestCase
{
    private function clean(string $s,string $id,bool $gap = false): void
    {
        self::assertNotSame('',trim($s),$id); self::assertSame($s,trim($s),$id);
        if ($gap) { $s = str_replace('{a1}','',$s); }
        self::assertDoesNotMatchRegularExpression('/<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);|\{[^}]*\}|_{2,}/iu',$s,$id);
    }
    public function test_twelve_topics_have_six_gaps_and_three_translations_at_each_of_six_levels(): void
    {
        self::assertCount(12,Quality::TOPICS); $uuids = [];
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic);
            self::assertSame($topic,$source['topic_key']); self::assertCount(54,$source['questions']);
            self::assertDoesNotMatchRegularExpression('/polyglot|поліглот/iu',$source['title']);
            foreach (Quality::LEVELS as $level) {
                $rows = array_filter($source['questions'],fn($r) => $r['level'] === $level);
                self::assertCount(9,$rows,$topic.'/'.$level);
                self::assertCount(6,array_filter($rows,fn($r) => $r['kind'] === 'gap'));
                self::assertCount(3,array_filter($rows,fn($r) => $r['kind'] === 'compose'));
            }
            foreach ($source['questions'] as $r) { $uuid = Quality::questionUuid($topic,$r['id']); self::assertArrayNotHasKey($uuid,$uuids); $uuids[$uuid] = true; }
        }
        self::assertCount(648,$uuids);
    }
    public function test_ukrainian_contextual_hints_clean_fields_and_reviewed_complete_chunks(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $r) {
            $id = $topic.'/'.$r['id'];
            $r['uk'] ??= $r['kind'] === 'compose' ? $r['question'] : '';
            foreach (['question','target','uk','hint_uk'] as $field) { $this->clean($r[$field],$id.'/'.$field,$field === 'question' && $r['kind'] === 'gap'); }
            foreach (['uk','hint_uk'] as $field) {
                self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$r[$field],$id.'/'.$field);
                self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u',$r[$field],$id.'/'.$field);
            }
            self::assertGreaterThanOrEqual(20,mb_strlen($r['hint_uk']),$id);
            self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$r['target'],$id);
            self::assertSame($r['target'],implode(' ',$r['groups']),$id);
            self::assertGreaterThanOrEqual(2,count($r['groups']),$id);
            foreach ($r['groups'] as $group) { self::assertLessThanOrEqual(3,count(preg_split('/\s+/u',$group)),$id); self::assertMatchesRegularExpression('/\pL/u',$group,$id); }
            if ($r['kind'] === 'compose') { self::assertSame($r['uk'],$r['question'],$id); }
        }
    }
    public function test_gap_answers_reconstruct_exact_targets_without_repeated_visible_words_and_options_are_short(): void
    {
        $synonyms = 0;
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $r) {
            if ($r['kind'] !== 'gap') { continue; } $id = $topic.'/'.$r['id'];
            self::assertSame(1,substr_count($r['question'],'{a1}'),$id);
            self::assertSame($r['target'],str_replace('{a1}',$r['answer'],$r['question']),$id);
            self::assertCount(3,$r['options']); self::assertCount(3,array_unique($r['options'])); self::assertContains($r['answer'],$r['options']);
            foreach (array_merge($r['options'],$r['answer_synonyms'] ?? []) as $option) {
                $this->clean($option,$id.'/option'); self::assertLessThanOrEqual(5,count(preg_split('/\s+/u',$option)),$id);
                self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$option,$id);
            }
            foreach ($r['answer_synonyms'] ?? [] as $alias) { ++$synonyms; self::assertNotContains($alias,array_values(array_diff($r['options'],[$r['answer']])),$id); }
            // The entire reviewed missing phrase is one source marker. Per-word fields
            // are a presentation of it, not a license to accept its first word alone.
            self::assertNotSame('',$r['answer']);
        }
        self::assertGreaterThan(8,$synonyms);
    }
    public function test_every_role_slot_has_four_varied_candidates_and_all_alias_combinations_are_reachable(): void
    {
        $choiceSets = []; $aliases = 0;
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $s = Quality::source($topic); $compiled = Quality::compile($topic,'builder');
            foreach ($s['choice_pools'] as $role => $values) {
                self::assertGreaterThanOrEqual(6,count(array_unique($values)),$topic.'/'.$role);
                foreach ($values as $v) { $this->clean($v,$topic.'/'.$role); self::assertLessThanOrEqual(3,count(preg_split('/\s+/u',$v))); }
            }
            $rows = array_column($compiled['questions'],null,'author_id');
            foreach ($s['questions'] as $r) {
                if ($r['kind'] !== 'compose') { continue; } $id = $topic.'/'.$r['id']; $q = $rows[$r['id']];
                self::assertSame($r['target'],implode(' ',$r['compose_slots'])); self::assertCount(count($r['compose_slots']),$r['slot_roles']);
                $variants = [''];
                // Authored groups still test the whitelist; visible fields are individual words.
                foreach ($q['markers'] as $markerKey => $compiledMarker) {
                    $options = $q['options_by_marker'][$markerKey];
                    self::assertCount(4, $options); self::assertCount(4, array_unique($options));
                    self::assertContains(\App\Support\FiniteBuilderWordSlots::clean($compiledMarker['answer']), $options);
                    $choiceSets[json_encode($options)] = true;
                    foreach ($options as $option) { self::assertDoesNotMatchRegularExpression('/\\s/u', $option, $id); }
                }
                foreach ($r['compose_slots'] as $i => $slot) {
                    self::assertArrayHasKey($r['slot_roles'][$i],$s['choice_pools'],$id);
                    $next = [];
                    foreach ($variants as $prefix) foreach (array_merge([$slot],FinitePracticeQualityCatalog::displayAliasesForToken($slot,$r['display_synonyms'] ?? [])) as $v) { $next[] = trim($prefix.' '.$v); }
                    $variants = $next;
                }
                $accepted = array_merge([$r['target']],$r['accepted_targets'] ?? []); $aliases += count($accepted)-1;
                foreach ($accepted as $v) { self::assertContains($v,$variants,$id.' unreachable alias'); $this->clean($v,$id); }
                if (isset($r['coupled_variants'])) {
                    self::assertSame('negative-questions',$topic);
                    self::assertSame('interrogative_negation_position',$r['coupled_variants']['kind']);
                    self::assertLessThan(count($variants),count($accepted),$id.' must not accept mismatched contracted/full hybrids');
                } else { foreach ($variants as $v) { self::assertContains($v,$accepted,$id.' incomplete whitelist'); } }
            }
        }
        self::assertGreaterThan(80,count($choiceSets)); self::assertGreaterThan(8,$aliases);
    }
    public function test_new_targets_do_not_duplicate_each_other_or_any_historical_bank_in_this_topic(): void
    {
        $seen = []; $canonical = [];
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (['standard','builder'] as $kind) {
            foreach (Quality::originalDefinition($topic,$kind)['questions'] as $q) {
                $target = $q['target_text'] ?? $q['question'];
                foreach ($q['markers'] ?? [] as $marker => $data) { $target = str_replace('{'.$marker.'}',$data['answer'],$target); }
                $seen[Quality::normalized($target)] = 'old/'.$topic.'/'.$kind;
            }
        }
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $r) {
            $id = $topic.'/'.$r['id'];
            foreach (array_unique(array_merge([$r['target']],$r['accepted_targets'] ?? [])) as $v) {
                // Short responses necessarily recur in ordinary English. Their
                // actual question/context, not an isolated “Yes, I can”, is the task.
                $key = Quality::normalized($topic === 'answers' && $r['kind'] === 'compose' ? $r['question'].' '.$v : $v);
                self::assertArrayNotHasKey($key,$seen,$id.' duplicates '.$v.' in '.($seen[$key] ?? ''));
                $seen[$key] = $id;
            }
            $canonical[$id] = array_unique(explode(' ',Quality::normalized($topic === 'answers' && $r['kind'] === 'compose' ? $r['question'].' '.$r['target'] : $r['target'])));
        }
        foreach ($canonical as $a => $words) foreach ($canonical as $b => $other) {
            if (strcmp($a,$b) >= 0) { continue; }
            $score = count(array_intersect($words,$other))/count(array_unique(array_merge($words,$other)));
            self::assertLessThan(0.90,$score,$a.' and '.$b.' are near-duplicates');
        }
    }
    public function test_original_metadata_is_pinned_and_exact_locale_projection_never_mutates_original_questions(): void
    {
        self::assertCount(Quality::SAVED_TEST_COUNT,Quality::metadataPatches());
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (['standard','builder'] as $kind) {
            $d = Quality::originalDefinition($topic,$kind); $after = Quality::definitionMetadata($d,'uk');
            self::assertSame($d['questions'],$after['questions']); self::assertSame($d,Quality::definitionMetadata($d,'en'));
            self::assertSame([Quality::mapping($topic)[$kind]['class']],$after['saved_test']['filters']['seeder_classes']);
            $changed = $d; $changed['questions'][0]['question'] .= ' '; self::assertSame($changed,Quality::definitionMetadata($changed,'uk'));
        }
    }
    public function test_exact_reviewed_question_and_short_reply_surfaces_bind_revision_and_cache_membership(): void
    {
        $strictRows=0;
        foreach(Quality::questions() as $source){
            $payload=['uuid'=>$source['uuid'],'question'=>$source['question'],'type'=>(string)$source['type'],
                'answer_map'=>array_map(static fn($m)=>$m['answer'],$source['markers']),
                'chosen'=>['kept'],'manualInputsBySlot'=>['draft'],'attemptsBySlot'=>[2],'done'=>false];
            $after=Quality::decorate($payload);
            foreach(['chosen','manualInputsBySlot','attemptsBySlot','done'] as $key)self::assertSame($payload[$key],$after[$key]);
            $expectedTopic=in_array($source['source'],['questions_negations_quality_answers','questions_negations_quality_tags','questions_negations_quality_negative_questions'],true);
            if(!$expectedTopic){self::assertArrayNotHasKey('qnq_strict_slot_surfaces',$after);continue;}
            ++$strictRows;self::assertArrayHasKey('qnq_strict_slot_surfaces',$after);
            $policy=$after['qnq_strict_slot_surfaces'];self::assertSame($after['psp_content_revision'],$policy['revision']);
            foreach($policy['answers_by_marker'] as $marker=>$values){
                self::assertSame(array_values(array_unique(array_merge([$source['markers'][$marker]['answer']],$source['answer_synonyms'][$marker]??[]))),$values);
            }
            $missing=$after;unset($missing['qnq_strict_slot_surfaces']);self::assertTrue(Quality::cachedNeedsRefresh([$missing]));
            $changed=$after;$changed['qnq_strict_slot_surfaces']['revision']=str_repeat('0',64);self::assertTrue(Quality::cachedNeedsRefresh([$changed]));
            $changed=$after;$key=array_key_first($changed['qnq_strict_slot_surfaces']['answers_by_marker']);$changed['qnq_strict_slot_surfaces']['answers_by_marker'][$key][]='foreign';self::assertTrue(Quality::cachedNeedsRefresh([$changed]));
            $changed=$payload;$changed['question'].=' altered';self::assertSame($changed,Quality::decorate($changed));
        }
        self::assertSame(162,$strictRows);
    }
    public function test_lifted_pronoun_hint_is_an_exact_render_only_projection_without_author_or_progress_changes(): void
    {
        $id='qnq-question-forms-b1-g03';$source=Quality::questions()[$id];$original=$source;
        $old='Потрібне заперечне питання в минулому пасиві; обери одну скорочену допоміжну форму.';
        $new='Постав заперечне питання в минулому пасиві; у пропуску потрібні скорочена допоміжна форма та займенник «ми».';
        self::assertSame('b6b20905ad04f42be67d5db9fe30bb6a173b994307b7cf10ad9fc873600592ab',hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)));
        self::assertSame($old,$source['verb_hint']);self::assertSame($old,$source['markers']['a1']['verb_hint']);
        $payload=['uuid'=>$id,'question'=>$source['question'],'type'=>(string)$source['type'],
            'answer_map'=>array_map(static fn($m)=>$m['answer'],$source['markers']),'verb_hint'=>$old,'verb_hints'=>['a1'=>$old],
            'chosen'=>[null],'manualInputsBySlot'=>['unfinished'],'manualWordIndexBySlot'=>[0],'attemptsBySlot'=>[2],'done'=>false];
        $after=Quality::decorate($payload);self::assertSame($new,$after['verb_hint']);self::assertSame(['a1'=>$new],$after['verb_hints']);
        self::assertSame($after['psp_content_revision'],'b6b20905ad04f42be67d5db9fe30bb6a173b994307b7cf10ad9fc873600592ab');
        foreach(['question','answer_map','chosen','manualInputsBySlot','manualWordIndexBySlot','attemptsBySlot','done'] as $key)self::assertSame($payload[$key],$after[$key]);
        self::assertFalse(Quality::cachedNeedsRefresh([$after]));
        foreach(['verb_hint','verb_hints'] as $field){$stale=$after;$stale[$field]=$payload[$field];self::assertTrue(Quality::cachedNeedsRefresh([$stale]));}
        $changed=$payload;$changed['question'].=' changed';self::assertSame($changed,Quality::decorate($changed));
        $changed=$payload;$changed['answer_map']['a1']='were';self::assertSame($changed,Quality::decorate($changed));
        $projection=new \ReflectionMethod(Quality::class,'presentedSubjectHint');
        self::assertSame($new,$projection->invoke(null,$source));
        foreach(['uuid'=>'foreign','question'=>$source['question'].' ','verb_hint'=>$old.' '] as $field=>$value){$changedSource=$source;$changedSource[$field]=$value;self::assertNull($projection->invoke(null,$changedSource));}
        self::assertSame($original,Quality::questions()[$id]);
        self::assertMatchesRegularExpression('/«ми»/u',$new);self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u',$new);
    }
    public function test_all_hundred_authored_hint_clarifications_are_lexically_useful_exact_and_hint_only(): void
    {
        $file=dirname(__DIR__,2).'/'.Quality::HINT_CLARIFICATIONS_SOURCE;
        self::assertSame(Quality::HINT_CLARIFICATIONS_SHA,hash_file('sha256',$file));
        $data=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(['verb_hint','verb_hints.a1'],$data['fields']);
        $entries=Quality::hintClarifications();self::assertCount(100,$entries);self::assertCount(100,$data['entries']);
        $projection=new \ReflectionMethod(Quality::class,'projectPresentedHints');$all=Quality::questions();$covered=0;$unlisted=0;
        foreach($all as$uuid=>$source){
            $original=$source;$entry=$entries[$uuid]??null;
            $payload=['uuid'=>$uuid,'question'=>$source['question'],'type'=>(string)$source['type'],
                'answer_map'=>array_map(static fn($m)=>$m['answer'],$source['markers']),
                'answers'=>array_values(array_map(static fn($m)=>$m['answer'],$source['markers'])),
                'accepted_answers_by_marker'=>$source['answer_synonyms'],'verb_hint'=>$source['verb_hint'],
                'verb_hints'=>array_map(static fn($m)=>$m['verb_hint'],$source['markers']),
                'chosen'=>[null],'manualInputsBySlot'=>['unfinished'],'attemptsBySlot'=>[2],'done'=>false,'score'=>3];
            if($entry===null){++$unlisted;self::assertSame($payload,$projection->invoke(null,$payload,$source),$uuid);continue;}
            ++$covered;self::assertSame('0',(string)$source['type']);self::assertSame(['a1'],array_keys($source['markers']));
            self::assertSame(Quality::questionUuid($entry['topic'],$entry['author_id']),$uuid);
            self::assertSame($entry['source_row_sha256'],hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)));
            self::assertSame($entry['before_hint_uk'],$source['verb_hint']);self::assertSame($entry['before_hint_uk'],$source['markers']['a1']['verb_hint']);
            self::assertNotSame($entry['before_hint_uk'],$entry['after_hint_uk']);
            self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u',$entry['after_hint_uk']);
            self::assertDoesNotMatchRegularExpression('/[A-Za-z]|<[^>]*>|\{[^}]*\}/u',$entry['after_hint_uk']);
            self::assertNotEmpty($entry['lexical_cues_uk']);
            foreach($entry['lexical_cues_uk']as$cue)self::assertStringContainsString(mb_strtolower($cue),mb_strtolower($entry['after_hint_uk']));
            $expected=$payload;$expected['verb_hint']=$entry['after_hint_uk'];$expected['verb_hints']['a1']=$entry['after_hint_uk'];
            self::assertSame($expected,$projection->invoke(null,$payload,$source),$uuid.' only public hint fields may change');
            $after=Quality::decorate($payload);self::assertSame($entry['after_hint_uk'],$after['verb_hint']);self::assertSame($entry['after_hint_uk'],$after['verb_hints']['a1']);
            self::assertSame($source['target_text'],implode(' ',$after['reorder_groups']));
            $variantFields=(new \ReflectionMethod(Quality::class,'answerVariantFields'))->invoke(null,$source);
            self::assertSame($variantFields['reorder_accepted_answers']??$source['accepted_target_texts'],$after['reorder_accepted_answers']);
            self::assertSame($payload['answer_map'],$after['answer_map']);
            foreach(['chosen','manualInputsBySlot','attemptsBySlot','done','score']as$key)self::assertSame($payload[$key],$after[$key]);
            self::assertFalse(Quality::cachedNeedsRefresh([$after]));
            $stale=$after;$stale['verb_hint']=$entry['before_hint_uk'];self::assertTrue(Quality::cachedNeedsRefresh([$stale]));
            $stale=$after;unset($stale['verb_hints']['a1']);self::assertTrue(Quality::cachedNeedsRefresh([$stale]));
            foreach(['uuid'=>'foreign','question'=>$source['question'].' changed','verb_hint'=>$source['verb_hint'].' changed','translation_uk'=>'changed']as$field=>$value){
                $bad=$source;$bad[$field]=$value;self::assertSame($payload,$projection->invoke(null,$payload,$bad),$uuid.'/'.$field.' drift');
            }
            $bad=$source;$bad['markers']['a1']['answer']='foreign';self::assertSame($payload,$projection->invoke(null,$payload,$bad));
            self::assertSame($original,Quality::questions()[$uuid]);
        }
        self::assertSame(100,$covered);self::assertSame(548,$unlisted);
    }
    public function test_two_reviewed_lexical_aliases_change_only_four_fields_and_leave_other_646_rows_untouched(): void
    {
        $file=dirname(__DIR__,2).'/'.Quality::ANSWER_VARIANTS_SOURCE;
        self::assertSame(Quality::ANSWER_VARIANTS_SHA,hash_file('sha256',$file));$entries=Quality::answerVariants();self::assertCount(2,$entries);
        $projection=new \ReflectionMethod(Quality::class,'projectAnswerVariants');$covered=0;$unlisted=0;
        foreach(Quality::questions()as$uuid=>$source){
            $payload=['uuid'=>$uuid,'question'=>$source['question'],'type'=>(string)$source['type'],
                'answer_map'=>array_map(static fn($m)=>$m['answer'],$source['markers']),
                'answers'=>array_values(array_map(static fn($m)=>$m['answer'],$source['markers'])),
                'accepted_answers_by_marker'=>['a1'=>\App\Support\AcceptedAnswerVariants::for($source['markers']['a1']['answer'])],
                'accepted_answers'=>[\App\Support\AcceptedAnswerVariants::for($source['markers']['a1']['answer'])],
                'answer_synonyms_by_marker'=>$source['display_answer_synonyms']??[],
                'reorder_accepted_answers'=>$source['accepted_target_texts'],'verb_hint'=>'kept hint','verb_hints'=>['a1'=>'kept hint'],
                'chosen'=>[null],'manualInputsBySlot'=>['draft'],'attemptsBySlot'=>[2],'done'=>false,'score'=>3,
                'options'=>['preserved'],'qnq_strict_slot_surfaces'=>null,'psp_content_revision'=>'preserved'];
            $entry=$entries[$uuid]??null;
            if($entry===null){++$unlisted;self::assertSame($payload,$projection->invoke(null,$payload,$source),$uuid);continue;}
            ++$covered;self::assertSame($entry['source_row_sha256'],hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)));
            self::assertSame($entry['primary_answer'],$source['markers']['a1']['answer']);self::assertSame([],$source['answer_synonyms']['a1']);
            $accepted=\App\Support\AcceptedAnswerVariants::for($entry['primary_answer']);$full=$source['accepted_target_texts'];
            foreach($entry['added_answers']as$alias)foreach(\App\Support\AcceptedAnswerVariants::for($alias)as$variant){$accepted[]=$variant;$full[]=str_replace('{a1}',$variant,$source['question']);}
            $expected=$payload;$expected['accepted_answers_by_marker']=['a1'=>array_values(array_unique($accepted))];$expected['accepted_answers']=array_values($expected['accepted_answers_by_marker']);
            $expected['answer_synonyms_by_marker']=['a1'=>$entry['added_answers']];$expected['reorder_accepted_answers']=array_values(array_unique($full));
            self::assertSame($expected,$projection->invoke(null,$payload,$source),$uuid.' only four approved fields change');
            $after=Quality::decorate($payload);self::assertFalse(Quality::cachedNeedsRefresh([$after]));
            self::assertSame($entry['source_row_sha256'],$after['psp_content_revision']);self::assertSame($payload['answer_map'],$after['answer_map']);
            self::assertSame(Quality::hintClarifications()[$uuid]['after_hint_uk'],$after['verb_hint']);
            foreach(['accepted_answers_by_marker','accepted_answers','answer_synonyms_by_marker','reorder_accepted_answers']as$field){self::assertSame($expected[$field],$after[$field]);$stale=$after;$stale[$field]=[];self::assertTrue(Quality::cachedNeedsRefresh([$stale]));}
            foreach(['question'=>$source['question'].' changed','verb_hint'=>$source['verb_hint'].' changed','uuid'=>'foreign']as$field=>$value){$bad=$source;$bad[$field]=$value;self::assertSame($payload,$projection->invoke(null,$payload,$bad));}
            $bad=$source;$bad['markers']['a1']['answer']='foreign';self::assertSame($payload,$projection->invoke(null,$payload,$bad));
            self::assertStringNotContainsString('nullify',json_encode($after['accepted_answers_by_marker']));
        }
        self::assertSame(2,$covered);self::assertSame(646,$unlisted);
    }
}
