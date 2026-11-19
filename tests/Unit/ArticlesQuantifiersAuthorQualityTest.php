<?php
declare(strict_types=1);
namespace Tests\Unit;

use App\Support\ArticlesQuantifiersPracticeQuality as Quality;
use App\Support\FinitePracticeQualityCatalog;
use PHPUnit\Framework\TestCase;

/** Pure canonical author/compiled-bank checks: no kernel, .env, DB or learner storage. */
final class ArticlesQuantifiersAuthorQualityTest extends TestCase
{
    public function test_historical_and_foreign_ids_never_gain_the_new_quality_presentation(): void
    {
        foreach (['pot-v3-a1-01','pop-poly-a1-01','aqv-time-a1-g01-foreign','course-aqv-time-a1-g01'] as $uuid) {
            $old=['uuid'=>$uuid,'question'=>'Historical {a1}.','type'=>'0','answer_map'=>['a1'=>'unchanged'],
                'chosen'=>['saved'],'manualInputsBySlot'=>['typed draft'],'done'=>false];
            self::assertSame($old, Quality::decorate($old));
            self::assertSame($old, Quality::restoreClarifiedHints($old,'uk'));
            self::assertFalse(Quality::cachedNeedsRefresh([$old]));
        }
    }

    public function test_exact_smaller_row_contract_does_not_relax_historical_catalogs(): void
    {
        $method = new \ReflectionMethod(FinitePracticeQualityCatalog::class, 'requiredRowsPerLevel');
        foreach (\App\Support\FinitePracticeQualityCatalogs::CATALOGS as $catalog) {
            $actual = $method->invoke(null);
            self::assertSame(['gap'=>6,'compose'=>[3,5]], $actual);
            $inherited = new \ReflectionMethod($catalog, 'requiredRowsPerLevel');
            $expected = in_array($catalog, [Quality::class, \App\Support\VocabCollocationsPracticeQuality::class, \App\Support\SentenceStructurePracticeQuality::class, \App\Support\FormalEnglishPracticeQuality::class, \App\Support\RelativeClausesPracticeQuality::class, \App\Support\AcademicEnglishPracticeQuality::class, \App\Support\MixedRevisionPracticeQuality::class, \App\Support\VerbPatternsPracticeQuality::class, \App\Support\PrepositionsPhrasalPracticeQuality::class, \App\Support\ClausesLinkingPracticeQuality::class, \App\Support\CommonMistakesPracticeQuality::class, \App\Support\SentenceTransformationsPracticeQuality::class], true)
                ? ['gap'=>2,'compose'=>[1]] : ['gap'=>6,'compose'=>[3,5]];
            self::assertSame($expected, $inherited->invoke(null), $catalog);
        }
        foreach ([['uk'],42,false] as $invalidLocale) {
            $foreign=['defaults'=>['default_locale'=>$invalidLocale], 'seeder'=>['class'=>'ForeignSeeder']];
            foreach (\App\Support\FinitePracticeQualityCatalogs::CATALOGS as $catalog) {
                self::assertSame($foreign,$catalog::definitionMetadata($foreign,'uk'),$catalog.' malformed locale must preserve fallback');
            }
        }
        self::assertSame(['gap'=>2,'compose'=>[1]], (new \ReflectionMethod(Quality::class,'requiredRowsPerLevel'))->invoke(null));
        foreach (['rsq-v1','cqq-v1','mvq-v1','pvq-v1','woq-v1','foreign'] as $profile) {
            self::assertSame(9, \App\Support\SentenceReorderQuestionFactory::compactQuestionsPerLevel($profile));
        }
        self::assertSame(3, \App\Support\SentenceReorderQuestionFactory::compactQuestionsPerLevel(Quality::REVISION));
    }

    private function clean(string $text, string $id, bool $gap = false): void
    {
        self::assertNotSame('', trim($text), $id);
        self::assertSame(trim($text), $text, $id);
        if ($gap) { $text = str_replace('{a1}', '', $text); }
        self::assertDoesNotMatchRegularExpression('/<[^>]*>|&(?:#\d+|#x[\da-f]+|[a-z][a-z\d]+);|\{[^}]*\}|_{2,}/iu', $text, $id);
    }

    private static function surfaces(array $row): array
    {
        $surfaces=array_merge([$row['target']],$row['accepted_targets']??[]);
        if($row['kind']==='gap')foreach($row['answer_synonyms']??[]as$alias)$surfaces[]=str_replace('{a1}',$alias,$row['question']);
        return array_values(array_unique($surfaces));
    }

    public function test_two_topics_have_exactly_two_gaps_and_one_translation_per_level(): void
    {
        self::assertCount(2, Quality::TOPICS); $seen = [];
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic); self::assertCount(18, $source['questions']);
            self::assertDoesNotMatchRegularExpression('/polyglot|поліглот/iu', $source['title']);
            foreach (Quality::LEVELS as $level) {
                $rows = array_filter($source['questions'], static fn ($r) => $r['level'] === $level);
                self::assertCount(3, $rows, $topic.'/'.$level);
                self::assertCount(2, array_filter($rows, static fn ($r) => $r['kind'] === 'gap'));
                self::assertCount(1, array_filter($rows, static fn ($r) => $r['kind'] === 'compose'));
            }
            foreach ($source['questions'] as $row) {
                $id = $topic.'/'.$row['id']; $uuid = Quality::questionUuid($topic, $row['id']);
                self::assertArrayNotHasKey($uuid, $seen); $seen[$uuid] = true;
                foreach (['question','target','uk','hint_uk'] as $field) { $this->clean($row[$field], $id, $field === 'question' && $row['kind'] === 'gap'); }
                foreach (['uk','hint_uk'] as $field) {
                    self::assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $row[$field], $id);
                    self::assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $row[$field], $id);
                }
                self::assertGreaterThanOrEqual(12, mb_strlen($row['hint_uk']), $id);
                self::assertSame($row['target'], implode(' ', $row['groups']), $id);
                foreach ($row['groups'] as $group) { self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $group)), $id); }
                if ($row['kind'] === 'gap') {
                    self::assertSame(1, substr_count($row['question'], '{a1}'), $id);
                    self::assertSame($row['target'], str_replace('{a1}', $row['answer'], $row['question']), $id);
                    self::assertCount(3, $row['options']); self::assertCount(3, array_unique($row['options']));
                    self::assertContains($row['answer'], $row['options']);
                    foreach ($row['options'] as $option) { $this->clean($option, $id); self::assertLessThanOrEqual(6, count(preg_split('/\s+/u', $option)), $id); }
                    foreach ($row['answer_synonyms'] ?? [] as $alias) { self::assertNotContains($alias, array_slice($row['options'], 1), $id); }
                }
            }
        }
        self::assertCount(36, $seen);
    }

    public function test_every_compose_slot_has_four_varied_short_reviewed_choices_and_reachable_aliases(): void
    {
        $sets = []; $synonyms = 0;
        foreach (array_keys(Quality::TOPICS) as $topic) {
            $source = Quality::source($topic); $compiled = array_column(Quality::compile($topic, 'builder')['questions'], null, 'author_id');
            foreach ($source['choice_pools'] as $pool) {
                self::assertGreaterThanOrEqual(6, count(array_unique($pool)));
                foreach ($pool as $value) { $this->clean($value, $topic); self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', $value))); }
            }
            foreach ($source['questions'] as $row) {
                if ($row['kind'] !== 'compose') { continue; } $id = $topic.'/'.$row['id']; $q = $compiled[$row['id']];
                self::assertSame($row['target'], implode(' ', $row['compose_slots']));
                self::assertCount(count($row['compose_slots']), $row['slot_roles']); $variants = [''];
                // Authored groups still test the whitelist; visible fields are individual words.
                foreach ($q['markers'] as $markerKey => $compiledMarker) {
                    $options = $q['options_by_marker'][$markerKey];
                    self::assertCount(4, $options); self::assertCount(4, array_unique($options));
                    self::assertContains(\App\Support\FiniteBuilderWordSlots::clean($compiledMarker['answer']), $options);
                    $sets[json_encode($options)] = true;
                    foreach ($options as $option) { self::assertDoesNotMatchRegularExpression('/\\s/u', $option, $id); }
                }
                foreach ($row['compose_slots'] as $i => $slot) {
                    $next = [];
                    foreach ($variants as $prefix) foreach (array_merge([$slot], FinitePracticeQualityCatalog::displayAliasesForToken($slot, $row['display_synonyms'] ?? [])) as $v) { $next[] = trim($prefix.' '.$v); }
                    $variants = $next;
                }
                $accepted = array_unique(array_merge([$row['target']], $row['accepted_targets'] ?? [])); $synonyms += count($accepted) - 1;
                $variantKeys = array_map(Quality::normalized(...), $variants);
                $acceptedKeys = array_map(Quality::normalized(...), $accepted);
                foreach ($accepted as $v) { self::assertContains(Quality::normalized($v), $variantKeys, $id.' unreachable full-sentence alternative'); }
                foreach ($variants as $v) { self::assertContains(Quality::normalized($v), $acceptedKeys, $id.' missing whole-sentence whitelist'); }
            }
        }
        self::assertGreaterThan(35, count($sets)); self::assertGreaterThan(0, $synonyms);
    }

    public function test_active_author_targets_have_no_exact_or_near_duplicates_between_levels_and_topics(): void
    {
        $seen = []; $words = [];
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (Quality::source($topic)['questions'] as $row) {
            $id = $topic.'/'.$row['id'];
            foreach (self::surfaces($row) as $text) {
                $key = Quality::normalized($text);
                if (isset($seen[$key])) { self::assertSame($id, $seen[$key], $id.' repeats '.$seen[$key].': '.$text); }
                $seen[$key] = $id;
                $words[$key]=array_unique(explode(' ',$key));
            }
        }
        $ids = array_keys($words); $near = [];
        foreach ($ids as $i => $a) foreach (array_slice($ids, $i + 1) as $b) {
            if($seen[$a]===$seen[$b])continue;
            $dice = 2 * count(array_intersect($words[$a], $words[$b])) / (count($words[$a]) + count($words[$b]));
            if ($dice >= 0.84) { $near[] = $a.' / '.$b; }
        }
        self::assertSame([], $near, 'Near-duplicate active sentences require a content review');
    }


    public function test_finite_authors_do_not_recycle_any_frozen_legacy_question(): void
    {
        $reserved=[]; $definitions=[]; $rawCount=0;
        foreach(array_keys(Quality::TOPICS) as $topic) foreach(['standard','builder'] as $kind) {
            $old=Quality::originalDefinition($topic,$kind);
            $definitions[$old['seeder']['class']]=$old;
        }
        foreach(array_keys(Quality::TOPICS) as $topic) foreach(Quality::mapping($topic)['additional_originals']??[] as $binding) {
            $definitions[$binding['class']]=json_decode(file_get_contents(dirname(__DIR__,2).'/'.$binding['path']),true,flags:JSON_THROW_ON_ERROR);
        }
        foreach($definitions as $class=>$old) {
            $rawCount+=count($old['questions']);
            foreach($old['questions'] as $row) {
                $text=$row['target_text']??(isset($row['tokens_correct'])?implode(' ',$row['tokens_correct']):($row['answers']['a1']['answer']??''));
                self::assertIsString($text,$class.'/'.$row['uuid']);self::assertNotSame('',$text,$class.'/'.$row['uuid']);
                foreach($row['markers']??[] as $marker=>$data) { $text=str_replace('{'.$marker.'}',$data['answer'],$text); }
                if (!preg_match('/[А-Яа-яІіЇїЄєҐґ]|\\{[^}]+\\}/u',$text)) { $reserved[Quality::normalized($text)]=$class.'/'.$row['uuid']; }
            }
        }
        // Exact frozen inventory: eight all-level banks and four exact captured course banks.
        self::assertCount(6,$definitions); self::assertSame(384,$rawCount);
        $near=[];
        foreach(array_keys(Quality::TOPICS) as $topic) foreach(Quality::source($topic)['questions'] as $row) {
            foreach(self::surfaces($row) as $text) {
                self::assertArrayNotHasKey(Quality::normalized($text),$reserved,$topic.'/'.$row['id'].' repeats a frozen question');
                $words=array_unique(explode(' ',Quality::normalized($text)));
                foreach($reserved as$key=>$owner){$other=array_unique(explode(' ',$key));$dice=2*count(array_intersect($words,$other))/(count($words)+count($other));if($dice>=0.84)$near[]=$topic.'/'.$row['id'].' resembles '.$owner;}
            }
        }
        self::assertSame([],$near,'A cosmetic change must not recycle a frozen question');
    }

    public function test_active_author_targets_do_not_repeat_native_tasks_or_the_five_question_widgets(): void
    {
        $root=dirname(__DIR__,2); $reserved=[];
        $previous=\Illuminate\Container\Container::getInstance();
        $app=new \Illuminate\Foundation\Application($root);
        $app->instance('config',new \Illuminate\Config\Repository(['app'=>['locale'=>'uk']]));
        try {
            foreach ([\App\Support\ArticlesQuantifiersPracticeSource::class,\App\Support\ArticlesQuantifiersPracticeSupplement::class,\App\Support\ArticlesQuantifiersWidgetHints::class] as $source) {
                self::assertSame($source::SOURCE_SHA,hash_file('sha256',$root.'/'.$source::SOURCE));
            }
            $native=\App\Support\ArticlesQuantifiersPracticeSupplement::load();
            foreach ($native['targets'] as $target) {
                foreach ($target['added'] as $row) foreach (array_unique(array_merge([$row['answer']],$row['accepted']??[])) as $answer) {
                    $text=$row['kind']==='gap'?str_replace('{a1}',$answer,$row['condition']):$answer;
                    $reserved[Quality::normalized($text)]='added-native/'.$row['id'];
                }
                $before=$target['before_data'];
                foreach ($before['inputs']??[] as $i=>$row) foreach (array_unique(array_merge([$row['answer']],$row['accepted']??[])) as $answer) {
                    if(!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$answer))$reserved[Quality::normalized($answer)]='retained-native/'.$target['topic'].'/'.$i;
                }
                foreach ($before['cases']??[] as $i=>$case) foreach ($case['controls']??[] as $control) {
                    if(($control['kind']??null)!=='manual')continue;
                    foreach(array_unique(array_merge([$control['answer']],$control['accepted']??[]))as$answer) {
                        if(!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$answer))$reserved[Quality::normalized($answer)]='retained-authored/'.$target['topic'].'/'.$control['id'];
                    }
                }
            }
            $widgets=json_decode(file_get_contents($root.'/'.\App\Support\ArticlesQuantifiersWidgetHints::SOURCE),true,flags:JSON_THROW_ON_ERROR);
            self::assertCount(10,$widgets['questions']);
            foreach ($widgets['questions'] as $row) { $reserved[Quality::normalized(implode(' ',$row['correct_tokens']))]='widget/'.$row['id']; }
        } finally { \Illuminate\Container\Container::setInstance($previous); }
        self::assertGreaterThan(10,count($reserved));
        $exact=[]; $near=[];
        foreach(array_keys(Quality::TOPICS)as$topic)foreach(Quality::source($topic)['questions']as$row) {
            $id=$topic.'/'.$row['id'];$texts=array_merge([$row['target']],$row['accepted_targets']??[]);
            if($row['kind']==='gap')foreach($row['answer_synonyms']??[]as$alias)$texts[]=str_replace('{a1}',$alias,$row['question']);
            foreach(array_unique($texts)as$text)if(isset($reserved[Quality::normalized($text)]))$exact[]=$id.' repeats '.$reserved[Quality::normalized($text)];
            $words=array_unique(explode(' ',Quality::normalized($row['target'])));
            foreach($reserved as$key=>$owner) {
                $other=array_unique(explode(' ',$key));$dice=2*count(array_intersect($words,$other))/(count($words)+count($other));
                if($dice>=0.84)$near[]=$id.' resembles '.$owner;
            }
        }
        self::assertSame([],$exact,'Native tasks, widgets and mixed banks require independent questions');
        self::assertSame([],$near,'A cosmetic adjective must not create another copy of the same question');
    }

    public function test_generated_banks_and_loaders_match_the_authors_without_mutating_historical_sources(): void
    {
        foreach (array_keys(Quality::TOPICS) as $topic) foreach (['standard','builder'] as $kind) {
            $map = Quality::mapping($topic)[$kind]; $old = Quality::originalDefinition($topic, $kind);
            self::assertCount(72, $old['questions']);
            $actual = json_decode(file_get_contents(dirname(__DIR__, 2).'/'.$map['definition']), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(Quality::compile($topic, $kind), $actual, $topic.'/'.$kind);
            self::assertCount($kind === 'standard' ? 12 : 6, $actual['questions']);
            self::assertFileExists(dirname(__DIR__, 2).'/'.$map['loader']); self::assertFileExists(dirname(__DIR__, 2).'/'.$map['implementation']);
        }
        self::assertCount(4, Quality::metadataPatches());
    }

    public function test_exact_content_revision_and_locale_restore_hints_without_touching_progress(): void
    {
        self::assertCount(36, Quality::questions(), 'Every finite topic must have fresh compiled definitions');
        foreach (Quality::questions() as $source) {
            $payload = ['uuid'=>$source['uuid'],'question'=>$source['question'],'type'=>(string)$source['type'],
                'answer_map'=>array_map(static fn ($m) => $m['answer'], $source['markers']),
                'chosen'=>['kept'],'manualInputsBySlot'=>['draft'],'attemptsBySlot'=>[2],'done'=>false];
            $fresh = Quality::decorate($payload);
            self::assertSame($source['verb_hint'], $fresh['verb_hint']);
            self::assertSame(array_fill_keys(array_keys($source['markers']), $source['verb_hint']), $fresh['verb_hints']);
            self::assertSame($fresh['psp_content_revision'], $fresh['aqv_option_caption_revision']);
            foreach (['chosen','manualInputsBySlot','attemptsBySlot','done'] as $field) { self::assertSame($payload[$field], $fresh[$field]); }
            if ((string)$source['type'] === '0') { self::assertTrue($fresh['passive_gap_phrase_input']); self::assertTrue($fresh['passive_phrase_word_inputs']); }
            self::assertFalse(Quality::cachedNeedsRefresh([$fresh]));
            $stale = $fresh; $stale['verb_hint'] = 'stale'; self::assertTrue(Quality::cachedNeedsRefresh([$stale]));
            self::assertSame($source['verb_hint'], Quality::restoreClarifiedHints($stale, 'uk')['verb_hint']);
            self::assertSame($stale, Quality::restoreClarifiedHints($stale, 'en'));
            $foreign = $payload; $foreign['question'] .= ' changed'; self::assertSame($foreign, Quality::decorate($foreign));
        }
    }

    public function test_stale_order_datasets_refresh_without_changing_current_drafts_answers_or_counters(): void
    {
        $payloads=[];
        foreach (Quality::questions() as $row) {
            $payloads[]=Quality::decorate(['uuid'=>$row['uuid'],'question'=>$row['question'],'type'=>(string)$row['type'],
                'level'=>$row['level'],'answer_map'=>array_map(static fn($m)=>$m['answer'],$row['markers'])]);
        }
        $mixed=\App\Support\SentenceReorderQuestionFactory::addToMixedTheoryTest($payloads,[
            'theory_page_mixed_type_profile'=>Quality::REVISION,'theory_page_mixed_interleave_question_types'=>true,
            '__meta'=>['theory_page_mixed_polyglot_test'=>true]]);
        self::assertFalse(Quality::cachedNeedsRefresh($mixed)); $orders=0;
        foreach ($mixed as $fresh) {
            if (($fresh['presentation']??'')!=='sentence_reorder') { continue; }
            ++$orders; $fresh+=['chosen'=>['kept'],'manualInputsBySlot'=>['typed draft'],'attemptsBySlot'=>[2],'done'=>false];
            foreach (['manual','groups','tokens'] as $case) {
                $stale=$fresh;
                if ($case==='manual') { $stale['reorder_manual_input']=false; }
                elseif ($case==='groups') { $stale['reorder_groups']=array_reverse($stale['reorder_groups']); }
                else { $stale['reorder_tokens'][0]='a blind chunk'; }
                $before=serialize($stale);
                self::assertTrue(Quality::cachedNeedsRefresh([$stale]),$fresh['uuid'].'/'.$case);
                self::assertSame($before,serialize($stale),'Refresh detection must not modify saved learner state');
            }
        }
        self::assertSame(12,$orders);
    }

    public function test_saved_metadata_and_foreign_locale_projection_preserve_old_questions_and_course_boundaries(): void
    {
        foreach (Quality::metadataPatches() as $uuid => $patch) {
            $old = Quality::originalDefinition($patch['topic'], $patch['kind']);
            $projected = Quality::definitionMetadata($old, 'uk');
            self::assertSame($old['questions'], $projected['questions']); self::assertSame($old, Quality::definitionMetadata($old, 'en'));
            self::assertSame($patch['after'], $projected['saved_test']);
            self::assertSame($patch['before']['filters'], Quality::filtersForLocale($uuid, $patch['after']['filters'], 'en'));
            self::assertNotNull(Quality::stateNamespace($patch['after']['filters'], 'uk', $uuid));
            self::assertSame($patch['kind'] === 'builder' ? 6 : null, Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'], $patch['after']['filters'], 'uk'));
            self::assertNull(Quality::savedBuilderDisplayCount($uuid, $patch['before']['slug'].'-foreign', $patch['after']['filters'], 'uk'));
            $changed = $old; $changed['questions'][0]['question'] .= ' changed'; self::assertSame($changed, Quality::definitionMetadata($changed, 'uk'));
        }
    }

    public function test_both_fresh_and_persisted_controller_paths_restore_the_current_locale_hints(): void
    {
        foreach([\App\Http\Controllers\GrammarTestController::class,\App\Http\Controllers\TestJsV2Controller::class]as$class) {
            foreach(['buildQuestionDataset','localizePersistedQuestionVerbHints']as$name) {
                $method=new \ReflectionMethod($class,$name);$lines=file($method->getFileName());
                $source=implode('',array_slice($lines,$method->getStartLine()-1,$method->getEndLine()-$method->getStartLine()+1));
                self::assertSame(1,substr_count($source,'ArticlesQuantifiersPracticeQuality::restoreClarifiedHints('),$class.'/'.$name);
                self::assertStringContainsString('app()->getLocale()',$source);
            }
        }
    }
}
