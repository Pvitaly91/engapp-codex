<div class="grid gap-3 md:grid-cols-3 mb-3">
    @foreach($rows as $row)
        <div class="m44-form-point" data-m44-form-row="{{ $row['point'] }}">
            @php
                $rowBlock = (object) ['id' => $block->id.'-row-'.$loop->index, 'level' => $block->level ?? null, 'uuid' => $block->uuid];
                $rowExamples = view('engram.theory.blocks-v3.m44-compact-form-examples', ['formExamples' => [$row['example']]])->render();
            @endphp
            @include('engram.theory.blocks-v3.forms-grid', ['block' => $rowBlock, 'data' => ['items' => [[
                'label' => $row['label'], 'title' => $row['formula'], 'native_description' => $rowExamples,
            ]]], 'embeddedDetail' => true, 'm44NativePiece' => true, 'm44ReferencePiece' => true, 'lessonLinks' => [], 'pointSections' => []])
        </div>
    @endforeach
</div>
