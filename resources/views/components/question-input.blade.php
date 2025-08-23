@props([
    'question',
    'inputNamePrefix',
    'arrayInput' => false,
    'manualInput' => false,
    'autocompleteInput' => false,
    'builderInput' => false,
    'autocompleteRoute' => url('/api/search?lang=en'),
    'methodMap' => [],
    'showVerbHintEdit' => false,
])
@php
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
            $input .= ' <span class="text-red-700 text-xs font-bold">('.e($verbHint).')';
            if(!empty($showVerbHintEdit)){
                $editUrl = route('verb-hints.edit', ['verbHint' => $verbHintRow->id, 'from' => request()->getRequestUri()]);
                $deleteId = 'delete-verb-hint-'.$verbHintRow->id;
                $deleteUrl = route('verb-hints.destroy', $verbHintRow->id);
                $input .= ' <a href="'.$editUrl.'" class="underline">Edit</a>';
                $input .= ' <button type="submit" form="'.$deleteId.'" class="underline text-red-600" onclick="return confirm(\'Delete verb hint?\')">Delete</button>';
                $input .= '<form id="'.$deleteId.'" action="'.$deleteUrl.'" method="POST" class="hidden">'.csrf_field().method_field('DELETE').'<input type="hidden" name="from" value="'.e(request()->getRequestUri()).'"></form>';
            }
            $input .= '</span>';
        } elseif(!empty($showVerbHintEdit)) {
            $createUrl = route('verb-hints.create', ['question_id' => $question->id, 'marker' => $markerKey, 'from' => request()->getRequestUri()]);
            $input .= ' <a href="'.$createUrl.'" class="text-xs text-blue-600 underline">Add hint</a>';
        }
        $replacements[$marker] = $input;
    }
    $finalQuestion = strtr(e($questionText), $replacements);

@endphp

<div
    x-data='{
        qid: {{ $question?->id ?? 'null' }},
        qtext: @json($question->question),
        hints: { chatgpt: "", gemini: "" },
        async fetchHints(refresh = false) {
            if (!this.qid && !this.qtext) return; // немає даних питання
            const payload = { refresh };
            if (this.qid) {
                payload.question_id = this.qid;
            } else {
                payload.question = this.qtext;
            }
            try {
                const r = await fetch("{{ route('question.hint') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload)
                });
                const d = await r.json();
                this.hints = d;
            } catch (e) {
                console.error(e);
            }
        }
    }'
><label class="text-base" style="white-space:normal">{!! $finalQuestion !!}</label>
    <button type="button" class="text-xs text-blue-600 underline ml-1" @click="fetchHints()">Help</button>
    <template x-if="hints.chatgpt || hints.gemini">
        <div class="text-sm text-gray-600 mt-1">
            <p><strong>ChatGPT:</strong> <span x-text="hints.chatgpt"></span></p>
            <p><strong>Gemini:</strong> <span x-text="hints.gemini"></span></p>
            <button type="button" class="text-xs text-blue-600 underline" @click="fetchHints(true)">Refresh</button>
        </div>
    </template>
</div>
