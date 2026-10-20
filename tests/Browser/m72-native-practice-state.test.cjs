'use strict';
// Frozen M72 learning data in the unchanged native factory. NOT browser acceptance.
// Pure VM: no app bootstrap, .env, SQL, progress, network, receipts or file writes.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const root = path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(p, 'utf8');
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const hash = p => digest(fs.readFileSync(p));
const packageSource = read(path.join(root, 'app/Support/M72PartitivesNegativesGeography.php'));
const pin = name => {
    const match = packageSource.match(new RegExp("public const " + name + " = '([a-f0-9]{64})';"));
    assert.ok(match, 'frozen package must publish raw SHA-256 for ' + name);
    assert.notEqual(match[1], '0'.repeat(64), 'release must be frozen before execution');
    return match[1];
};
const manifestPath = path.join(root, 'docs/content/m72-source-checksums.v1.json');
assert.equal(hash(manifestPath), pin('CHECKSUMS_SHA'), 'frozen manifest raw binding');
const manifest = JSON.parse(read(manifestPath));
assert.equal(manifest.status, 'frozen');
assert.equal(manifest.package, 'm72-partitives-negatives-geography');
const masterPath = path.join(root, 'docs/content/m72-partitives-negatives-geography.v1.0.0.json');
const masterSha = pin('MASTER_SHA');
assert.equal(hash(masterPath), masterSha, 'frozen master raw binding');
const master = JSON.parse(read(masterPath));
assert.equal(master.status, 'frozen');
assert.equal(master.base_sha, '2bcd468b25af3c44dcf34ccee0e012f3f683335d');
assert.equal(master.lessons.length, 3);
for (const relative of ['docs/content/m72-partitives-negatives-geography.v1.0.0.json', 'docs/content/m72-before-source.v1.json']) {
    const entry = manifest.artifacts.find(a => a.path === relative);
    assert.ok(entry, 'frozen artifact manifest entry');
    const absolute = path.join(root, relative);
    assert.equal(hash(absolute), entry.sha256, 'manifest raw-byte binding');
    assert.equal(fs.statSync(absolute).size, entry.bytes);
}
const sourcePath = path.join(root, 'resources/views/engram/theory/blocks-v3/practice-set.blade.php');
const sourceSha = hash(sourcePath);
assert.equal(sourceSha, '05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e', 'unchanged accepted native factory');
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
        addEventListener(name, callback) { assert.equal(name, 'alpine:init'); listener = callback; },
        querySelector() { return {focus() {}}; },
    },
    Alpine: {data(name, callback) { assert.equal(name, 'theoryPracticeSet'); factory = callback; }},
    fetch() { networkCalls++; throw Error('Network forbidden'); },
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
        file:'partitives.json', identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityPartitivesTheorySeeder',
        slot:12, slots:14,
        selects:['a piece of','a glass of','a loaf of','a bar of'],
        choices:['A','B'],
        inputs:['a piece of','a cup of','a jar of','There are two bottles of water here.','We need three cups of tea.'],
        rephrase:[null,'Can I have a cup of coffee?','She bought two loaves of bread.'],
        full_sentence_inputs:[3,4], token_inputs:[3,4],
    },
    {
        file:'negatives.json', identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder',
        slot:11, slots:13,
        selects:['no','None','Neither','Either'],
        choices:['Можна взяти будь-яку з двох книжок; чи хтось уже взяв книжку, не сказано.',
            'Not all six students passed: склали не всі, але четверо склали.'],
        inputs:['None','no','Neither','Neither key works.','You can use either entrance.'],
        rephrase:[null,'Neither of them came.','I have no money.'],
        full_sentence_inputs:[3,4], token_inputs:[3,4],
    },
    {
        file:'geography.json', identity:'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder',
        slot:13, slots:15,
        selects:['the','Без артикля','the','the'],
        choices:['У першому реченні місто, у другому річка','Без Lake назва може залишатися без артикля'],
        inputs:['I live in Ukraine.','The Dnipro is a beautiful river.','I climbed Mount Everest.',
            'She lives in the Netherlands.','We visited Lake Geneva.'],
        rephrase:[null,'The Thames is a long river.','I visited Mount Everest.'],
        full_sentence_inputs:[0,1,2,3,4], token_inputs:[0,1,2,3,4],
    },
];
const beforePath = path.join(root, 'docs/content/m72-before-source.v1.json');
const beforeSha = hash(beforePath);
assert.equal(beforeSha, pin('BEFORE_SHA'), 'independent versioned BEFORE raw binding');
const before = JSON.parse(read(beforePath));
assert.equal(before.content_type, 'M72_source_before');
assert.equal(before.targets.length, 3);
const beforePractices = expected.map((e,i) => {
    const target = before.targets[i];
    assert.equal(target.identity, e.identity);
    assert.equal(target.before.seeder.class, e.identity);
    assert.equal(target.before.page.blocks.length, e.slots);
    assert.equal(target.before.page.blocks[e.slot].type, 'practice-set');
    return JSON.parse(target.before.page.blocks[e.slot].body);
});
assert.equal(expected.reduce((n,e) => n + e.slots, 0), 42);

// Reproduce old defects from independent BEFORE, not current authored AFTER.
const beforeDefects = [];
for (const li of [0,1]) {
    const data = beforePractices[li], model = make(data);
    assert.deepEqual(data.options, ['Перший варіант','Другий варіант']);
    for (let i=0; i<4; i++) {
        for (const option of data.options) {
            model.selectAnswers[i] = option;
            assert.equal(model.isCorrect('selects', i), false);
        }
        model.selectAnswers[i] = data.selects[i].answer;
        assert.equal(model.isCorrect('selects', i), true);
        beforeDefects.push({lesson:li,group:'selects',index:i,defect:'ordinal visible options do not match word key'});
    }
}
{
    const data = beforePractices[2], model = make(data);
    assert.deepEqual(data.options, ['Ø (без артикля)','the']);
    assert.equal(data.selects[1].answer, 'Ø');
    model.selectAnswers[1] = data.options[0];
    assert.equal(model.isCorrect('selects',1), false);
    model.selectAnswers[1] = 'Ø';
    assert.equal(model.isCorrect('selects',1), true);
    beforeDefects.push({lesson:2,group:'selects',index:1,defect:'decorated zero option differs from key'});
    for (const i of [0,2]) {
        assert.equal(data.inputs[i].answer, '');
        assert.equal(model.hasAnswer('inputs', i), false);
        for (const blank of ['', ' ', '\t', ' \n ']) {
            model.inputAnswers[i] = blank; model.check('inputs');
            assert.equal(model.isCorrect('inputs', i), false);
        }
        beforeDefects.push({lesson:2,group:'inputs',index:i,defect:'empty key excluded from grading'});
    }
    assert.equal(model.scoreText('inputs'), 'Результат: 0 / 1');
}

function plainFeedback(value) {
    assert.equal(typeof value, 'string');
    assert.match(value, /[\u0400-\u04ff]/, 'feedback contains Ukrainian');
    assert.doesNotMatch(value, /<\/?[a-z]|&(?:#[0-9]+|#x[0-9a-f]+|[a-z][a-z0-9]+);/iu, 'feedback rendered by x-text is plain');
}
const unicodeAlias = value => value.replace(/[A-Za-z]/g, c => String.fromCharCode(c.charCodeAt(0)+0xfee0))
    .replace(/'/g, '\u2019').replace(/ /g, '\u00a0').normalize('NFD');
function assemble(model, index, key, partialProbe = false) {
    const remaining = [...model.inputTokenBank(index)], words = key.split(/\s+/);
    assert.equal(new Set(remaining.map(t=>t.id)).size, remaining.length, 'unique token instance IDs');
    assert.ok(remaining.length > 1);
    for (const [position,word] of words.entries()) {
        const at = remaining.findIndex(token => token.value === word);
        assert.notEqual(at, -1, 'exact case/multiplicity token for ' + word);
        const token = remaining.splice(at,1)[0];
        model.appendInputToken('inputs', index, token);
        const current = model.inputAnswers[index];
        model.appendInputToken('inputs', index, token);
        assert.equal(model.inputAnswers[index], current, 'used instance cannot be appended twice');
        if (partialProbe && position < words.length-1) {
            model.check('inputs');
            assert.equal(model.isCorrect('inputs', index), false, 'partial sentence never graded complete');
        }
    }
    assert.equal(model.inputAnswers[index], key);
    assert.equal(model.isCorrect('inputs', index), true);
}
const records = [], lessonResults = [], lessonDigests = {};
let taskCount=0, optionCount=0, acceptedCount=0, demos=0, addedTasks=0;
let fullSentencePaths=0, newSentenceManualPaths=0, newSentenceTokenPaths=0;
for (const [li,e] of expected.entries()) {
    const lesson = master.lessons[li];
    lessonDigests[e.file] = digest(Buffer.from(JSON.stringify(lesson),'utf8'));
    assert.equal(lesson.identity,e.identity); assert.equal(lesson.locale,'uk');
    assert.equal(lesson.blocks.length,e.slots);
    assert.deepEqual(lesson.blocks.map(b=>b.type),before.targets[li].before.page.blocks.map(b=>b.type));
    assert.deepEqual(lesson.blocks.map(b=>b.column),before.targets[li].before.page.blocks.map(b=>b.column));
    assert.equal(lesson.blocks[e.slot].type,'practice-set');
    assert.equal(lesson.blocks.filter(b=>b.type==='practice-set').length,1);
    const data = lesson.blocks[e.slot].data, inherited = beforePractices[li];
    assert.equal(data.selects.length,4); assert.equal(data.choices.length,2);
    assert.equal(data.inputs.length,5); assert.equal(data.rephrase.length,3);
    for (const [g,retained] of [['selects',4],['inputs',3],['rephrase',3]]) {
        for (let i=0;i<retained;i++) {
            assert.equal(Object.hasOwn(data[g][i],'id'),false,'old ID absence');
            assert.equal(Object.hasOwn(data[g][i],'source_index'),Object.hasOwn(inherited[g][i],'source_index'));
        }
    }
    const added = [...data.choices,...data.inputs.slice(3)]; addedTasks += added.length;
    assert.equal(new Set(added.map(x=>x.id)).size,4);
    for (const item of added) assert.match(item.id,/^m72-/);
    assert.deepEqual(data.inputs.flatMap((x,i)=>typeof x.before==='string'&&x.before.includes('/')?[i]:[]),e.token_inputs);
    for (const item of data.selects) assert.equal(Object.hasOwn(item,'prompt'),false,'unsupported select prompt removed');
    for (const i of e.full_sentence_inputs) {
        assert.equal(typeof data.inputs[i].after,'string');
        assert.match(data.inputs[i].after,/[\u0400-\u04ff]/,'supported visible condition');
        assert.ok(!data.inputs[i].after.includes(data.inputs[i].answer),'no whole English answer in condition');
        assert.doesNotMatch(data.inputs[i].answer,/Ø|Без артикля|[\u0400-\u04ff]/,'English sentence, not zero-label token');
    }
    for (const g of groups) {
        assert.equal(data[g].length,e[g].length);
        const total = e[g].filter(k=>k!==null).length, initial = make(data);
        assert.equal(initial.scoreText(g),'Результат: 0 / '+total);
        for (const [i,item] of data[g].entries()) {
            const key = e[g][i], prop = answerProps[g];
            if (key===null) {demos++;assert.ok(item.example_original);assert.equal(initial.hasAnswer(g,i),false);continue;}
            taskCount++;
            assert.equal(item.answer,key,'independent canonical '+li+'/'+g+'/'+i);
            assert.ok(Array.isArray(item.accepted)&&item.accepted.includes(key));
            assert.ok(item.accepted.every(a=>typeof a==='string'&&a.trim()!==''));
            const model = make(data), rec = {lesson:li,group:g,index:i,canonical:key,options:[],states:[]};
            for (const blank of ['', ' ', '\t', ' \n ', '\u00a0']) {
                model[prop][i]=blank; model.check(g);
                assert.equal(model.isCorrect(g,i),false);
                assert.equal(model.feedbackText(g,i),'Введи відповідь.');
                assert.equal(model.scoreText(g),'Результат: 0 / '+total);
            }
            model[prop][i]='zzzz wrong'; model.check(g);
            assert.equal(model.isCorrect(g,i),false);
            const wrongScore=model.scoreText(g); model.check(g); assert.equal(model.scoreText(g),wrongScore);
            plainFeedback(model.feedbackText(g,i));
            for (const alias of item.accepted) {
                for (const variant of [alias,'  '+alias.toUpperCase()+'  ',alias.replace(/[.!?]+$/,'')+'!!!',unicodeAlias(alias)]) {
                    model[prop][i]=variant; model.check(g);
                    assert.equal(model.isCorrect(g,i),true,'accepted/case/punctuation/Unicode alias '+variant);
                    plainFeedback(model.feedbackText(g,i));
                }
                acceptedCount++;
            }
            for (const feedback of Object.values(item.feedback||{})) plainFeedback(feedback);
            const options = item.options || (g==='selects'?data.options:g==='choices'?data.choice_options:[]) || [];
            if (g==='selects'||g==='choices') {
                assert.ok(options.length>=2);
                assert.equal(new Set(options.map(o=>model.normalize(o))).size,options.length,'no normalization collision');
                let correctOptions=0;
                for (const option of options) {
                    model[prop][i]=option; model.check(g);
                    const correct=item.accepted.some(a=>model.normalize(a)===model.normalize(option));
                    assert.equal(model.isCorrect(g,i),correct,'all actual visible options and distractors');
                    assert.equal(typeof item.feedback[option.trim().toLowerCase()],'string','literal option feedback lookup');
                    plainFeedback(model.feedbackText(g,i));
                    correctOptions+=Number(correct); optionCount++; rec.options.push({value:option,correct});
                }
                assert.equal(correctOptions,1);
            }
            if (g==='inputs'&&e.token_inputs.includes(i)) {
                fullSentencePaths++;
                model.resetGroup(g);
                const otherBanks = data.inputs.map((_,n)=>n===i?null:JSON.stringify(model.inputTokenBank(n)));
                assemble(model,i,key,true);
                for (let n=0;n<data.inputs.length;n++) if(n!==i) {
                    assert.equal(JSON.stringify(model.inputTokenBank(n)),otherBanks[n],'another task token bank stays independent');
                    assert.equal(model.inputAnswers[n],undefined,'another manual field remains untouched');
                }
                assert.equal(model.inputTokenBank(i).filter(t=>!t.used).length,0,'full authored bank consumed');
                const words=key.split(/\s+/), prefix=words.slice(0,-1).join(' ');
                model.inputAnswers[i]=prefix; model.syncInputTokenBank(i); model.check(g);
                assert.equal(model.isCorrect(g,i),false);
                const returned=model.inputTokenBank(i).find(t=>!t.used&&t.value===words.at(-1));
                assert.ok(returned,'deleted final token returns to bank');
                model.appendInputToken('inputs',i,returned);
                assert.equal(model.inputAnswers[i],key);
                assert.equal(model.isCorrect(g,i),true);
                const score=model.scoreText(g); model.check(g); model.check(g); assert.equal(model.scoreText(g),score);
                if(i>=3) {
                    newSentenceTokenPaths++;
                    model.resetGroup(g);
                    model.inputAnswers[i]=key; model.syncInputTokenBank(i); model.check(g);
                    assert.equal(model.isCorrect(g,i),true,'new full sentence manually typed');
                    assert.equal(model.inputTokenBank(i).filter(t=>!t.used).length,0,'manual entry syncs same native bank');
                    newSentenceManualPaths++;
                }
                rec.states.push('manual/token/partial/used-instance/delete-return/independence');
            }
            model[prop][i]=key; model.check(g); assert.equal(model.isCorrect(g,i),true);
            const correctScore=model.scoreText(g); model.check(g); assert.equal(model.scoreText(g),correctScore);
            assert.equal(correctScore,'Результат: 1 / '+total,'correct edit counted once');
            model.resetGroup(g); assert.equal(model.scoreText(g),'Результат: 0 / '+total);
            model.check(g); assert.equal(model.isCorrect(g,i),false);
            assert.equal(model.scoreText(g),'Результат: 0 / '+total);
            rec.states.push('empty/whitespace/wrong/repeat/edit-correct/reset');
            records.push(rec);
        }
    }
    const mixed=make(data);
    for(const g of groups) {
        const indices=e[g].flatMap((k,i)=>k===null?[]:[i]);
        mixed[answerProps[g]][indices[0]]='zzzz wrong';
        mixed[answerProps[g]][indices[1]]=e[g][indices[1]];
        mixed.check(g); assert.equal(mixed.scoreText(g),'Результат: 1 / '+indices.length,'mixed group result');
    }
    mixed.resetGroup('inputs'); mixed.check('inputs');
    assert.equal(mixed.scoreText('inputs'),'Результат: 0 / 5');
    for(const g of ['selects','choices','rephrase']) assert.equal(mixed.scoreText(g),'Результат: 1 / '+e[g].filter(k=>k!==null).length);
    const full=make(data);
    for(const g of groups) {
        for(const[i,key]of e[g].entries())if(key!==null)full[answerProps[g]][i]=key;
        full.check(g);
        const n=e[g].filter(k=>k!==null).length;
        assert.equal(full.scoreText(g),'Результат: '+n+' / '+n);
    }
    for(const g of groups) {
        const others=groups.filter(x=>x!==g).map(x=>[x,full.scoreText(x)]);
        full.resetGroup(g); full.check(g);
        assert.equal(full.scoreText(g),'Результат: 0 / '+e[g].filter(k=>k!==null).length);
        for(const[other,score]of others)assert.equal(full.scoreText(other),score,'group reset independence');
    }
    lessonResults.push({identity:e.identity,slot:e.slot,slots:e.slots,graded_tasks:13,demos:1,new_tasks:4,
        full_sentence_input_indices:e.full_sentence_inputs,token_input_indices:e.token_inputs});
}

// Same established two-person absence supports both neither and contextual none.
// Canonical remains Neither; this is a bounded accepted alias, not a global rewrite.
{
    const data=master.lessons[1].blocks[11].data,x=make(data);
    assert.equal(data.inputs[2].answer,'Neither');
    assert.ok(data.inputs[2].accepted.includes('None'),'contextual none alias explicitly retained');
    for(const value of ['Neither','None',' none ']) {
        x.inputAnswers[2]=value; x.check('inputs');
        assert.equal(x.isCorrect('inputs',2),true,'two known absent people allow this contextual alias');
        plainFeedback(x.feedbackText('inputs',2));
    }
    assert.equal(typeof data.inputs[2].feedback.none,'string','contextual Ukrainian none feedback');
    x.inputAnswers[2]='Either'; assert.equal(x.isCorrect('inputs',2),false,'positive either does not state both absent');
}
// Deliberate zero selection is nonempty; old empty geographical keys are now full sentences.
{
    const data=master.lessons[2].blocks[13].data,x=make(data);
    assert.equal(data.selects[1].answer,'Без артикля');
    x.selectAnswers[1]='Без артикля'; x.check('selects'); assert.equal(x.isCorrect('selects',1),true);
    for(const value of ['', ' ', 'Ø', 'Ø (без артикля)']) {
        x.selectAnswers[1]=value; assert.equal(x.isCorrect('selects',1),false);
    }
    x.resetGroup('selects'); x.check('selects'); assert.equal(x.isCorrect('selects',1),false);
    for(const i of [0,2]) {
        assert.ok(x.hasAnswer('inputs',i));
        for(const value of ['', ' ', 'Без артикля','Ø']) {
            x.inputAnswers[i]=value; assert.equal(x.isCorrect('inputs',i),false,'empty/label is not a full English sentence');
        }
        x.inputAnswers[i]=data.inputs[i].answer; assert.equal(x.isCorrect('inputs',i),true);
    }
    x.inputAnswers[0]='I live in the Ukraine.'; assert.equal(x.isCorrect('inputs',0),false);
    x.inputAnswers[2]='I climbed the Mount Everest.'; assert.equal(x.isCorrect('inputs',2),false);
}
// Independent synthetic multiplicity probe, not an added M72 own task.
const multiplicity={inputs:[{before:'a / book / a / I / pen. / and / bought / an',
    answer:'I bought a book and a pen.',accepted:['I bought a book and a pen.']}]};
{
    const x=make(multiplicity),articles=x.inputTokenBank(0).filter(t=>t.value==='a');
    assert.equal(articles.length,2); assert.notEqual(articles[0].id,articles[1].id);
    assemble(x,0,multiplicity.inputs[0].answer,true);
    assert.equal(x.inputTokenBank(0).filter(t=>t.value==='a'&&t.used).length,2);
    x.inputAnswers[0]='I bought a book and pen.'; x.syncInputTokenBank(0);
    assert.equal(x.isCorrect('inputs',0),false);
    assert.equal(x.inputTokenBank(0).filter(t=>t.value==='a'&&!t.used).length,1);
    x.resetGroup('inputs');
    assert.equal(x.inputTokenBank(0).filter(t=>t.value==='a'&&!t.used).length,2);
    x.check('inputs'); assert.equal(x.isCorrect('inputs',0),false);
}
assert.equal(taskCount,39); assert.equal(demos,3); assert.equal(addedTasks,12);
assert.equal(optionCount,36); assert.equal(acceptedCount,40);
assert.equal(fullSentencePaths,9);
assert.equal(newSentenceManualPaths,6); assert.equal(newSentenceTokenPaths,6);
assert.equal(beforeDefects.length,11); assert.equal(networkCalls,0);
assert.equal(hash(sourcePath),sourceSha);
assert.equal(hash(beforePath),beforeSha);
assert.equal(hash(masterPath),masterSha);
const receipt={schema_version:1,kind:'isolated unchanged native factory with frozen M72 master; NOT browser acceptance',
    master_sha256:masterSha,factory_sha256:sourceSha,before_source_sha256:beforeSha,
    answer_variant_loader_sha256:hash(answerVariantsPath),lesson_digests:lessonDigests,lesson_results:lessonResults,
    totals:{continuing_slots:42,graded_own_tasks:taskCount,ungraded_demos:demos,added_tasks:addedTasks,
        visible_options:optionCount,accepted_values:acceptedCount,full_sentence_token_paths:fullSentencePaths,
        new_full_sentence_manual_paths:newSentenceManualPaths,new_full_sentence_token_paths:newSentenceTokenPaths,
        separate_synthetic_multiplicity_probes:1,before_defect_records:beforeDefects.length},
    records,before_defects:beforeDefects,zero_deliberate_choice_and_empty_regression:true,
    mixed_scores_and_group_reset_independence:true,network_calls:networkCalls,db_writes:0,file_writes:0,
    browser_visibility:'not_checked_by_this_script',browser_interaction:'not_checked_by_this_script'};
console.log(JSON.stringify({master_sha256:masterSha,...receipt.totals,network_calls:networkCalls,db_writes:0,file_writes:0}));
