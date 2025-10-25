<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'question',
    'inputNamePrefix',
    'arrayInput' => false,
    'manualInput' => false,
    'autocompleteInput' => false,
    'builderInput' => false,
    'autocompleteRoute' => url('/api/search?lang=en'),
    'methodMap' => [],
    'showVerbHintEdit' => false,
    'showQuestionEdit' => false,
    'testSlug' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'question',
    'inputNamePrefix',
    'arrayInput' => false,
    'manualInput' => false,
    'autocompleteInput' => false,
    'builderInput' => false,
    'autocompleteRoute' => url('/api/search?lang=en'),
    'methodMap' => [],
    'showVerbHintEdit' => false,
    'showQuestionEdit' => false,
    'testSlug' => null,
]); ?>
<?php foreach (array_filter(([
    'question',
    'inputNamePrefix',
    'arrayInput' => false,
    'manualInput' => false,
    'autocompleteInput' => false,
    'builderInput' => false,
    'autocompleteRoute' => url('/api/search?lang=en'),
    'methodMap' => [],
    'showVerbHintEdit' => false,
    'showQuestionEdit' => false,
    'testSlug' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php
    $questionText = $question->question;
    preg_match_all('/\{a(\d+)\}/', $questionText, $matches);
    $replacements = [];
    foreach ($matches[0] as $i => $marker) {
        $num = $matches[1][$i];
        $markerKey = 'a' . $num;
        $inputName = $arrayInput
            ? "{$inputNamePrefix}[{$markerKey}]"
            : "{$inputNamePrefix}{$markerKey}";
        $verbHintRow = $question->verbHints->where('marker', $markerKey)->first();
        $verbHint = $verbHintRow?->option?->option;
        $method = $methodMap[$markerKey] ?? null;
        if($method === null) {
            if(!empty($builderInput)) {
                $method = 'builder';
            } elseif(!empty($manualInput) && !empty($autocompleteInput)) {
                $method = 'autocomplete';
            } elseif(!empty($manualInput)) {
                $method = 'text';
            } else {
                $method = 'select';
            }
        }
        if($method === 'autocomplete') {
            $input = <<<HTML
<div
    x-data="{open:false,value:'',suggestions:[],fetch(){if(this.value.length===0){this.suggestions=[];this.open=false;return;}fetch('{$autocompleteRoute}&q='+encodeURIComponent(this.value)).then(res=>res.json()).then(data=>{this.suggestions=data.map(i=>i.en);this.open=!!this.suggestions.length;});},pick(val){this.value=val;this.open=false;}}"
    class="relative inline-block"
    @click.away="open=false"
    x-init="\$watch('value', fetch)"
>
    <input type="text" name="{$inputName}" autocomplete="off" class="border rounded px-2 py-1 mx-1 w-[80px] h-[28px]" x-model="value" @focus="fetch(); open=true" @input="fetch(); open=true">
    <template x-if="open && suggestions.length">
        <ul class="absolute left-0 z-10 bg-white shadow-lg border mt-1 max-h-40 rounded-md overflow-auto w-full" style="min-width:120px">
            <template x-for="item in suggestions" :key="item">
                <li @mousedown.prevent="pick(item)" class="cursor-pointer px-3 py-1 hover:bg-blue-100" x-text="item"></li>
            </template>
        </ul>
    </template>
</div>
HTML;
        } elseif($method === 'builder') {
            $input = <<<HTML
<div x-data="builder('{$autocompleteRoute}', '{$inputName}[')" class="inline-flex items-center gap-[3px]">
    <template x-for="(word, index) in words" :key="index">
        <div class="relative w-[90px]">
            <input type="text" :name="'{$inputName}['+index+']'" class="border rounded px-2 py-1 w-[99%] h-[28px]" autocomplete="off" x-model="words[index]" pattern="^\\S+$" title="One word only" @keydown.space.prevent="completeWord(index)" @focus="fetchSuggestions(index)" @input="fetchSuggestions(index)">
            <template x-if="suggestions[index] && suggestions[index].length">
                <ul class="absolute left-0 z-10 bg-white shadow-lg border mt-1 max-h-40 rounded-md overflow-auto w-full">
                    <template x-for="suggestion in suggestions[index]" :key="suggestion">
                        <li class="cursor-pointer px-3 py-1 hover:bg-blue-100" @mousedown.prevent="selectSuggestion(index, suggestion)" x-text="suggestion"></li>
                    </template>
                </ul>
            </template>
        </div>
    </template>
    <button type="button" @click="addWord" class="bg-gray-200 px-2 py-1 rounded order-last  h-[28px]" style="line-height: 0">+</button>
    <button type="button" x-show="words.length > 1" @click="removeWord" class="bg-gray-200 px-2 py-1 rounded order-last  h-[28px]" style="line-height: 0">-</button>
</div>
HTML;
        } elseif($method === 'text') {
            $input = '<input type="text" name="'.$inputName.'" autocomplete="off" class="border rounded px-2 py-1 mx-1 w-[80px] h-[28px]">';
        } else {
            $input = '<select name="'.$inputName.'" class="border rounded px-2 py-1 mx-1 w-[80px] h-[28px]">';
            $input .= '<option value="">---</option>';
            foreach($question->options as $opt){
                $input .= '<option value="'.$opt->option.'">'.$opt->option.'</option>';
            }
            $input .= '</select>';
        }
        if($verbHintRow){
            $input .= ' <span class="verb-hint text-red-700 text-xs font-bold">(<span class="verb-hint-text">'.e($verbHint).'</span>)';
            if(!empty($showVerbHintEdit)){
                $input .= " <button type=\"button\" class=\"verb-hint-edit\" aria-label=\"Edit hint\" onclick=\"editVerbHint({$verbHintRow->id}, this)\"><i class=\"fa-solid fa-pen\"></i></button>";
                $input .= " <button type=\"button\" class=\"verb-hint-delete text-red-600\" aria-label=\"Delete hint\" onclick=\"deleteVerbHint({$verbHintRow->id}, this, {$question->id}, '{$markerKey}')\"><i class=\"fa-solid fa-trash\"></i></button>";
            }
            $input .= '</span>';
        } elseif(!empty($showVerbHintEdit)) {
            $input .= " <button type=\"button\" class=\"text-xs text-blue-600 underline\" onclick=\"addVerbHint({$question->id}, '{$markerKey}', this)\">Add hint</button>";
        }
        $replacements[$marker] = $input;
    }
    $finalQuestion = strtr(e($questionText), $replacements);

?>

<div
    x-data="{
        qid: <?php echo e($question?->id ?? 'null'); ?>,
        qtext: <?php echo \Illuminate\Support\Js::from($question->question)->toHtml() ?>,
        testSlug: <?php echo \Illuminate\Support\Js::from($testSlug)->toHtml() ?>,
        hints: { chatgpt: '', gemini: '' },
        fetchHints(refresh = false) {
            if (!this.qid && !this.qtext) return; // немає даних питання
            const payload = { refresh };
            if (this.qid) {
                payload.question_id = this.qid;
            } else {
                payload.question = this.qtext;
            }
            if (this.testSlug) {
                payload.test_slug = this.testSlug;
            }
            fetch('<?php echo e(route('question.hint')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(d => {
                    if (d.chatgpt) {
                        d.chatgpt = d.chatgpt.replace(/\{a\d+\}/g, '\n$&');
                    }
                    if (d.gemini) {
                        d.gemini = d.gemini.replace(/\{a\d+\}/g, '\n$&');
                    }
                    this.hints = d;
                })
                .catch(e => console.error(e));
        }
    }"
>
<label class="text-base" style="white-space:normal"><?php echo $finalQuestion; ?></label>
    <button type="button" class="text-xs text-blue-600 underline ml-1" @click="fetchHints()">Help</button>
    <?php if($showQuestionEdit): ?>
        <button type="button" class="text-xs text-blue-600 underline ml-1" data-question="<?php echo e(e($question->question)); ?>" onclick="editQuestion(<?php echo e($question->id); ?>, this)">Edit question</button>
    <?php endif; ?>
    <template x-if="hints.chatgpt || hints.gemini">
        <div class="text-sm text-gray-600 mt-1">
            <p><strong>ChatGPT:</strong> <span class="whitespace-pre-line" x-text="hints.chatgpt"></span></p>
            <template x-if="hints.gemini">
                <p><strong>Gemini:</strong> <span class="whitespace-pre-line" x-text="hints.gemini"></span></p>
            </template>
            <button type="button" class="text-xs text-blue-600 underline" @click="fetchHints(true)">Refresh</button>
        </div>
    </template>
</div>

<?php if (! $__env->hasRenderedOnce('bc653ea0-0462-4fda-9749-8a8fa85e0a49')): $__env->markAsRenderedOnce('bc653ea0-0462-4fda-9749-8a8fa85e0a49'); ?>
    <script>
        const verbHintBaseUrl = '<?php echo e(url('/verb-hints')); ?>';
        const questionBaseUrl = '<?php echo e(url('/questions')); ?>';
        const verbHintCsrf = '<?php echo e(csrf_token()); ?>';
        function editVerbHint(id, btn) {
            const span = btn.closest('.verb-hint');
            const current = span.querySelector('.verb-hint-text').textContent;
            const hint = prompt('Edit verb hint', current);
            if (hint === null) return;
            fetch(`${verbHintBaseUrl}/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': verbHintCsrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ hint })
            }).then(() => {
                span.querySelector('.verb-hint-text').textContent = hint;
            });
        }
        function deleteVerbHint(id, btn, qid, marker) {
            if (!confirm('Delete verb hint?')) return;
            fetch(`${verbHintBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': verbHintCsrf,
                    'Accept': 'application/json'
                }
            }).then(() => {
                const addBtn = document.createElement('button');
                addBtn.type = 'button';
                addBtn.className = 'text-xs text-blue-600 underline';
                addBtn.textContent = 'Add hint';
                addBtn.onclick = function(){ addVerbHint(qid, marker, addBtn); };
                btn.closest('.verb-hint').replaceWith(addBtn);
            });
        }
        function addVerbHint(qid, marker, btn) {
            const hint = prompt('Enter verb hint');
            if (!hint) return;
            fetch(verbHintBaseUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': verbHintCsrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ question_id: qid, marker, hint })
            }).then(r => r.json()).then(data => {
                const span = document.createElement('span');
                span.className = 'verb-hint text-red-700 text-xs font-bold';
                span.innerHTML = '(<span class="verb-hint-text"></span>) <button type="button" class="verb-hint-edit" aria-label="Edit hint"><i class="fa-solid fa-pen"></i></button> <button type="button" class="verb-hint-delete text-red-600" aria-label="Delete hint"><i class="fa-solid fa-trash"></i></button>';
                span.querySelector('.verb-hint-text').textContent = hint;
                span.querySelector('.verb-hint-edit').onclick = function(){ editVerbHint(data.id, this); };
                span.querySelector('.verb-hint-delete').onclick = function(){ deleteVerbHint(data.id, this, qid, marker); };
                btn.replaceWith(span);
            });
        }
        function editQuestion(id, btn) {
            const current = btn.dataset.question || '';
            const text = prompt('Edit question', current);
            if (text === null) return;
            fetch(`${questionBaseUrl}/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': verbHintCsrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ question: text })
            }).then(() => {
                btn.dataset.question = text;
                location.reload();
            });
        }
        function storageKey(name) {
            return `answer:${location.pathname}:${name}`;
        }
        function restoreSavedAnswers() {
            document.querySelectorAll('input[name], select[name], textarea[name]').forEach(el => {
                const key = storageKey(el.name);
                const saved = localStorage.getItem(key);
                if (saved === null) return;
                if (el.type === 'checkbox') {
                    el.checked = saved === 'true';
                } else if (el.type === 'radio') {
                    if (el.value === saved) el.checked = true;
                } else {
                    el.value = saved;
                }
            });
        }
        document.addEventListener('input', e => {
            const el = e.target;
            if (!el.name) return;
            const key = storageKey(el.name);
            if (el.type === 'checkbox') {
                localStorage.setItem(key, el.checked);
            } else if (el.type === 'radio') {
                if (el.checked) localStorage.setItem(key, el.value);
            } else {
                localStorage.setItem(key, el.value);
            }
        });

        function clearSavedAnswers() {
            Object.keys(localStorage)
                .filter(k => k.startsWith(`answer:${location.pathname}:`))
                .forEach(k => localStorage.removeItem(k));
        }

        window.addEventListener('DOMContentLoaded', () => {
            restoreSavedAnswers();
            document.querySelectorAll('form').forEach(f => {
                f.addEventListener('submit', clearSavedAnswers);
            });
            document.querySelectorAll('a[href*="nav=next"], a[href*="nav=prev"]').forEach(a => {
                a.addEventListener('click', clearSavedAnswers);
            });
        });
    </script>
<?php endif; ?>
<?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/components/question-input.blade.php ENDPATH**/ ?>