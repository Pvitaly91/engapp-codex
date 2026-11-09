<?php

namespace App\Support;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Finite UK question/negation banks; historical questions and learner progress remain intact. */
final class QuestionsNegationsPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'qnq-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'question-forms' => 'QuestionForms', 'yes-no' => 'YesNoQuestionsGeneralQuestions',
        'wh' => 'WhQuestionsSpecialQuestions', 'subject' => 'SubjectQuestions',
        'answers' => 'AnswersToQuestions', 'alternative' => 'AlternativeQuestions',
        'tags' => 'QuestionTagsDisjunctiveQuestions', 'negative-questions' => 'NegativeQuestions',
        'embedded' => 'EmbeddedQuestions', 'indirect' => 'IndirectQuestions',
        'negative-forms' => 'NegativeForms', 'double-negatives' => 'DoubleNegatives',
    ];
    protected const UUID_PREFIX = 'qnq-';
    protected const SOURCE_PREFIX = 'questions_negations_quality_';
    protected const NAMESPACE_PREFIX = 'questions-negations-quality-';
    protected const LABEL = 'Questions and Negations Quality';
    protected const META_PREFIX = 'questions_negations_quality';
    public const CATEGORY_ID = 21;
    public const CATEGORY_SLUG = 'pytalni-rechennia-ta-zaperechennia';
    public const CATEGORY_TOPICS = ['question-forms','yes-no','wh','subject','answers','alternative','tags','negative-questions','embedded','indirect','negative-forms','double-negatives'];
    public const TYPES_CATEGORY_ID = 22;
    public const TYPES_CATEGORY_SLUG = 'types-of-questions';
    public const TYPES_TOPICS = ['yes-no','wh','subject','answers','alternative','tags','negative-questions','embedded','indirect'];
    public const SAVED_TEST_COUNT = 24;

    // Exact authored lexical/gender clues, not a runtime vocabulary heuristic.
    // Raw accepted banks, answer aliases, DB rows and snapshots stay unchanged.
    public const HINT_CLARIFICATIONS_SOURCE = 'docs/content/questions-negations-hint-clarifications.v1.json';
    public const HINT_CLARIFICATIONS_SHA = '0e1d97cea62d632d5f17a2fe47a841e5e69494166165e492bec2cd17bfeee767';
    public const HINT_CLARIFICATIONS_COUNT = 100;
    public const ANSWER_VARIANTS_SOURCE = 'docs/content/questions-negations-answer-variants.v1.json';
    public const ANSWER_VARIANTS_SHA = '8497f33925e74a79d76469aefc23fbcff4f880cc4e7ad17e3cfe1cdb51c49f7e';

    private const ORIGINALS = [
        'question-forms' => [
            'standard' => ['qn-qf-v3-all','551e966bc802268bd5adf857483dea8a9e63ed3f5ae548cc79b725dd695512ee'],
            'builder' => ['qn-qf-poly-all','76f8885df687d9f8e1b903d13c483b9cf982bac0d57e43642ab31e9f91e1691c']],
        'yes-no' => [
            'standard' => ['qn-yn-v3-all','039d411c3382375841b40c15976233c9e63f6882593f790dec26c638adbb8d12'],
            'builder' => ['qn-yn-poly-all','99540fa3069e8f5bbecb5efea768fa2e4058a5ddc943e4ca1b6dfed61104c1b5']],
        'wh' => [
            'standard' => ['qn-wh-v3-all','55c532448756b521b1ce262dc9d443f9f634d66b75be26570ed22a26baf67a83'],
            'builder' => ['qn-wh-poly-all','d1950ea5c5deee8373595c1e287dc83e4450a8cbb1eb3602bc4d02320d133f6d']],
        'subject' => [
            'standard' => ['qn-sq-v3-all','963d44f89a724d419c124d7677fcdcc880e7e785a6bee085ed1fb69b7f760026'],
            'builder' => ['qn-sq-poly-all','2033d70ec76431d9df736d439f447fe8cec31f7734e08600036f59451bd3681e']],
        'answers' => [
            'standard' => ['qn2-atq-v3-all','944e897fd5287e19bb6280cb05a69448adacb637e8a407edcc8331955a3b463b'],
            'builder' => ['qn2-atq-poly-all','1ff413d9fd842a9637f42880e2e21d63b42a667c298e2b54df9db1f97523574f']],
        'alternative' => [
            'standard' => ['qn2-alt-v3-all','c9ec5280c3315e28e317282ce4b361f8bdd3f55e6cc960b5bc660beb125aef51'],
            'builder' => ['qn2-alt-poly-all','ea586c119bdfd5107049bc3d21461c05fc73034d24feb6542ba1fab1ec81a978']],
        'tags' => [
            'standard' => ['qn2-qtg-v3-all','49ffe7e4257549bdfa80b01e1d7a0aa2e07ced322a316c52cb796ed4302697a0'],
            'builder' => ['qn2-qtg-poly-all','3c326d4d21adeb9886d6fd782478067052c0b43eb0161e7576e4b91142d5ab96']],
        'negative-questions' => [
            'standard' => ['qn2-neg-v3-all','5546cec6c8d6203d32106dcabe3ebe5b500b348e5e4372ada6193c9560111844'],
            'builder' => ['qn2-neg-poly-all','447bc9ad5dd3c81a1536b3c1bec83a6a7df4c0466e53a2f4ea6683cadefea8f3']],
        'embedded' => [
            'standard' => ['qn3-emb-v3-all','ea192800d39c61f407b2a94a9a525ffdad2b39a6f7d2a2f7bfd045ae8726f93c'],
            'builder' => ['qn3-emb-poly-all','dcdeaa564232bff3e7fc094f94bbd788391a6c33e011548da5c78c39f53f0cbe']],
        'indirect' => [
            'standard' => ['qn3-ind-v3-all','0101a2525694d3438ddfc10168a3ae4fde894c9cadd165f0ac28ebb97aaabe21'],
            'builder' => ['qn3-ind-poly-all','fa18bd83293b5ac790c628590a8abb1b9cd7b7c457a85aab861377d82e723a36']],
        'negative-forms' => [
            'standard' => ['qn3-negf-v3-all','8a82d998a119240db4d5cc197f904cdb5b106b788039705690f3631d78241bfd'],
            'builder' => ['qn3-negf-poly-all','f1003b28cd086383af01e9ac460c833fd6cb040f2ef541c690858384cc6218e7']],
        'double-negatives' => [
            'standard' => ['qn3-dbln-v3-all','ee60a6d92c58e7d963d42c34031d97b8e7eec6fc040478801b88e88d8e4b2fae'],
            'builder' => ['qn3-dbln-poly-all','9b2550a8337e5d28993c68c8b005b7d10fa7864c8bd8d67259478e9ded0a834a']],
    ];
    private const TAG_BASICS = ['path' => 'database/seeders/V3/Polyglot/PolyglotQuestionTagsBasicsLessonSeeder/definition.json',
        'sha256' => '6e484bf072a1c352f81e98274945fd021b66782ded96f3cf2b13a9173267d094',
        'class' => 'Database\\Seeders\\V3\\Polyglot\\PolyglotQuestionTagsBasicsLessonSeeder',
        'saved_test_uuid' => 'polyglot-qtags-basics-a2-saved-test', 'kind' => 'builder'];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown question/negation topic.'); }
        $stem = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/questions-negations-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $ns = $kind === 'standard' ? 'QuestionsNegations' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$ns.'/'.$name;
            $oldName = $kind === 'standard' ? 'QuestionsNegations/'.$stem.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$ns.'\\'.$name, 'definition' => $directory.'/definition.json',
                'loader' => $directory.'.php', 'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0], 'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        if ($topic === 'tags') {
            $map['additional_originals'] = [self::TAG_BASICS];
            $map['additional_locale_overrides'] = [self::TAG_BASICS['class'] => $map['builder']['class']];
        }
        return $map;
    }

    protected static function composeSlots(array $author): array { return FinitePracticeChoiceOptions::reviewedSlots($author); }

    public static function compile(string $topic, string $kind): array
    {
        static $compiled = [];
        $source = self::source($topic); self::originalDefinition($topic, $kind);
        $key = $topic.'|'.$kind.'|'.hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (isset($compiled[$key])) { return $compiled[$key]; }
        $definition = parent::compileAuthored($topic, $kind);
        return $compiled[$key] = $kind === 'builder' ? FinitePracticeChoiceOptions::compile($definition, $source, $topic, self::composeSlots(...)) : $definition;
    }

    public static function decorate(array $payload): array
    {
        $payload = parent::decorate($payload); $source = self::questions()[$payload['uuid'] ?? ''] ?? null;
        if ($source && ($payload['question'] ?? null) === $source['question']
            && ($payload['psp_content_revision'] ?? null) === hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            && ($payload['answer_map'] ?? null) === array_map(static fn ($m) => $m['answer'], $source['markers'])) {
            if ((string)$source['type'] === '0') { $payload['passive_phrase_word_inputs'] = true; }
            $strict = self::strictSlotSurfaces($source,$payload['psp_content_revision']);
            if ($strict !== null) { $payload['qnq_strict_slot_surfaces'] = $strict; }
            $payload = self::projectPresentedHints($payload,$source);
            $payload = self::projectAnswerVariants($payload,$source);
        }
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads): bool
    {
        if (parent::cachedNeedsRefresh($payloads)) { return true; }
        $sources = self::questions();
        foreach ($payloads as $q) {
            if (isset($sources[$q['uuid'] ?? '']) && (string) $sources[$q['uuid']]['type'] === '0'
                && ($q['passive_phrase_word_inputs'] ?? null) !== true) { return true; }
            $source = $sources[$q['uuid'] ?? ''] ?? null;
            if ($source && ($q['qnq_strict_slot_surfaces'] ?? null) !== self::strictSlotSurfaces($source,hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)))) { return true; }
            $hint = $source ? self::presentedSubjectHint($source) : null;
            if ($hint !== null && (($q['verb_hint'] ?? null) !== $hint || ($q['verb_hints']['a1'] ?? null) !== $hint)) { return true; }
            $variants = $source ? self::answerVariantFields($source) : null;
            foreach ($variants ?? [] as $field => $expected) { if (($q[$field] ?? null) !== $expected) { return true; } }
        }
        return false;
    }

    /** Finite source data can alter only the two public Ukrainian hint fields. */
    public static function hintClarifications(): array
    {
        static $entries = null;
        if ($entries !== null) { return $entries; }
        try {
            $path = dirname(__DIR__,2).'/'.self::HINT_CLARIFICATIONS_SOURCE;
            if (!is_file($path) || is_link($path) || !hash_equals(self::HINT_CLARIFICATIONS_SHA,hash_file('sha256',$path))) { return $entries = []; }
            $data = json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if (($data['schema_version'] ?? null) !== 1 || ($data['revision'] ?? '') !== 'qnq-hint-clarifications-v1'
                || ($data['fields'] ?? null) !== ['verb_hint','verb_hints.a1'] || !is_array($data['entries'] ?? null)
                || count($data['entries']) !== self::HINT_CLARIFICATIONS_COUNT) { return $entries = []; }
            $reviewed = [];
            foreach ($data['entries'] as $entry) {
                if (!is_array($entry) || !isset(self::TOPICS[$entry['topic'] ?? ''])
                    || ($entry['question_uuid'] ?? null) !== self::questionUuid($entry['topic'],$entry['author_id'] ?? '')
                    || isset($reviewed[$entry['question_uuid']]) || preg_match('/^[a-f0-9]{64}$/D',$entry['source_row_sha256'] ?? '') !== 1
                    || !is_string($entry['before_hint_uk'] ?? null) || !is_string($entry['after_hint_uk'] ?? null)
                    || preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$entry['after_hint_uk']) !== 1
                    || preg_match('/[A-Za-z<>\{\}]|&(?:#[0-9]+|[a-z]+);/iu',$entry['after_hint_uk']) === 1
                    || !is_array($entry['lexical_cues_uk'] ?? null) || $entry['lexical_cues_uk'] === []) { return $entries = []; }
                foreach ($entry['lexical_cues_uk'] as $cue) {
                    if (!is_string($cue) || trim($cue) === '' || !str_contains(mb_strtolower($entry['after_hint_uk']),mb_strtolower($cue))) { return $entries = []; }
                }
                $reviewed[$entry['question_uuid']] = $entry;
            }
            return $entries = $reviewed;
        } catch (Throwable) { return $entries = []; }
    }

    private static function presentedSubjectHint(array $source): ?string
    {
        $entry = self::hintClarifications()[$source['uuid'] ?? ''] ?? null;
        if ($entry === null || ($source['verb_hint'] ?? null) !== $entry['before_hint_uk']
            || ($source['markers']['a1']['verb_hint'] ?? null) !== $entry['before_hint_uk']
            || !hash_equals($entry['source_row_sha256'],hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)))) { return null; }
        return $entry['after_hint_uk'];
    }

    private static function projectPresentedHints(array $payload,array $source): array
    {
        $hint = self::presentedSubjectHint($source);
        if ($hint !== null) {
            $payload['verb_hint'] = $hint;
            $payload['verb_hints'] = array_replace(is_array($payload['verb_hints'] ?? null) ? $payload['verb_hints'] : [], ['a1' => $hint]);
        }
        return $payload;
    }

    /** Reapply only reviewed UK hints after cached DB-hint localization. */
    public static function restoreClarifiedHints(array $payload,string $locale): array
    {
        $uuid=$payload['uuid']??null;
        if ($locale !== 'uk' || !is_string($uuid) || !str_starts_with($uuid,self::UUID_PREFIX)
            || !isset(self::hintClarifications()[$uuid])) { return $payload; }
        $source=self::questions()[$uuid]??null;
        if (!$source || (string)($payload['type']??'') !== (string)$source['type']
            || ($payload['question']??null) !== $source['question']
            || ($payload['psp_content_revision']??null) !== hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))
            || ($payload['answer_map']??null) !== array_map(static fn($m)=>$m['answer'],$source['markers'])) { return $payload; }
        return self::projectPresentedHints($payload,$source);
    }

    public static function answerVariants(): array
    {
        static $entries = null;
        if ($entries !== null) { return $entries; }
        try {
            $path=dirname(__DIR__,2).'/'.self::ANSWER_VARIANTS_SOURCE;
            if (!is_file($path) || is_link($path) || !hash_equals(self::ANSWER_VARIANTS_SHA,hash_file('sha256',$path))) { return $entries=[]; }
            $data=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if (($data['schema_version']??null)!==1 || ($data['revision']??'')!=='qnq-answer-variants-v1'
                || ($data['fields']??null)!==['accepted_answers_by_marker','accepted_answers','answer_synonyms_by_marker','reorder_accepted_answers']
                || !is_array($data['entries']??null) || count($data['entries'])!==2) { return $entries=[]; }
            $reviewed=[];
            foreach ($data['entries'] as $entry) {
                if (!in_array($entry['question_uuid']??null,['qnq-negative-forms-c1-g02','qnq-negative-forms-c2-g02'],true)
                    || isset($reviewed[$entry['question_uuid']]) || ($entry['marker']??null)!=='a1'
                    || preg_match('/^[a-f0-9]{64}$/D',$entry['source_row_sha256']??'')!==1
                    || !is_string($entry['primary_answer']??null) || !is_array($entry['added_answers']??null) || count($entry['added_answers'])!==1) { return $entries=[]; }
                foreach ($entry['added_answers'] as $answer) {
                    if (!is_string($answer) || trim($answer)!==$answer || $answer==='' || $answer===$entry['primary_answer']
                        || preg_match('/[А-Яа-яІіЇїЄєҐґ<>\{\}]|&(?:#[0-9]+|[a-z]+);/iu',$answer)===1) { return $entries=[]; }
                }
                $reviewed[$entry['question_uuid']]=$entry;
            }
            return $entries=$reviewed;
        } catch (Throwable) { return $entries=[]; }
    }

    /** Recompute from pinned source, never from possibly stale cached aliases. */
    private static function answerVariantFields(array $source): ?array
    {
        $entry=self::answerVariants()[$source['uuid']??'']??null;
        if ($entry===null || (string)($source['type']??'')!=='0' || array_keys($source['markers']??[])!==['a1']
            || ($source['markers']['a1']['answer']??null)!==$entry['primary_answer']
            || !hash_equals($entry['source_row_sha256'],hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)))) { return null; }
        $answers=[];
        foreach (array_merge([$entry['primary_answer']],$source['answer_synonyms']['a1']??[],$entry['added_answers']) as $answer) {
            $answers=array_merge($answers,AcceptedAnswerVariants::for($answer));
        }
        $byMarker=['a1'=>array_values(array_unique($answers))];
        $display=$source['display_answer_synonyms']??[];
        $display['a1']=array_values(array_unique(array_merge($display['a1']??[],$entry['added_answers'])));
        $full=$source['accepted_target_texts'];
        foreach ($entry['added_answers'] as $answer) foreach (AcceptedAnswerVariants::for($answer) as $variant) {
            $full[]=str_replace('{a1}',$variant,$source['question']);
        }
        return ['accepted_answers_by_marker'=>$byMarker,'accepted_answers'=>array_values($byMarker),
            'answer_synonyms_by_marker'=>$display,'reorder_accepted_answers'=>array_values(array_unique($full))];
    }

    private static function projectAnswerVariants(array $payload,array $source): array
    {
        $fields=self::answerVariantFields($source);
        return $fields===null?$payload:array_replace($payload,$fields);
    }

    /** A contracted interrogative cannot expand before its subject: Does not he is invalid. */
    private static function strictSlotSurfaces(array $source,string $revision): ?array
    {
        $answers = [];
        foreach ($source['markers'] as $marker => $data) {
            $tags = $source['source'] === self::SOURCE_PREFIX.'tags';
            $negative = $source['source'] === self::SOURCE_PREFIX.'negative_questions';
            $shortReply = $source['source'] === self::SOURCE_PREFIX.'answers';
            $strict = $shortReply || ($tags && ((string)$source['type'] === '0' || $marker === array_key_last($source['markers'])))
                || ($negative && ((string)$source['type'] === '0' || preg_match('/^(?:do|does|did|is|are|was|were|has|have|had|will|would|can|could|shall|should|might|need|ought)n\x{27}t$/iu',$data['answer']) === 1
                    || in_array(mb_strtolower($data['answer']),["won't","can't"],true)));
            if (!$strict) { continue; }
            $answers[$marker] = array_values(array_unique(array_merge([$data['answer']],$source['answer_synonyms'][$marker] ?? [])));
        }
        return $answers === [] ? null : ['revision'=>$revision,'answers_by_marker'=>$answers];
    }

    public static function ownsCategory(int $id,string $slug): bool
    {
        return ($id === self::CATEGORY_ID && $slug === self::CATEGORY_SLUG) || ($id === self::TYPES_CATEGORY_ID && $slug === self::TYPES_CATEGORY_SLUG);
    }

    public static function categoryProfile(int $id,string $slug,array $groups): ?string
    {
        if (!self::ownsCategory($id,$slug) || $groups === []) { return null; }
        $topics = $id === self::TYPES_CATEGORY_ID ? self::TYPES_TOPICS : self::CATEGORY_TOPICS; $pairs = [];
        foreach ($topics as $topic) { $m = self::mapping($topic); $p = [strtolower($m['standard']['class']),strtolower($m['builder']['class'])]; sort($p); $pairs[] = $p; }
        foreach ($groups as $group) {
            if (!is_array($group) || ($group['base_filters']['theory_page_mixed_type_profile'] ?? '') !== self::REVISION || !is_array($group['seeder_classes'] ?? null)) { return null; }
            if (array_filter($group['seeder_classes'],static fn($v) => !is_string($v)) !== []) { return null; }
            $p = array_values(array_unique(array_map('strtolower',$group['seeder_classes']))); sort($p);
            if (!in_array($p,$pairs,true)) { return null; }
        }
        return self::REVISION;
    }

    private static function contextFilters(array $filters): array
    {
        if ((int) Arr::get($filters,'__meta.theory_category_id') !== self::TYPES_CATEGORY_ID || Arr::get($filters,'__meta.theory_category_slug') !== self::TYPES_CATEGORY_SLUG) { return $filters; }
        $groups = $filters['theory_category_page_groups'] ?? [];
        $wrapped = array_map(static fn($g) => ['seeder_classes' => $g['seeder_classes'] ?? [], 'base_filters' => ['theory_page_mixed_type_profile' => self::REVISION]], is_array($groups) ? $groups : []);
        if (self::categoryProfile(self::TYPES_CATEGORY_ID,self::TYPES_CATEGORY_SLUG,$wrapped) !== self::REVISION) { return $filters; }
        Arr::set($filters,'__meta.theory_category_id',self::CATEGORY_ID); Arr::set($filters,'__meta.theory_category_slug',self::CATEGORY_SLUG);
        return $filters;
    }
    public static function stateNamespace(array $filters,string $locale,string $uuid = ''): ?string { return parent::stateNamespace(self::contextFilters($filters),$locale,$uuid); }
    public static function testContextNeedsRefresh(array $cached,array $filters,string $locale,string $uuid = ''): bool { return parent::testContextNeedsRefresh($cached,self::contextFilters($filters),$locale,$uuid); }

    public static function savedBuilderDisplayCount(string $uuid,string $slug,array $filters,string $locale): ?int
    {
        if ($locale !== 'uk') { return null; }
        // Only these frozen builder identities can match metadataPatches().
        // Owned tests still verify the complete catalog and all context guards.
        if (!in_array($uuid, array_column(array_column(self::ORIGINALS, 'builder'), 0), true)) { return null; }
        try {
            foreach (self::metadataPatches() as $patch) {
                if ($patch['kind'] !== 'builder' || $uuid !== $patch['before']['uuid'] || $slug !== $patch['before']['slug']
                    || self::stateNamespace($filters,$locale,$uuid) === null) { continue; }
                foreach (['slug','category_slug_path','page_seeder_class'] as $key) {
                    if (Arr::get($filters,'prompt_generator.theory_page.'.$key) !== Arr::get($patch['before']['filters'],'prompt_generator.theory_page.'.$key)) { return null; }
                }
                return count(self::compile($patch['topic'],'builder')['questions']);
            }
        } catch (Throwable) { }
        return null;
    }
}
