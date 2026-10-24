<?php

/** Pure finite assertions for the M85 diagnostic only; never a runtime adapter. */
final class M85LiveDiagnosticContract
{
    public const OWNERS=[
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePresentTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeNegativesTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeQuestionsTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBePastTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\VerbToBeFutureTheorySeeder',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\VerbToBe\\ThereIsThereAreTheorySeeder',
    ];
    public const MISTAKE_SLOTS=[4,4,4,5,5,4];

    /** All18 authored explanations are required; exactly the six E/F aliases remain literal. */
    public static function assertMistakeNotes(array $lessons):array
    {
        if(array_column($lessons,'identity')!==self::OWNERS){throw new RuntimeException('M85 diagnostic exact six-owner lesson order differs.');}
        $notes=0;$aliases=[];
        foreach($lessons as $i=>$lesson){
            $slot=self::MISTAKE_SLOTS[$i];$blocks=$lesson['blocks']??[];
            $found=array_keys(array_filter($blocks,static fn($b)=>($b['type']??null)==='mistakes-grid'));
            if($found!==[$slot]||count($blocks[$slot]['data']['items']??[])!==3){throw new RuntimeException('M85 diagnostic exact mistake slot/three-item inventory differs.');}
            foreach($blocks[$slot]['data']['items'] as $n=>$item){
                if(!is_string($item['note']??null)||trim($item['note'])===''){throw new RuntimeException('M85 required mistake note missing: '.$i.':'.$slot.':'.$n.'; a legacy hint is not an accepted substitute.');}
                ++$notes;$aliasExpected=$i===4||$i===5;
                if($aliasExpected){
                    if(!is_string($item['hint']??null)||trim($item['hint'])===''||$item['note']!==$item['hint']){throw new RuntimeException('M85 six required mistake note aliases must equal the retained authored hint byte-for-byte.');}
                    $aliases[]=['identity'=>$lesson['identity'],'source_index'=>$slot,'item_index'=>$n,'hint_sha256'=>hash('sha256',$item['hint']),'note_sha256'=>hash('sha256',$item['note']),'exact'=>true];
                }elseif(array_key_exists('hint',$item)){
                    throw new RuntimeException('M85 diagnostic unreviewed legacy hint outside the exact six E/F aliases.');
                }
            }
        }
        if($notes!==18||count($aliases)!==6){throw new RuntimeException('M85 diagnostic exact18notes/sixaliases inventory differs.');}
        return ['mistake_blocks'=>6,'required_notes'=>18,'exact_retained_hint_aliases'=>6,'aliases'=>$aliases];
    }

    /** Identical UA wording still needs two distinct, correctly paired DOM nodes. */
    public static function assertPairedMistakeDom(DOMXPath $xp,DOMNode $point,array $item,callable $plain):array
    {
        $wrong=$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-example--wrong ")]//*[@lang="en"]',$point);
        $right=$xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-example--right ")]//*[@lang="en"]',$point);
        $uk=$xp->query('.//p[@lang="uk" and contains(concat(" ",normalize-space(@class)," ")," theory-translation ")]',$point);
        if($wrong->length!==1||$right->length!==1||$uk->length!==2){throw new RuntimeException('M85 mistakes require two separate EN models and two separate UK translation nodes.');}
        foreach(['wrong_en'=>$wrong->item(0),'right_en'=>$right->item(0),'wrong_ua'=>$uk->item(0),'right_ua'=>$uk->item(1)] as $field=>$node){
            if(!is_string($item[$field]??null)||$plain($node->textContent)!==$plain($item[$field])){throw new RuntimeException('M85 ordered EN/UK correction field differs: '.$field);}
        }
        return ['english_nodes'=>2,'ukrainian_nodes'=>2,'ordered_fields_exact'=>true];
    }
}
