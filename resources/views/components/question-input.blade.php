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
        if($verbHint){
            $input .= ' <span class="text-red-700 text-xs font-bold">('.e($verbHint).')';
            if(!empty($showVerbHintEdit) && $verbHintRow){
                $url = route('verb-hints.edit', ['verbHint' => $verbHintRow->id, 'from' => request()->getRequestUri()]);
                $input .= ' <a href="'.$url.'" class="underline">Edit</a>';
            }
            $input .= '</span>';
        }
        $replacements[$marker] = $input;
    }
    $finalQuestion = strtr(e($questionText), $replacements);
@endphp
<div x-data="{hints:{chatgpt:'',gemini:''},fetchHints(refresh=false){fetch('{{ route('question.hint') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({question_id:{{ $question->id }},refresh})}).then(r=>r.json()).then(d=>this.hints=d);}}">
    <label class="text-base" style="white-space:normal">{!! $finalQuestion !!}</label>
    <button type="button" class="text-xs text-blue-600 underline ml-1" @click="fetchHints()">Help</button>
    <template x-if="hints.chatgpt || hints.gemini">
        <div class="text-sm text-gray-600 mt-1">
            <p><strong>ChatGPT:</strong> <span x-text="hints.chatgpt"></span></p>
            <p><strong>Gemini:</strong> <span x-text="hints.gemini"></span></p>
            <button type="button" class="text-xs text-blue-600 underline" @click="fetchHints(true)">Refresh</button>
        </div>
    </template>
</div>
