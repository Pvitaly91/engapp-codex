@once
<script>window.GRAMLYZE_CONTRACTION_RULES = @json(\App\Support\AcceptedAnswerVariants::rules());</script>
<script src="{{ asset('js/english-answer-variants.js') }}?v={{ filemtime(public_path('js/english-answer-variants.js')) }}"></script>
@endonce
