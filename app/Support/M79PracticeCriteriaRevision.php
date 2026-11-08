<?php

namespace App\Support;

use RuntimeException;

/** Exact six-label successor; frozen 1.0.0 content remains a historical input. */
final class M79PracticeCriteriaRevision
{
    public const LEDGER = 'docs/content/m79-practice-criteria-revision.v1.0.1.json';
    public const LEDGER_SHA = '333f101e6fb32c122d5d20ffbe67f4482f43d157b4d8b163cc7222d40b4abfc5';
    public const OLD_PATCH = 'database/content-patches/m79-question-types-completion.v1.json';
    public const OLD_PATCH_SHA = '0e0b1406a376e392c4b12028e589ec099e4a675599b6907a873b738474e51d11';
    public const OLD_MASTER = 'docs/content/m79-question-types-completion.v1.json';
    public const OLD_MASTER_SHA = '1941b5bc4976d9526145e32216f429b5bde81a6776718c894634a0e0ee9f532f';
    public const PATCH = 'database/content-patches/m79-question-types-completion.v1.0.1.json';
    public const PATCH_SHA = '9af348eae92ac0cb361f5a9da2c407275037cfb1fc1673e84486abef31d7396c';
    public const MASTER = 'docs/content/m79-question-types-completion.v1.0.1.json';
    public const MASTER_SHA = '8ec1e542444fbf26bea877cba2896c5bf64cea959b91b1fc51914f9a7b0ecc7f';
    public const OLD_AUDIT = 'docs/content/m79-sentence-builder-bank-audit.v1.json';
    public const OLD_AUDIT_SHA = '9953bcf155eac62bc7369d6859513cc682bd46175eed2ce3bcd5f76eb300bf49';
    public const AUDIT = 'docs/content/m79-sentence-builder-bank-audit.v1.0.1.json';
    public const AUDIT_SHA = 'd3acbfc669a8842f11dcbf040493541a66c0e07305c626ce62bba4aee071ebd7';
    public const ROW_IDS = [4243,4372];
    public const UUIDS = ['37f2ff28-6385-5261-bee9-c30d5ec77172','221438bd-95b6-57fd-87cf-b1bb6a06a987'];
    public const LESSON_INDICES = [0,2];

    public static function fileHash(string $path): string
    {
        if (!is_file($path)||is_link($path)) { throw new RuntimeException('M79 criteria frozen source is absent or linked.'); }
        return hash_file('sha256',$path);
    }

    public static function pinned(string $path,string $sha): array
    {
        if (!hash_equals($sha,self::fileHash($path))) { throw new RuntimeException('M79 criteria frozen source/evidence drift.'); }
        return json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
    }

    /** Only these six literal label prefixes may change; no answers or other learning fields. */
    public static function exactTransition(array $old,array $next,array $oldMaster,array $newMaster,array $ledger): array
    {
        $normalized=$next; $normalizedMaster=$newMaster; $labels=[]; $transitions=[]; $bodies=[];
        if (($oldMaster['version']??null)!=='1.0.0'||($newMaster['version']??null)!=='1.0.1') {
            throw new RuntimeException('M79 criteria predecessor/successor version differs.');
        }
        $normalizedMaster['version']='1.0.0';
        foreach (self::LESSON_INDICES as $n=>$i) {
            $owner=M79QuestionTypesCompletion::OWNERS[$i];
            $a=$old['targets'][$i]['after']['page']['blocks'][11]['body']??null;
            $b=$next['targets'][$i]['after']['page']['blocks'][11]['body']??null;
            if (!is_string($a)||!is_string($b)||$a===$b) { throw new RuntimeException('M79 criteria exact two bodies required.'); }
            $before=json_decode($a,true,flags:JSON_THROW_ON_ERROR); $after=json_decode($b,true,flags:JSON_THROW_ON_ERROR);
            $projected=$before;
            if (count($before['selects']??[])!==3) { throw new RuntimeException('M79 criteria exact three-select group differs.'); }
            foreach ($before['selects'] as $j=>$task) {
                $prompt=$task['prompt']??null; $label=$task['label']??null;
                if (!is_string($prompt)||trim($prompt)!==$prompt||$prompt===''||!is_string($label)||str_starts_with($label,$prompt)) {
                    throw new RuntimeException('M79 criteria predecessor is not the known missing visible criterion.');
                }
                $projected['selects'][$j]['label']=$prompt.' '.$label;
                $labels[]=['lesson_index'=>$i,'identity'=>$owner,'block_source_index'=>11,'group'=>'selects','group_index'=>$j,
                    'prompt'=>$prompt,'before_label'=>$label,'after_label'=>$prompt.' '.$label,'other_task_fields_identical'=>true];
                $oldTask=$oldMaster['lessons'][$i]['blocks'][11]['data']['selects'][$j]??null;
                $newTask=$newMaster['lessons'][$i]['blocks'][11]['data']['selects'][$j]??null;
                if ($oldTask!==$task||$newTask!==$projected['selects'][$j]) { throw new RuntimeException('M79 criteria master task differs from exact body successor.'); }
                $normalizedMaster['lessons'][$i]['blocks'][11]['data']['selects'][$j]['label']=$label;
            }
            if ($after!==$projected||$b!==json_encode($projected,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)) {
                throw new RuntimeException('M79 criteria changes more than its exact three label prefixes.');
            }
            $normalized['targets'][$i]['after']['page']['blocks'][11]['body']=$a;
            $transitions[]=['lesson_index'=>$i,'identity'=>$owner,'definition_path'=>$old['targets'][$i]['path'],
                'page_id'=>M79QuestionTypesCompletion::PAGE_IDS[$i],'page_category_id'=>22,'locale'=>'uk','block_source_index'=>11,
                'type'=>'practice-set','column'=>'left','before_body_sha256'=>hash('sha256',$a),'after_body_sha256'=>hash('sha256',$b),
                'allowed_field'=>'body','only_six_select_labels_changed'=>true];
            $bodies[]=['id'=>self::ROW_IDS[$n],'identity'=>$owner,'page_id'=>M79QuestionTypesCompletion::PAGE_IDS[$i],
                'uuid'=>self::UUIDS[$n],'level'=>M79QuestionTypesCompletion::LEVELS[$i],'before'=>$a,'after'=>$b];
        }
        if ($normalized!==$old||$normalizedMaster!==$oldMaster||($ledger['schema_version']??null)!==1
            ||($ledger['package']??null)!=='m79-practice-criteria-revision'||($ledger['version']??null)!=='1.0.1'
            ||($ledger['status']??null)!=='frozen'||($ledger['labels']??null)!==$labels||($ledger['transitions']??null)!==$transitions) {
            throw new RuntimeException('M79 criteria exact diff/ledger differs.');
        }
        return $bodies;
    }

    public static function exactAuditTransition(array $old,array $next,array $transitions,array $ledger): void
    {
        $normalized=$next; $changes=[];
        if (($old['version']??null)!=='1.0.0'||($next['version']??null)!=='1.0.1') { throw new RuntimeException('M79 criteria bank audit versions differ.'); }
        $normalized['version']='1.0.0';
        foreach (self::LESSON_INDICES as $n=>$i) {
            $a=$old['practice_bindings'][$i]['body_sha256']??null;
            $b=$next['practice_bindings'][$i]['body_sha256']??null;
            if ($a!==hash('sha256',$transitions[$n]['before'])||$b!==hash('sha256',$transitions[$n]['after'])) {
                throw new RuntimeException('M79 criteria bank binding differs from exact body transition.');
            }
            $normalized['practice_bindings'][$i]['body_sha256']=$a;
            $changes[]=['lesson_index'=>$i,'identity'=>$transitions[$n]['identity'],'before_body_sha256'=>$a,'after_body_sha256'=>$b];
        }
        if ($normalized!==$old||($ledger['bank_binding_successor']??null)!==[
            'version_before'=>'1.0.0','version_after'=>'1.0.1','practice_body_bindings'=>$changes,
            'targets_and_selected_questions_identical'=>true,'all_other_audit_fields_identical'=>true]) {
            throw new RuntimeException('M79 criteria bank audit changes beyond version and exact two body hashes.');
        }
    }

    public static function load(string $root): array
    {
        $ledger=self::pinned($root.'/'.self::LEDGER,self::LEDGER_SHA); $sources=[self::LEDGER=>self::LEDGER_SHA];
        foreach ($ledger['predecessor_pins']??[] as $path=>$sha) { self::pinned($root.'/'.$path,$sha); $sources[$path]=$sha; }
        foreach ($ledger['active_artifacts']??[] as $path=>$entry) {
            if (!hash_equals($entry['sha256'],self::fileHash($root.'/'.$path))||filesize($root.'/'.$path)!==$entry['bytes']) {
                throw new RuntimeException('M79 criteria active artifact drift.');
            }
            $sources[$path]=$entry['sha256'];
        }
        $old=self::pinned($root.'/'.self::OLD_PATCH,self::OLD_PATCH_SHA);
        $next=self::pinned($root.'/'.self::PATCH,self::PATCH_SHA);
        $oldMaster=self::pinned($root.'/'.self::OLD_MASTER,self::OLD_MASTER_SHA);
        $master=self::pinned($root.'/'.self::MASTER,self::MASTER_SHA);
        $transitions=self::exactTransition($old,$next,$oldMaster,$master,$ledger);
        self::exactAuditTransition(self::pinned($root.'/'.self::OLD_AUDIT,self::OLD_AUDIT_SHA),
            self::pinned($root.'/'.self::AUDIT,self::AUDIT_SHA),$transitions,$ledger);
        [$before,$active,$activeMaster,$contract]=M79QuestionTypesCompletion::load($root);
        M79QuestionTypesCompletion::validate($before,$active,$activeMaster,$contract);
        if ($active!==$next||$activeMaster!==$master) { throw new RuntimeException('M79 criteria successor is not the exact active native projection.'); }
        foreach (M79QuestionTypesCompletion::profile()['artifacts'] as $path=>$sha) { $sources[$path]=$sha; }
        foreach ($next['targets'] as $target) {
            $path=$target['path']; $sources[$path]=self::fileHash($root.'/'.$path);
            if (json_decode(file_get_contents($root.'/'.$path),true,flags:JSON_THROW_ON_ERROR)!==$target['after']) {
                throw new RuntimeException('M79 criteria canonical definition differs from exact successor.');
            }
        }
        return ['transitions'=>$transitions,'ledger'=>$ledger,'sources'=>$sources];
    }
}
