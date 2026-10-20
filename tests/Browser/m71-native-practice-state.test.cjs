'use strict';
// Frozen M71 learning data in the exact unchanged CLI factory. NOT browser acceptance.
// Pure isolated VM: no working .env, SQL, progress, network, runtime receipts or file writes.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const root = path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(p, 'utf8');
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const hash = p => digest(fs.readFileSync(p));
const packageSource = read(path.join(root, 'app/Support/M71ArticlesQuantifiers.php'));
const pin = name => {
    const match = packageSource.match(new RegExp("public const " + name + " = '([a-f0-9]{64})';"));
    assert.ok(match, 'frozen package must publish raw SHA-256 for ' + name);
    return match[1];
};
const manifestPath = path.join(root, 'docs/content/m71-source-checksums.v1.json');
assert.equal(hash(manifestPath), pin('CHECKSUMS_SHA'), 'raw frozen checksum manifest');
const manifest = JSON.parse(read(manifestPath));
assert.equal(manifest.status, 'frozen');
assert.equal(manifest.package, 'm71-articles-quantifiers');
const masterPath = path.join(root, 'docs/content/m71-articles-quantifiers.v1.0.0.json');
const masterSha = pin('MASTER_SHA');
assert.equal(hash(masterPath), masterSha, 'raw frozen authored master');
const master = JSON.parse(read(masterPath));
assert.equal(master.status, 'frozen');
assert.equal(master.base_sha, '1291b576b47dc28e5ba9e0c58faa052f6e49460e');
assert.equal(master.lessons.length, 3);
for (const relative of ['docs/content/m71-articles-quantifiers.v1.0.0.json','docs/content/m71-before-source.v1.json']) {
    const entry = manifest.artifacts.find(a => a.path === relative);
    assert.ok(entry, 'frozen artifact manifest entry');
    const absolute = path.join(root, relative);
    assert.equal(hash(absolute), entry.sha256, 'manifest raw-byte binding');
    assert.equal(fs.statSync(absolute).size, entry.bytes);
}
const sourcePath = path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const sourceSha = hash(sourcePath);
assert.equal(sourceSha, '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'unchanged M70 shipped factory');
const source = read(sourcePath);
const scripts = [...source.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)]
    .map(m => m[1]).filter(s => /Alpine\.data\(\s*['"]theoryPracticeSet['"]/.test(s));
assert.equal(scripts.length, 1);
assert.doesNotMatch(scripts[0], /@(?:php|json|js|endphp)\b|\{\{!!?/);
let factory, listener, networkCalls = 0;
const answerVariantsPath = path.join(root, 'tests/Browser/load-answer-variants.cjs');
const context = vm.createContext({
    window: {EnglishAnswerVariants: require(answerVariantsPath)},
    document: {
        addEventListener(name, callback) {assert.equal(name, 'alpine:init'); listener = callback;},
        querySelector() {return {focus() {}};},
    },
    Alpine: {data(name, callback) {assert.equal(name, 'theoryPracticeSet'); factory = callback;}},
    fetch() {networkCalls++; throw Error('Network forbidden');},
    URL,
});
vm.runInContext(scripts[0], context, {filename: sourcePath, timeout: 1000});
listener();
const make = data => {
    const model = factory({...data, i18n: {empty:'Введи відповідь.', correct:'Правильно.',
        incorrect:'Неправильно.', answer:'Правильна відповідь', score:'Результат: :correct / :total'}});
    model.$nextTick = callback => callback();
    model.init();
    return model;
};
const groups = ['selects', 'choices', 'inputs', 'rephrase'];
const answerProps = {selects:'selectAnswers', choices:'choiceAnswers', inputs:'inputAnswers', rephrase:'rephraseAnswers'};
const expected = [
    {
        file:'articles.json', identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesTheorySeeder',
        selects:['an','the','Без артикля','an'],
        choices:['Співрозмовник може визначити потрібну папку','Подобається конкретна музика на цьому заході'],
        inputs:['a university','an honest answer','a European country','I bought an old bicycle.','Can you close the door?'],
        rephrase:[null,'I love music.','She lives in France.'],
        full_sentence_inputs:[3,4], token_inputs:[0,1,2,3,4],
    },
    {
        file:'zero.json', identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityZeroArticleTheorySeeder',
        selects:['Без артикля','Без артикля','Без артикля','Без артикля'],
        choices:['Діти навчаються; батько відвідує конкретну будівлю','Так: без артикля можливий і окремий епізод'],
        inputs:['I go to work by bus.','Без артикля','the','Marta goes to school on Saturdays.','We had breakfast at home.'],
        rephrase:[null,'I live in Ukraine.','Dogs are loyal animals.'],
        full_sentence_inputs:[0,3,4], token_inputs:[0,3,4],
    },
    {
        file:'quantifiers.json', identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityQuantifiersTheorySeeder',
        selects:['much','many','a lot of','little'],
        choices:['Кілька проблем є; це не гарантує добру оцінку','Помилок мало; це може бути бажаним результатом'],
        inputs:['a lot of','much','many','We have a few chairs.','We do not have much time.'],
        rephrase:[null,"She doesn't have much time.",'There are many people in the room.'],
        full_sentence_inputs:[3,4], token_inputs:[3,4],
    },
];
const beforePath = path.join(root, 'docs/content/m71-before-source.v1.json');
const beforeSha = hash(beforePath);
assert.equal(beforeSha,pin('BEFORE_SHA'),'independent versioned BEFORE raw-byte binding');
const before = JSON.parse(read(beforePath));
assert.equal(before.content_type,'M71_source_before');
assert.equal(before.targets.length,3);
const beforePractices = expected.map((e,i) => {
    const target = before.targets[i];
    assert.equal(target.identity,e.identity,'independent source owner');
    const definition = target.before;
    assert.equal(definition.seeder.class,e.identity);
    const blocks = definition.page.blocks.filter(b => b.type === 'practice-set');
    assert.equal(blocks.length,1);
    return JSON.parse(blocks[0].body);
});

// Reproduce data/engine inconsistencies from independent captured bytes, not current AFTER.
// This section does not claim real UI interaction; the main agent has separate browser evidence.
const beforeDefects = [];
{
    const data = beforePractices[0], model = make(data), stated = ['a','an','a'];
    assert.match(data.input_intro, /a або an/);
    for (let i=0; i<3; i++) {
        model.inputAnswers[i] = stated[i];
        assert.equal(model.isCorrect('inputs', i), false);
        model.inputAnswers[i] = data.inputs[i].answer;
        assert.equal(model.isCorrect('inputs', i), true);
        beforeDefects.push({lesson:0, group:'inputs', index:i, defect:'article-only instruction versus full-phrase key',
            instruction_answer:stated[i], instruction_answer_correct:false, full_phrase_correct:true});
    }
}
{
    const data = beforePractices[1], model = make(data);
    assert.deepEqual(data.options, ['Ø (без артикля)','the']);
    for (let i=0; i<4; i++) {
        assert.equal(data.selects[i].answer, 'Ø');
        model.selectAnswers[i] = data.options[0];
        assert.equal(model.isCorrect('selects', i), false);
        const displayNorm = model.normalize(data.options[0]);
        model.selectAnswers[i] = 'Ø';
        assert.equal(model.isCorrect('selects', i), true);
        assert.notEqual(displayNorm, model.normalize('Ø'));
        beforeDefects.push({lesson:1, group:'selects', index:i, defect:'decorated visible option differs from canonical key',
            visible_option_correct:false, raw_key_correct:true});
    }
    assert.equal((data.inputs[0].before.match(/___/g)||[]).length, 2, 'two old blanks in one control');
    for (const i of [0,1]) {
        assert.equal(data.inputs[i].answer, '');
        assert.equal(model.hasAnswer('inputs', i), false, 'empty old key is excluded, not a valid zero-article answer');
        for (const blank of ['', ' ', '\t']) {
            model.inputAnswers[i] = blank;
            model.check('inputs');
            assert.equal(model.isCorrect('inputs', i), false);
        }
        beforeDefects.push({lesson:1, group:'inputs', index:i, defect:'empty canonical key excludes intended task from grading',
            has_gradable_answer:false, untouched_awarded:false, source_blank_count:(data.inputs[i].before.match(/___/g)||[]).length});
    }
    assert.equal(model.scoreText('inputs'), 'Результат: 0 / 1', 'only the old the answer is checkable');
}
{
    const data = beforePractices[2], model = make(data);
    assert.deepEqual(data.options, ['Перший варіант','Другий варіант']);
    for (let i=0; i<4; i++) {
        for (const option of data.options) {
            model.selectAnswers[i] = option;
            assert.equal(model.isCorrect('selects', i), false);
        }
        model.selectAnswers[i] = data.selects[i].answer;
        assert.equal(model.isCorrect('selects', i), true);
        beforeDefects.push({lesson:2, group:'selects', index:i, defect:'neither visible ordinal option matches word key',
            correct_visible_options:0, direct_word_key_correct:true});
    }
    assert.match(data.input_intro, /a lot of/);
    model.inputAnswers[2] = 'a lot of';
    assert.equal(model.isCorrect('inputs', 2), false);
    model.inputAnswers[2] = 'many';
    assert.equal(model.isCorrect('inputs', 2), true);
    beforeDefects.push({lesson:2, group:'inputs', index:2, defect:'stated a lot of alternative rejected by many-only key',
        a_lot_of_correct:false, many_correct:true});
}

const draftHashes = {}, records = [], notes = [], lessonResults = [];
let taskCount=0, optionCount=0, acceptedCount=0, fullSentencePaths=0, tokenPathCount=0, demos=0;
function plainFeedback(value, label) {
    assert.equal(typeof value, 'string', label);
    assert.match(value, /[\u0400-\u04ff]/, 'feedback includes Ukrainian');
    assert.doesNotMatch(value, /<\/?[a-z]|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu, 'x-text is plain text');
}
function assemble(model, index, key) {
    const remaining = [...model.inputTokenBank(index)];
    assert.equal(new Set(remaining.map(t=>t.id)).size, remaining.length, 'token instance IDs unique');
    assert.ok(remaining.length > 1);
    for (const word of key.split(/\s+/)) {
        const at = remaining.findIndex(token => token.value === word);
        assert.notEqual(at, -1, 'exact case/multiplicity instance for '+word);
        model.appendInputToken('inputs', index, remaining.splice(at,1)[0]);
    }
    assert.equal(model.inputAnswers[index], key, 'exact token assembly');
    assert.equal(model.isCorrect('inputs', index), true);
}
for (const [li,e] of expected.entries()) {
    const lesson = master.lessons[li];
    draftHashes[e.file] = digest(Buffer.from(JSON.stringify(lesson), 'utf8'));

    assert.equal(lesson.identity, e.identity);
    assert.equal(lesson.locale, 'uk');
    const practices = lesson.blocks.filter(b => b.type === 'practice-set');
    assert.equal(practices.length, 1, 'one continuing own practice');
    const data = practices[0].data;
    const inherited = beforePractices[li];
    assert.equal(data.selects.length, 4); assert.equal(data.choices.length, 2);
    assert.equal(data.inputs.length, 5); assert.equal(data.rephrase.length, 3);
    assert.deepEqual(data.selects.map(x=>Object.hasOwn(x,'id')), [false,false,false,false], 'old select identity absence');
    assert.deepEqual(data.inputs.slice(0,3).map(x=>Object.hasOwn(x,'id')), [false,false,false], 'old input identity absence');
    assert.deepEqual(data.rephrase.map(x=>Object.hasOwn(x,'id')), [false,false,false], 'old rephrase/demo identity absence');
    for (const group of ['selects','inputs','rephrase']) {
        const retainedCount = group==='selects'?4:3;
        for (let i=0; i<retainedCount; i++) {
            assert.equal(Object.hasOwn(data[group][i],'source_index'), Object.hasOwn(inherited[group][i],'source_index'));
        }
    }
    const added = [...data.choices,...data.inputs.slice(3)];
    assert.equal(added.length,4);
    assert.equal(new Set(added.map(x=>x.id)).size,4);
    for (const task of added) assert.match(task.id, /^m71-/);
    for (const i of e.full_sentence_inputs) {
        assert.equal(typeof data.inputs[i].after, 'string', 'full-sentence condition uses supported after field');
        assert.match(data.inputs[i].after, /[\u0400-\u04ff]/, 'visible Ukrainian full-sentence condition');
        assert.ok(!data.inputs[i].after.includes(data.inputs[i].answer), 'no ready English answer in condition');
    }
    const presentTokenIndices = data.inputs.flatMap((item,i)=>typeof item.before==='string'&&item.before.includes('/')?[i]:[]);
    assert.deepEqual(presentTokenIndices,e.token_inputs, 'actual supported slash-bank inputs');
    for (const [i,item] of data.selects.entries()) {
        if (Object.hasOwn(item,'prompt')) {
            // Select prompt is NOT consumed by this view. Never count it as visible learning text.
            assert.match(item.label, /[\u0400-\u04ff]/);
            assert.ok(item.label.length>item.prompt.length);
            notes.push({lesson:li,group:'selects',index:i,field:'prompt',status:'not_rendered_by_native_select',
                consequence:'complete condition is present in rendered label; redundant field should be removed before freeze'});
        }
    }
    const initial = make(data);
    for (const g of groups) {
        assert.equal(data[g].length,e[g].length);
        const total=e[g].filter(key=>key!==null).length;
        assert.equal(initial.scoreText(g),`Результат: 0 / ${total}`);
        for (const [i,item] of data[g].entries()) {
            const key=e[g][i];
            if (key===null) {demos++;assert.ok(item.example_original);assert.equal(initial.hasAnswer(g,i),false);continue;}
            taskCount++;
            assert.equal(item.answer,key, 'independent canonical '+li+'/'+g+'/'+i);
            assert.ok(Array.isArray(item.accepted)&&item.accepted.includes(key));
            assert.ok(item.accepted.every(a=>typeof a==='string'&&a.trim()!==''), 'no empty canonical/alias');
            const model=make(data),prop=answerProps[g];
            const rec={lesson:li,group:g,index:i,canonical:key,accepted:[],options:[],states:[]};
            for (const blank of ['', ' ', '\t', '  \n  ']) {
                model[prop][i]=blank;model.check(g);
                assert.equal(model.isCorrect(g,i),false,'untouched/whitespace is never an answer');
                assert.equal(model.feedbackText(g,i),'Введи відповідь.');
                assert.equal(model.scoreText(g),`Результат: 0 / ${total}`);
            }
            rec.states.push('empty/whitespace false');
            model[prop][i]='zzzz wrong';model.check(g);assert.equal(model.isCorrect(g,i),false);
            const wrongScore=model.scoreText(g);model.check(g);assert.equal(model.scoreText(g),wrongScore);
            plainFeedback(model.feedbackText(g,i),'fallback feedback');
            rec.states.push('wrong/repeated-check');
            for (const alias of item.accepted) {
                model[prop][i]=alias;model.check(g);assert.equal(model.isCorrect(g,i),true,'canonical/alias '+alias);
                plainFeedback(model.feedbackText(g,i),'canonical/alias feedback');
                model[prop][i]='  '+alias.toUpperCase()+'  ';
                assert.equal(model.isCorrect(g,i),true,'case/whitespace normalization');
                rec.accepted.push(alias);acceptedCount++;
            }
            for (const feedback of Object.values(item.feedback||{})) plainFeedback(feedback,'bounded feedback value');
            const options=item.options||(g==='selects'?data.options:g==='choices'?data.choice_options:[])||[];
            if (g==='selects'||g==='choices') {
                assert.ok(options.length>=2,'actual visible options');
                assert.equal(new Set(options.map(o=>model.normalize(o))).size,options.length,'no normalized option collision');
                let correctOptions=0;
                for (const option of options) {
                    model[prop][i]=option;model.check(g);
                    const correct=item.accepted.some(a=>model.normalize(a)===model.normalize(option));
                    assert.equal(model.isCorrect(g,i),correct,'every correct/distractor');
                    assert.equal(typeof item.feedback[option.trim().toLowerCase()],'string','every visible option plain feedback');
                    plainFeedback(model.feedbackText(g,i),'actual option feedback');
                    correctOptions+=Number(correct);rec.options.push({value:option,correct});optionCount++;
                }
                assert.equal(correctOptions,1,'one intended visible answer');
            }
            if (g==='inputs'&&e.token_inputs.includes(i)) {
                tokenPathCount++;model.resetGroup(g);assemble(model,i,key);
                const score=model.scoreText(g);model.check(g);model.check(g);assert.equal(model.scoreText(g),score);
                const words=key.split(/\s+/),prefix=words.slice(0,-1).join(' ');
                model.inputAnswers[i]=prefix;model.syncInputTokenBank(i);
                assert.equal(model.isCorrect(g,i),false);
                assert.ok(model.inputTokenBank(i).some(t=>!t.used),'deleted fragment returns to bank');
                model.inputAnswers[i]=key;model.syncInputTokenBank(i);assert.equal(model.isCorrect(g,i),true);
                rec.states.push('exact token case/instances/delete-return/rebuild');
                if (e.full_sentence_inputs.includes(i)) {
                    fullSentencePaths++;
                    assert.equal(model.inputTokenBank(i).filter(t=>!t.used).length,0,'full sentence uses its full authored bank');
                    assert.doesNotMatch(key,/Ø|Без артикля/,'zero article is not an English word/token');
                    assert.ok(model.inputTokenBank(i).every(t=>t.value.trim()!==''),'no empty token');
                    const withoutArticle=key.replace(/\b(?:a|an|the)\s+/i,'');
                    if(withoutArticle!==key){model.inputAnswers[i]=withoutArticle;assert.equal(model.isCorrect(g,i),false,'article remains part of answer');}
                } else {
                    assert.equal(model.inputTokenBank(i).filter(t=>!t.used).length,1,'short article phrases retain one explicit distractor');
                }
            }
            model[prop][i]=key;model.check(g);assert.equal(model.isCorrect(g,i),true);
            const correctScore=model.scoreText(g);model.check(g);assert.equal(model.scoreText(g),correctScore);
            assert.equal(correctScore,`Результат: 1 / ${total}`,'correct edit counted once');
            model.resetGroup(g);assert.equal(model.scoreText(g),`Результат: 0 / ${total}`);
            model.check(g);assert.equal(model.isCorrect(g,i),false);assert.equal(model.scoreText(g),`Результат: 0 / ${total}`);
            rec.states.push('wrong-edit-correct/idempotent-score/reset-empty-check');
            records.push(rec);
        }
    }
    const full=make(data);
    for (const g of groups) {
        for (const [i,key] of e[g].entries()) if(key!==null) full[answerProps[g]][i]=key;
        full.check(g);assert.equal(full.scoreText(g),`Результат: ${e[g].filter(k=>k!==null).length} / ${e[g].filter(k=>k!==null).length}`);
    }
    for (const g of groups) {
        const otherScores=groups.filter(other=>other!==g).map(other=>[other,full.scoreText(other)]);
        full.resetGroup(g);full.check(g);
        assert.equal(full.scoreText(g),`Результат: 0 / ${e[g].filter(k=>k!==null).length}`);
        for(const[other,score]of otherScores)assert.equal(full.scoreText(other),score,'independent group scores');
    }
    lessonResults.push({lesson:li,identity:e.identity,draft_sha256:draftHashes[e.file],graded_tasks:13,ungraded_demos:1,
        counts:{selects:4,choices:2,inputs:5,rephrase:2},full_sentence_input_indices:e.full_sentence_inputs,
        token_input_indices:e.token_inputs,group_score_reset_independence:true});
}

// Explicit zero-article regression: deliberate nonempty choice versus untouched/reset/space.
{
    const lesson=master.lessons[1],data=lesson.blocks.find(b=>b.type==='practice-set').data,x=make(data);
    assert.equal(data.inputs[1].answer,'Без артикля');
    for(const g of ['selects','inputs']){
        const i=g==='selects'?0:1,prop=answerProps[g];
        x[prop][i]='Без артикля';x.check(g);assert.equal(x.isCorrect(g,i),true);
        x.resetGroup(g);x.check(g);assert.equal(x.isCorrect(g,i),false);
        x[prop][i]=' ';x.check(g);assert.equal(x.isCorrect(g,i),false);
        x[prop][i]='Ø';x.check(g);assert.equal(x.isCorrect(g,i),false,'Ø is not a hidden alias for this deliberate label');
    }
    x.inputAnswers[0]='I go to the work by the bus.';assert.equal(x.isCorrect('inputs',0),false);
    x.inputAnswers[0]='';assert.equal(x.isCorrect('inputs',0),false,'zero-article sentence still requires a full answer');
}

// Separate synthetic engine probe: repeated article instances, not an extra own task.
const multiplicity={inputs:[{before:'a / book / a / I / pen. / and / bought / an',answer:'I bought a book and a pen.',accepted:['I bought a book and a pen.']}]};
{
    const x=make(multiplicity),bank=x.inputTokenBank(0),articles=bank.filter(t=>t.value==='a');
    assert.equal(articles.length,2);assert.notEqual(articles[0].id,articles[1].id);
    assemble(x,0,multiplicity.inputs[0].answer);
    assert.equal(x.inputTokenBank(0).filter(t=>t.value==='a'&&t.used).length,2);
    x.inputAnswers[0]='I bought a book and pen.';x.syncInputTokenBank(0);
    assert.equal(x.isCorrect('inputs',0),false);
    assert.equal(x.inputTokenBank(0).filter(t=>t.value==='a'&&!t.used).length,1);
    x.resetGroup('inputs');assert.equal(x.inputTokenBank(0).filter(t=>t.value==='a'&&!t.used).length,2);
    x.check('inputs');assert.equal(x.isCorrect('inputs',0),false);
    assemble(x,0,multiplicity.inputs[0].answer);
}
assert.equal(taskCount,39);assert.equal(demos,3);assert.equal(optionCount,44);assert.equal(acceptedCount,46);
assert.equal(fullSentencePaths,7);assert.equal(tokenPathCount,10);assert.equal(networkCalls,0);
assert.equal(hash(sourcePath),sourceSha,'factory unchanged during run');
assert.equal(hash(beforePath),beforeSha,'independent BEFORE unchanged during run');
assert.equal(hash(masterPath),masterSha,'frozen master unchanged during run');
assert.equal(notes.length,0,'no unused select prompt in final main projection');
const receipt={schema_version:1,kind:'isolated actual native factory using frozen M71 master; NOT browser acceptance',
    master_sha256:masterSha,
    factory_sha256:sourceSha,answer_variant_loader_sha256:hash(answerVariantsPath),before_source_sha256:beforeSha,
    lesson_semantic_digests:draftHashes,totals:{graded_own_tasks:taskCount,ungraded_demos:demos,visible_options:optionCount,
        accepted_values:acceptedCount,full_sentence_token_paths:fullSentencePaths,all_native_token_paths:tokenPathCount,
        separate_synthetic_multiplicity_probes:1,before_defect_records:beforeDefects.length},
    lesson_results:lessonResults,before_defects:beforeDefects,unsupported_field_notes:notes,records,
    zero_deliberate_choice_and_empty_regression:true,group_score_reset_independence:true,
    network_calls:networkCalls,db_writes:0,original_draft_writes:0,receipt_write:'none; pure test',
    browser_visibility:'not_checked_by_this_script',browser_interaction:'not_checked_by_this_script'};
console.log(JSON.stringify({master_sha256:masterSha,...receipt.totals,
    unsupported_fields:notes.length,network_calls:networkCalls,db_writes:0,file_writes:0}));
