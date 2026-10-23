<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Support\M26DetailPackage as Digest;
use App\Support\M83BuilderSelectionPresentation;
use App\Support\M83B2RevisionBasicGrammar as Package;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use App\Support\TheoryPresentation;
use App\Support\TheorySentenceBuilderAcceptance;
use App\Support\SiteMode;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

/** Actual canonical SSR with unsaved models. No working DB/HTTP; synthetic widget probes are not live acceptance. */
class M83B2RevisionBasicGrammarRuntimeTest extends TestCase
{
    private function release(): array
    {
        $release=Package::load(dirname(__DIR__,2));
        Package::validate($release[0],$release[1],$release[2],$release[3]);
        foreach(Package::profile()['artifacts'] as $path=>$sha){
            self::assertSame($sha,hash_file('sha256',base_path($path)),'Runtime and public test sources must be the same frozen package.');
        }
        return $release;
    }

    private function block(array $target,array $source,int $slot):TextBlock
    {
        $block=new TextBlock;
        $block->setRawAttributes(['id'=>8300000+$target['page_binding']['id']*100+$slot,
            'uuid'=>Digest::uuid($target['identity'],$source,$slot+1),'seeder'=>$target['identity'],'locale'=>'uk',
            'page_id'=>$target['page_binding']['id'],'page_category_id'=>$target['page_binding']['page_category_id'],
            'sort_order'=>$slot+1,'type'=>$source['type'],'column'=>$source['column'],'heading'=>$source['heading']??null,
            'css_class'=>$source['css_class']??null,'level'=>$source['level']??null,'body'=>$source['body']],true);
        $block->setRelation('tags',new Collection);$block->setRelation('page',null);return $block;
    }

    private function subtitle(array $target,array $old):TextBlock
    {
        return $this->block($target,['type'=>'subtitle','column'=>'header','heading'=>null,'css_class'=>null,
            'level'=>$old['page']['subtitle_level']??null,'uuid_key'=>$old['page']['subtitle_uuid_key']??'subtitle',
            'body'=>$target['after']['page']['subtitle_html']],-1);
    }

    private function noQueries(callable $callback):void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing',app()->environment());self::assertSame('sqlite',DB::connection()->getDriverName());
        self::assertSame(':memory:',DB::connection()->getDatabaseName());self::assertSame('array',config('cache.default'));
        self::assertSame('array',config('session.driver'));self::assertNotSame(realpath(base_path()),realpath(app()->environmentPath()));
        DB::connection()->enableQueryLog();DB::connection()->flushQueryLog();
        try{$callback();self::assertSame([],DB::connection()->getQueryLog(),'Unsaved presentation/rendering must not select or write SQL.');}
        finally{DB::connection()->disableQueryLog();app()->setLocale('uk');}
    }

    private static function text(string $value):string
    {
        // Separate block elements as the browser does; strip_tags alone joins adjacent paragraphs.
        $value=preg_replace('~</(?:p|div|li|h[1-6]|section|article)>|<br\s*/?>~iu',' ',$value);
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($value),ENT_QUOTES|ENT_HTML5,'UTF-8')));
    }

    private function dom(string $html):DOMXPath
    {
        $dom=new DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
        try{self::assertTrue($dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>',LIBXML_NONET));}
        finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
        return new DOMXPath($dom);
    }

    private function rendered(TextBlock $block,?Collection $questions=null):array
    {
        $resolved=TheoryContentSource::resolve($block,['m42StyleContext'=>true]);
        self::assertNotNull($resolved['presentation']);self::assertNotNull($resolved['view']);
        $html=TheoryContentRenderer::render($resolved['view'],['theoryCanonical'=>true,'block'=>$block,
            'data'=>$resolved['data'],'m42Design'=>$resolved['design'],'pointSections'=>$resolved['presentation']['points'],
            'practiceQuestions'=>$questions??new Collection,'lessonLinks'=>[]]);
        return [$html,$this->dom($html)];
    }

    public function test_all_seventy_two_exact_unsaved_models_use_canonical_caller_owned_presentations_without_mutation():void
    {
        app()->setLocale('uk');[, $patch,$master]=$this->release();
        $this->noQueries(function()use($patch,$master):void{$seen=0;
            foreach($patch['targets'] as $i=>$target){foreach($target['after']['page']['blocks'] as $slot=>$source){
                ++$seen;$block=$this->block($target,$source,$slot);$attrs=$block->getAttributes();
                $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$snapshot=$data;
                $p=Package::presentation($block,$data);self::assertNotNull($p);$design=$p['m42_native_design'];
                self::assertTrue(Package::validDesign($design));self::assertSame('m83-native-v1',$design['package']);
                self::assertSame($source['type'],$design['component']);self::assertSame([],$p['points']);
                self::assertSame($master['lessons'][$i]['blocks'][$slot]['data'],array_diff_key($p['data'],['m83_native_v1'=>true]));
                $resolved=TheoryContentSource::resolve($block,['m42StyleContext'=>true]);
                if(TheoryPresentation::nativeView($source['type'])===null){
                    self::assertContains($source['type'],['hero','navigation-chips']);self::assertNull($resolved['view']);self::assertNull($resolved['design']);
                    self::assertSame($data,$resolved['data']);
                }else{
                    self::assertSame(TheoryPresentation::nativeView($source['type']),$resolved['view']);self::assertSame($design,$resolved['design']);self::assertSame($p['data'],$resolved['data']);
                }
                foreach([[],['m42StyleContext'=>false]] as $context){$foreign=TheoryContentSource::resolve($block,$context);
                    self::assertNull($foreign['design']);self::assertSame($data,$foreign['stored']);self::assertSame($data,$foreign['data']);}
                self::assertSame($source['level']??null,$block->level);self::assertSame($snapshot,$data);
                self::assertSame($attrs,$block->getAttributes());self::assertSame([],$block->getDirty());self::assertFalse($block->exists);
            }}self::assertSame(72,$seen);
        });
    }

    public function test_foreign_missing_and_stale_metadata_retains_full_stored_fallback():void
    {
        app()->setLocale('uk');[, $patch]=$this->release();
        $this->noQueries(function()use($patch):void{foreach($patch['targets'] as $i=>$target){
            $slot=Package::PRACTICE_SOURCE_INDICES[$i];$source=$target['after']['page']['blocks'][$slot];
            $block=$this->block($target,$source,$slot);$data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);
            $exact=Package::presentation($block,$data);self::assertNotNull($exact);
            $changes=['seeder'=>'Foreign\\Owner','locale'=>'en','page_id'=>999,'page_category_id'=>999,'uuid'=>'foreign',
                'sort_order'=>99,'type'=>'box','column'=>'right','heading'=>'Later heading','css_class'=>'custom-style',
                'level'=>$block->level===null?'A2':null,'body'=>$source['body']."\n"];
            foreach($changes as $field=>$value){$foreign=clone $block;$foreign->$field=$value;$attrs=$foreign->getAttributes();$dirty=$foreign->getDirty();
                self::assertNull(Package::presentation($foreign,$data),$field);$fallback=TheoryContentSource::resolve($foreign,['m42StyleContext'=>true]);
                self::assertNull($fallback['design'],$field);self::assertSame($data,$fallback['stored']);self::assertSame($data,$fallback['data']);
                self::assertSame($attrs,$foreign->getAttributes());self::assertSame($dirty,$foreign->getDirty());}
            $missing=clone $block;$missing->offsetUnset('seeder');self::assertNull(Package::presentation($missing,$data));
            $range=clone $block;$range->level=Package::DISPLAY_LEVELS[$i]===$block->level?'B2-C1':Package::DISPLAY_LEVELS[$i];self::assertNull(Package::presentation($range,$data),'Display range must not pass an exact technical-level guard.');
            $unmarked=$data;unset($unmarked['m83_native_v1']);self::assertNull(Package::presentation($block,$unmarked));
            $extra=$data;$extra['secondary_body']='Unreviewed competing content';self::assertNull(Package::presentation($block,$extra));
            foreach(['en','pl'] as $locale){app()->setLocale($locale);self::assertNull(Package::presentation($block,$data));}app()->setLocale('uk');
            foreach(['package'=>'foreign','identity'=>'Foreign','locale'=>'en','uuid'=>'foreign','source_index'=>99,
                'body_sha256'=>str_repeat('0',64),'component'=>'m83-section'] as $field=>$value){$bad=$exact['m42_native_design'];$bad[$field]=$value;
                self::assertFalse(Package::validDesign($bad),$field);self::assertNull(Package::pointPanels($bad));}
        }self::assertFalse(Package::validDesign(null));});
    }

    public function test_production_profile_has_the_same_canonical_learning_content_without_external_http():void
    {
        app()->setLocale('uk');[, $patch]=$this->release();
        $originalRequest=app('request');$originalDomains=config('site-mode.production_domains');
        config(['site-mode.production_domains'=>['m83-production.test']]);$visible=[];
        try {
            foreach(['m83-development.test'=>SiteMode::DEVELOPMENT,'m83-production.test'=>SiteMode::PRODUCTION] as $host=>$mode){
                // Request objects only: no HTTP client, kernel route, server or external connection.
                app()->instance('request',Request::create('http://'.$host.'/theory/m83-isolated-profile'));
                self::assertSame($mode,app(SiteMode::class)->current());
                $this->noQueries(function()use($patch,$mode,&$visible):void{
                    $count=0;
                    foreach($patch['targets'] as $i=>$target){foreach($target['after']['page']['blocks'] as $slot=>$source){
                        if(in_array($source['type'],['hero','navigation-chips'],true)){continue;}
                        [$html,$xp]=$this->rendered($this->block($target,$source,$slot));++$count;
                        self::assertSame(0,$xp->query('//*[@data-theory-render-fallback]')->length);
                        $text='';foreach($xp->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]') as $node){$text.=' '.$node->textContent;}
                        $visible[$mode][$i.':'.$slot]=self::text($text);
                    }}self::assertSame(60,$count);
                });
            }
            self::assertSame($visible[SiteMode::DEVELOPMENT],$visible[SiteMode::PRODUCTION]);
        } finally {
            app()->instance('request',$originalRequest);config(['site-mode.production_domains'=>$originalDomains]);
        }
    }

    public function test_fifty_four_actual_native_content_sections_keep_bilingual_basic_and_zero_artificial_disclosures():void
    {
        app()->setLocale('uk');[, $patch,$master]=$this->release();
        $this->noQueries(function()use($patch,$master):void{$count=0;
            foreach($patch['targets'] as $i=>$target){foreach($target['after']['page']['blocks'] as $slot=>$source){
                if(in_array($source['type'],['hero','navigation-chips','practice-set'],true)){continue;}++$count;
                $block=$this->block($target,$source,$slot);[$html,$xp]=$this->rendered($block);$data=$master['lessons'][$i]['blocks'][$slot]['data'];
                self::assertSame(1,substr_count($html,'<!-- theory.content:node -->'));
                self::assertSame(1,$xp->query('//*[@data-theory-component="section" and @id="block-'.$block->id.'"]')->length);
                self::assertSame(0,$xp->query('//details')->length);self::assertSame(0,$xp->query('//*[@data-theory-render-fallback]')->length);
                self::assertStringNotContainsString('m83-section',$html);self::assertStringNotContainsString('m83-styles',$html);
                $visible=self::text($html);self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong)\b|<\/?(?:p|span|strong)\b/u',$visible);
                if($source['type']==='usage-panels'){
                    self::assertSame(count($data['sections']),$xp->query('//*[@data-theory-point-panel]')->length);
                    $ens=array_map(static fn($n)=>self::text($n->textContent),iterator_to_array($xp->query('//*[@data-theory-component="example"]//*[@lang="en"]')));
                    $uks=array_map(static fn($n)=>self::text($n->textContent),iterator_to_array($xp->query('//*[@data-theory-component="example"]//*[@lang="uk"]')));
                    foreach($data['sections'] as $section){foreach($section['examples']??[] as $example){
                        self::assertContains(self::text($example['en']),$ens);self::assertContains(self::text($example['ua']),$uks);}}
                }elseif($source['type']==='comparison-table'){
                    self::assertSame(count($data['rows']),$xp->query('//*[@data-theory-component="table"]//tbody/tr')->length);
                    foreach($data['rows'] as $row){foreach(['en','ua'] as $field){if(isset($row[$field])){self::assertStringContainsString(self::text($row[$field]),$visible);}}}
                }elseif($source['type']==='mistakes-grid'){
                    $items=$xp->query('//*[@data-theory-component="mistake"]');self::assertCount(count($data['items']),$items);
                    foreach($data['items'] as $n=>$item){foreach(['wrong_en','wrong_ua','right_en','right_ua'] as $field){
                        self::assertNotEmpty($item[$field]);self::assertStringContainsString(self::text($item[$field]),self::text($items->item($n)->textContent));}
                        if(isset($item['note'])){self::assertStringContainsString(self::text($item['note']),self::text($items->item($n)->textContent));}}
                }elseif($source['type']==='forms-grid'){
                    self::assertSame(count($data['items']),$xp->query('//*[@data-theory-component="form"]')->length);
                    foreach($data['items'] as $item){self::assertStringContainsString(self::text($item['title']),$visible);
                        if(isset($item['url'])){self::assertSame(1,$xp->query('//a[@data-theory-component="form" and @href="'.$item['url'].'"]')->length);}}
                }
            }}self::assertSame(54,$count);
        });
    }

    /** Invented model IDs are deliberate: exact real matcher/snapshot/condition-clone tests live in the bank test. */
    private function syntheticQuestions(array $samples,int $lesson,array $levels):Collection
    {
        return new Collection(array_map(function(array $sample,int $i)use($lesson,$levels):Question{
            $q=new Question;$q->setRawAttributes(['id'=>83900000+$lesson*10+$i,'uuid'=>'m83-offline-widget-'.$lesson.'-'.$i,
                'seeder'=>'Database\\Seeders\\V3\\Polyglot\\M83IsolatedTeachingProbe','type'=>'4','level'=>$levels[$i],
                'question'=>$sample['visible_condition_uk']],true);
            $answers=[];foreach($sample['raw_correct_tokens'] as $n=>$token){$option=new QuestionOption;
                $option->setRawAttributes(['id'=>83910000+$lesson*1000+$i*50+$n,'option'=>$token],true);
                $answer=new QuestionAnswer;$answer->setRawAttributes(['id'=>83920000+$lesson*1000+$i*50+$n,
                    'question_id'=>$q->id,'marker'=>'a'.($n+1),'option_id'=>$option->id],true);$answer->setRelation('option',$option);$answers[]=$answer;}
            $options=[];foreach($sample['token_bank_with_multiplicity'] as $n=>$token){$option=new QuestionOption;
                $option->setRawAttributes(['id'=>83930000+$lesson*1000+$i*100+$n,'option'=>$token],true);$options[]=$option;}
            $q->setRelation('answers',new Collection($answers));$q->setRelation('options',new Collection($options));
            foreach(['tags','hints','verbHints'] as $relation){$q->setRelation($relation,new Collection);}return $q;
        },$samples,array_keys($samples)));
    }

    public function test_six_practice_ssrs_show_all_own_conditions_and_one_five_question_native_widget_on_unsaved_probes():void
    {
        app()->setLocale('uk');[, $patch,$master]=$this->release();$audit=M83BuilderSelectionPresentation::loadAudit(dirname(__DIR__,2));
        $this->noQueries(function()use($patch,$master,$audit):void{$own=0;$widgets=0;
            foreach($patch['targets'] as $i=>$target){$slot=Package::PRACTICE_SOURCE_INDICES[$i];$source=$target['after']['page']['blocks'][$slot];
                $block=$this->block($target,$source,$slot);$data=$master['lessons'][$i]['blocks'][$slot]['data'];
                $questions=$this->syntheticQuestions($audit['targets'][$i]['first_five'],$i,$audit['targets'][$i]['selected_levels']);$unchanged=$questions->toArray();
                [$html,$xp]=$this->rendered($block,$questions);
                self::assertSame($i===0?3:4,$xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," theory-exercise ")]')->length);
                self::assertSame([2,5,3,3,3,3][$i],$xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]')->length);
                self::assertSame([0,2,2,2,2,1][$i],$xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"rephrase-")]')->length);
                $visible='';foreach($xp->query('//body//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::template)]') as $node){$visible.=' '.$node->textContent;}
                $visible=self::text($visible);if(isset($data['input_intro'])){self::assertStringContainsString(self::text($data['input_intro']),$visible);}
                foreach($data['inputs'] as $task){self::assertStringContainsString(self::text($task['after']),$visible);}
                foreach($data['rephrase']??[] as $n=>$task){if($n===0){self::assertStringContainsString(self::text($task['example_original']),$visible);self::assertStringContainsString(self::text($task['example_target']),$visible);}else{self::assertStringContainsString(self::text($task['original']),$visible);}}
                $labels=array_map(static fn($n)=>self::text($n->ownerDocument->saveHTML($n)),iterator_to_array($xp->query('//label')));
                foreach(['selects','choices'] as $group){foreach($data[$group] as $task){self::assertContains(self::text($task['label']),$labels);}}
                foreach($xp->query('//*[@data-word-suggestion-input and starts-with(@data-word-suggestion-input,"inputs-")]') as $input){self::assertSame('off',$input->getAttribute('autocomplete'));}
                self::assertSame(1,$xp->query('//*[@data-sentence-builder]')->length);++$widgets;
                self::assertSame(1,$xp->query('//*[@id="block-'.$block->id.'"]//*[@data-sentence-builder]')->length);
                self::assertStringContainsString('Вправа '.($i===0?4:5).'. Побудуй речення',$visible);
                $payload=TheorySentenceBuilderAcceptance::payload($html,$block->uuid);self::assertIsArray($payload);self::assertCount(5,$payload);
                self::assertSame($questions->pluck('id')->all(),array_column($payload,'id'));
                foreach($payload as $n=>$q){self::assertSame('4',$q['type']);self::assertSame($audit['targets'][$i]['first_five'][$n]['correct_tokens'],$q['correct_tokens']);
                    self::assertNotSame([],$q['options']);self::assertSame($questions[$n]->question,$q['question']);}
                self::assertSame($unchanged,$questions->toArray());self::assertFalse($block->exists);
                $own+=count($data['selects'])+count($data['choices'])+count($data['inputs'])+count($data['rephrase']??[])-($i===0?0:1);
                self::assertDoesNotMatchRegularExpression('/\{a\d+\}|&lt;\/?(?:p|span|strong)\b/u',$visible);
                self::assertSame(0,$xp->query('//*[@data-theory-render-fallback]')->length);
            }self::assertSame(60,$own);self::assertSame(6,$widgets);
        });
    }

    public function test_excluded_standalone_and_course_clones_restore_all_old_sources_and_hide_only_one_new_practice():void
    {
        app()->setLocale('uk');[$before,$patch]=$this->release();
        $this->noQueries(function()use($before,$patch):void{$hidden=0;
            foreach($patch['targets'] as $i=>$target){$old=$before['targets'][$i]['before'];$blocks=[$this->subtitle($target,$old)];
                foreach($target['after']['page']['blocks'] as $slot=>$source){$block=$this->block($target,$source,$slot);$blocks[]=$block;$attrs=$block->getAttributes();
                    foreach(['theory','course','unknown'] as $caller){self::assertSame($block,Package::preserveExcludedTextBlock($block,$caller));}
                    $restored=Package::preserveExcludedTextBlock($block,'standalone_test');
                    if($slot>=count($old['page']['blocks'])){self::assertNull($restored);++$hidden;}
                    else{self::assertNotSame($block,$restored);self::assertSame($old['page']['blocks'][$slot]['body'],$restored->body);
                        foreach(['type','column','heading','level','css_class'] as $field){self::assertSame($old['page']['blocks'][$slot][$field]??null,$restored->$field);}}
                    self::assertSame($attrs,$block->getAttributes());self::assertSame([],$block->getDirty());
                }
                $foreign=clone $blocks[1];$foreign->setRawAttributes(array_replace($foreign->getAttributes(),['id'=>8399000+$i,'locale'=>'en','uuid'=>'m83-en-'.$i,'sort_order'=>99,'body'=>'Unchanged English probe.']),true);$blocks[]=$foreign;
                $page=new Page;$page->setRawAttributes(['id'=>$target['page_binding']['id'],'page_category_id'=>$target['page_binding']['page_category_id'],
                    'seeder'=>$target['identity'],'slug'=>$target['slug'],'title'=>$old['page']['title'],'type'=>'theory','text'=>$target['after']['page']['subtitle_text']],true);
                $page->setRelation('textBlocks',new Collection($blocks));$attrs=$page->getAttributes();$original=$page->textBlocks->toArray();
                $restored=Package::preserveCoursePage($page);self::assertNotSame($page,$restored);self::assertSame($old['page']['subtitle_text'],$restored->text);
                $uk=$restored->textBlocks->where('locale','uk')->values();self::assertCount(count($old['page']['blocks'])+1,$uk);
                foreach($old['page']['blocks'] as $slot=>$source){$uuid=Digest::uuid($target['identity'],$source,$slot+1);self::assertSame($source['body'],$uk->firstWhere('uuid',$uuid)->body);}
                self::assertSame($foreign,$restored->textBlocks->firstWhere('locale','en'));self::assertSame($attrs,$page->getAttributes());self::assertSame($original,$page->textBlocks->toArray());
                $partial=clone $page;$partial->setRelation('textBlocks',$partial->textBlocks->slice(1)->values());self::assertSame($partial,Package::preserveCoursePage($partial));
                $stale=clone $page;$copies=$page->textBlocks->map(static fn($b)=>clone $b);$copies[1]->body.="\n";$stale->setRelation('textBlocks',$copies);self::assertSame($stale,Package::preserveCoursePage($stale));
            }self::assertSame(1,$hidden);self::assertNull(Package::preserveCoursePage(null));
        });
    }

    public function test_first_strong_h1_extraction_keeps_all_six_rendered_titles_and_raw_page_titles():void
    {
        [$before,$patch,$master]=$this->release();$controller=(new ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $extract=new ReflectionMethod(PageController::class,'extractLocalizedTitleFromSubtitle');
        foreach($patch['targets'] as $i=>$target){self::assertSame($before['targets'][$i]['before']['page']['title'],$target['after']['page']['title']);
            foreach([$before['targets'][$i]['before']['page']['subtitle_html'],$target['after']['page']['subtitle_html']] as $subtitle){self::assertSame(Package::RENDERED_TITLES[$i],$extract->invoke($controller,$subtitle));}
            self::assertSame(Package::RENDERED_TITLES[$i],$master['lessons'][$i]['preserved_h1']);}
        self::assertStringContainsString('<strong>B2 Mixed Revision</strong>',$master['lessons'][0]['blocks'][0]['data']['intro']);
    }
}
