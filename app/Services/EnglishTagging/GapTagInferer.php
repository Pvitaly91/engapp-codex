<?php

namespace App\Services\EnglishTagging;

/**
 * GapTagInferer - Infers grammar tags for English questions based on gap markers.
 *
 * This service determines grammatical tags only by analyzing:
 * - Context around the marker in questionText
 * - The correct answer
 * - Available options
 * - verb_hint as a weak signal (fallback only)
 *
 * Returns an array of tag names (slugs), not IDs. Maximum 1-3 tags per marker.
 * Only grammar-related tags are returned, no lexical/thematic tags.
 */
class GapTagInferer
{
    /**
     * Context window size (words before and after marker).
     */
    private const WINDOW_SIZE = 5;

    /**
     * Maximum number of tags to return per marker.
     */
    private const MAX_TAGS = 3;

    /**
     * Patterns that indicate indirect/embedded questions.
     */
    private const INDIRECT_QUESTION_PATTERNS = [
        '/can you tell me\b/i',
        '/could you tell me\b/i',
        '/do you know\b/i',
        '/i wonder\b/i',
        '/i\'m not sure\b/i',
        '/i\'m uncertain\b/i',
        '/do you remember\b/i',
        '/can you explain\b/i',
        '/could you clarify\b/i',
        '/we need to determine\b/i',
        '/the question remains\b/i',
        '/one might wonder\b/i',
        '/one might inquire\b/i',
        '/i\'d like to understand\b/i',
    ];

    /**
     * Patterns for embedded wh-clauses (statement word order after wh-word).
     */
    private const EMBEDDED_WH_PATTERNS = [
        '/\bwhere\s+\w+\s+is\b/i',
        '/\bwhat\s+\w+\s+is\b/i',
        '/\bwhen\s+\w+\s+(was|is|did)\b/i',
        '/\bhow\s+\w+\s+(is|was|works?)\b/i',
        '/\bwhether\s+\w+\s+(will|is|was|has|have|had)\b/i',
        '/\bif\s+\w+\s+(is|was|will|has|have|had)\b/i',
    ];

    /**
     * Negative tag endings pattern (e.g., "isn't he", "don't you").
     */
    private const TAG_NEGATIVE_PATTERN = '/^(isn\'t|aren\'t|wasn\'t|weren\'t|don\'t|doesn\'t|didn\'t|haven\'t|hasn\'t|hadn\'t|won\'t|wouldn\'t|can\'t|couldn\'t|shouldn\'t|mustn\'t|shan\'t)\s+(i|you|he|she|it|we|they)$/i';

    /**
     * Positive tag endings pattern (e.g., "is he", "do you").
     */
    private const TAG_POSITIVE_PATTERN = '/^(is|are|was|were|do|does|did|have|has|had|will|would|can|could|should|must|shall)\s+(i|you|he|she|it|we|they)$/i';

    /**
     * Ought tag pattern.
     */
    private const TAG_OUGHT_PATTERN = '/^(ought|oughtn\'t)\s+(i|you|he|she|it|we|they)(\s+not)?$/i';

    /**
     * Common irregular past tense verbs.
     */
    private const IRREGULAR_PAST_VERBS = [
        'wrote', 'broke', 'made', 'gave', 'took', 'went', 'came', 'saw', 'knew',
        'thought', 'found', 'said', 'got', 'put', 'told', 'left', 'felt', 'became',
        'brought', 'began', 'kept', 'held', 'stood', 'heard', 'let', 'meant', 'set',
        'met', 'paid', 'sat', 'spoke', 'led', 'read', 'ran', 'built', 'sent', 'spent',
        'understood', 'caught', 'drawn', 'shown', 'chosen', 'grown', 'thrown', 'worn',
        'torn', 'born', 'sworn', 'withdrawn', 'happened', 'caused', 'suggested', 'called',
    ];

    /**
     * Auxiliary forms that indicate NOT a subject question.
     */
    private const AUXILIARY_FORMS = [
        'am', 'is', 'are', 'was', 'were',                                      // be forms
        'do', 'does', 'did',                                                    // do forms
        'have', 'has', 'had',                                                   // have forms
        'will', 'would', 'can', 'could', 'should', 'must', 'may', 'might',     // modals
    ];

    /**
     * Infer grammatical tags for a gap marker in a question.
     *
     * @param  string  $questionText  The full question text with markers like {a1}, {a2}
     * @param  string  $marker  The marker identifier (e.g., "a1", "a2")
     * @param  string  $correctAnswer  The correct answer for this marker
     * @param  array  $flatOptions  All available options for this marker
     * @param  string|null  $verbHint  Optional verb hint (weak fallback signal)
     * @return array Array of tag names (strings, not IDs)
     */
    public function infer(
        string $questionText,
        string $marker,
        string $correctAnswer,
        array $flatOptions = [],
        ?string $verbHint = null
    ): array {
        $tags = [];

        // Normalize inputs
        $normalizedQuestion = $this->normalizeText($questionText);
        $normalizedAnswer = $this->normalizeText($correctAnswer);
        $normalizedOptions = $this->normalizeOptions($flatOptions);

        // Get context window around marker
        $window = $this->getMarkerWindow($normalizedQuestion, $marker);

        // Priority 1: Structural patterns (highest priority)
        $structuralTags = $this->detectStructuralPatterns(
            $normalizedQuestion,
            $marker,
            $normalizedAnswer,
            $normalizedOptions,
            $window
        );

        if (! empty($structuralTags)) {
            // If structural patterns detected, return them (1-3 tags max)
            return array_slice($structuralTags, 0, self::MAX_TAGS);
        }

        // Priority 2: Auxiliary/Tense patterns
        $auxTenseTags = $this->detectAuxiliaryTensePatterns(
            $normalizedAnswer,
            $normalizedOptions,
            $window,
            $verbHint
        );

        $tags = array_merge($tags, $auxTenseTags);

        // Limit to MAX_TAGS
        return array_slice(array_unique($tags), 0, self::MAX_TAGS);
    }

    /**
     * Normalize text to lowercase and trim.
     */
    private function normalizeText(string $text): string
    {
        return strtolower(trim($text));
    }

    /**
     * Normalize and flatten options array.
     */
    private function normalizeOptions(array $options): array
    {
        $flat = [];
        foreach ($options as $option) {
            if (is_array($option)) {
                foreach ($option as $value) {
                    if (is_string($value)) {
                        $flat[] = $this->normalizeText($value);
                    }
                }
            } elseif (is_string($option)) {
                $flat[] = $this->normalizeText($option);
            }
        }

        return array_unique($flat);
    }

    /**
     * Get context window around the marker (words before and after).
     *
     * @param  string  $text  Normalized question text
     * @param  string  $marker  Marker identifier (e.g., "a1")
     * @return array ['before' => string, 'after' => string, 'full' => string]
     */
    private function getMarkerWindow(string $text, string $marker): array
    {
        $markerPattern = '{'.$marker.'}';
        $pos = strpos($text, $markerPattern);

        if ($pos === false) {
            return ['before' => '', 'after' => '', 'full' => $text];
        }

        // Get text before marker
        $beforeText = substr($text, 0, $pos);
        $beforeWords = preg_split('/\s+/', trim($beforeText), -1, PREG_SPLIT_NO_EMPTY);
        $beforeWindow = implode(' ', array_slice($beforeWords, -self::WINDOW_SIZE));

        // Get text after marker
        $afterText = substr($text, $pos + strlen($markerPattern));
        $afterWords = preg_split('/\s+/', trim($afterText), -1, PREG_SPLIT_NO_EMPTY);
        $afterWindow = implode(' ', array_slice($afterWords, 0, self::WINDOW_SIZE));

        return [
            'before' => $beforeWindow,
            'after' => $afterWindow,
            'full' => $text,
        ];
    }

    /**
     * Detect high-priority structural patterns.
     *
     * Priority order:
     * 1. Indirect/Embedded question word order
     * 2. Tag questions polarity
     * 3. Subject questions (no auxiliary)
     */
    private function detectStructuralPatterns(
        string $question,
        string $marker,
        string $answer,
        array $options,
        array $window
    ): array {
        // 1. Check for Indirect/Embedded question word order
        $indirectTags = $this->detectIndirectEmbeddedQuestions($question, $window);
        if (! empty($indirectTags)) {
            return $indirectTags;
        }

        // 2. Check for Tag questions polarity
        $tagQuestionTags = $this->detectTagQuestions($question, $marker, $answer, $options, $window);
        if (! empty($tagQuestionTags)) {
            return $tagQuestionTags;
        }

        // 3. Check for Subject questions (no auxiliary)
        $subjectQuestionTags = $this->detectSubjectQuestions($question, $answer, $options, $window);
        if (! empty($subjectQuestionTags)) {
            return $subjectQuestionTags;
        }

        return [];
    }

    /**
     * Detect Indirect/Embedded question patterns.
     *
     * Patterns:
     * - "Can you tell me...", "Do you know...", "I wonder...", "Could you tell me..."
     * - Embedded wh-clauses (where the station is vs is the station)
     */
    private function detectIndirectEmbeddedQuestions(string $question, array $window): array
    {
        // Check indirect question intro patterns
        foreach (self::INDIRECT_QUESTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $question)) {
                return ['Indirect Questions', 'Question Word Order'];
            }
        }

        // Check for embedded wh-clause patterns (after wh-word, statement order is expected)
        foreach (self::EMBEDDED_WH_PATTERNS as $pattern) {
            if (preg_match($pattern, $question)) {
                return ['Indirect Questions', 'Embedded Questions'];
            }
        }

        return [];
    }

    /**
     * Detect Tag question patterns.
     *
     * - Marker at the end with comma before: ", {a1}?"
     * - Options look like tag endings (don't you, isn't it, etc.)
     */
    private function detectTagQuestions(
        string $question,
        string $marker,
        string $answer,
        array $options,
        array $window
    ): array {
        // Check if marker is at the end with comma pattern: ", {a1}?"
        $markerPattern = '{'.$marker.'}';
        $tagEndPattern = '/,\s*'.preg_quote($markerPattern, '/').'\s*\??$/';

        if (preg_match($tagEndPattern, $question)) {
            return ['Tag Questions', 'Question Tags'];
        }

        // Use constants for tag patterns
        $tagPatterns = [
            self::TAG_NEGATIVE_PATTERN,
            self::TAG_POSITIVE_PATTERN,
            self::TAG_OUGHT_PATTERN,
        ];

        $tagLikeOptions = 0;
        foreach ($options as $opt) {
            foreach ($tagPatterns as $pattern) {
                if (preg_match($pattern, $opt)) {
                    $tagLikeOptions++;
                    break;
                }
            }
        }

        // If majority of options look like tag endings
        if ($tagLikeOptions >= 2 || ($tagLikeOptions > 0 && count($options) <= 4 && $tagLikeOptions >= count($options) / 2)) {
            return ['Tag Questions', 'Question Tags'];
        }

        // Check answer itself
        foreach ($tagPatterns as $pattern) {
            if (preg_match($pattern, $answer)) {
                return ['Tag Questions', 'Question Tags'];
            }
        }

        return [];
    }

    /**
     * Detect Subject questions (no auxiliary).
     *
     * - "Who/What {a1} ... ?" structure
     * - The answer is a VERB FORM (not an auxiliary like is/are/do/does/did)
     * - Options might contain do/does/did but correct answer is the verb itself
     */
    private function detectSubjectQuestions(
        string $question,
        string $answer,
        array $options,
        array $window
    ): array {
        // Check for "Who/What {marker}" pattern at the beginning
        $subjectQuestionPattern = '/^(who|what)\s+\{a\d+\}/i';

        if (preg_match($subjectQuestionPattern, $question)) {
            // Subject questions have a VERB as the answer, not an auxiliary
            // If answer is an auxiliary, then it's NOT a subject question
            if (in_array($answer, self::AUXILIARY_FORMS)) {
                return [];
            }

            // Check if answer looks like a verb form (past simple or present simple)
            // Regular past (-ed), irregular past (constant), or 3rd person present (-s)
            $isVerbForm = preg_match('/^[a-z]+ed$/i', $answer)    // regular past: worked, played
                       || in_array($answer, self::IRREGULAR_PAST_VERBS)  // irregular past
                       || preg_match('/^[a-z]+s$/i', $answer);    // 3rd person present: works, plays

            // Only return subject question if the answer is a verb form (not auxiliary)
            if ($isVerbForm) {
                return ['Subject Questions', 'Question Formation'];
            }
        }

        return [];
    }

    /**
     * Detect auxiliary/tense patterns.
     *
     * Strategy: First check the ANSWER to determine the pattern type definitively.
     * Only fall back to option counting when the answer doesn't match any known pattern.
     *
     * Priority:
     * 1. Do/Does/Did (+ Present Simple / Past Simple)
     * 2. Be auxiliary / To be
     * 3. Have auxiliary (perfect patterns)
     * 4. Modal verbs (will/would/can/could/should/must/may/might)
     */
    private function detectAuxiliaryTensePatterns(
        string $answer,
        array $options,
        array $window,
        ?string $verbHint
    ): array {
        // FIRST: Check answer directly to determine the correct pattern type
        // This prevents options from incorrectly influencing the detection

        // Check if answer is a modal verb (check this early since modals are distinct)
        $modalTags = $this->detectModalFromAnswer($answer, $window);
        if (! empty($modalTags)) {
            return $modalTags;
        }

        // Check if answer is a have form (perfect patterns)
        $haveTags = $this->detectHaveFromAnswer($answer, $window);
        if (! empty($haveTags)) {
            return $haveTags;
        }

        // Check if answer is a be form
        $beTags = $this->detectBeFromAnswer($answer, $window);
        if (! empty($beTags)) {
            return $beTags;
        }

        // Check if answer is a do/does/did form
        $doTags = $this->detectDoFromAnswer($answer, $window);
        if (! empty($doTags)) {
            return $doTags;
        }

        // SECOND: If answer didn't match, analyze options pattern
        $optionTags = $this->detectFromOptions($options, $window);
        if (! empty($optionTags)) {
            return $optionTags;
        }

        // FALLBACK: Use verb_hint if nothing else matched
        if ($verbHint !== null) {
            $fallbackTags = $this->detectFromVerbHint($verbHint);
            if (! empty($fallbackTags)) {
                return $fallbackTags;
            }
        }

        return [];
    }

    /**
     * Detect modal verb from answer.
     */
    private function detectModalFromAnswer(string $answer, array $window): array
    {
        $modalGroups = [
            'will_would' => ['will', 'would', "won't", "wouldn't"],
            'can_could' => ['can', 'could', "can't", "couldn't"],
            'should' => ['should', "shouldn't"],
            'must' => ['must', "mustn't"],
            'may_might' => ['may', 'might'],
            'ought' => ['ought'],
        ];

        foreach ($modalGroups as $group => $modals) {
            if (in_array($answer, $modals)) {
                switch ($group) {
                    case 'will_would':
                        if (in_array($answer, ['would', "wouldn't"])) {
                            return ['Modal Verbs', 'Will/Would'];
                        }

                        return ['Modal Verbs', 'Will/Would', 'Future Simple'];
                    case 'can_could':
                        return ['Modal Verbs', 'Can/Could'];
                    case 'should':
                        return ['Modal Verbs', 'Should'];
                    case 'must':
                        return ['Modal Verbs', 'Must'];
                    case 'may_might':
                        return ['Modal Verbs', 'May/Might'];
                    case 'ought':
                        return ['Modal Verbs'];
                }
            }
        }

        return [];
    }

    /**
     * Detect have auxiliary from answer.
     */
    private function detectHaveFromAnswer(string $answer, array $window): array
    {
        $haveForms = ['have', 'has', 'had', "haven't", "hasn't", "hadn't"];

        if (in_array($answer, $haveForms)) {
            // Check for perfect patterns (been, V3 forms in context)
            if (preg_match('/\bbeen\b/', $window['after'])) {
                if (in_array($answer, ['had', "hadn't"])) {
                    return ['Have/Has/Had', 'Past Perfect'];
                }

                return ['Have/Has/Had', 'Present Perfect'];
            }

            // Check for ever/never/just/already patterns (common with perfect)
            if (preg_match('/\b(ever|never|just|already|yet)\b/i', $window['full'])) {
                if (in_array($answer, ['had', "hadn't"])) {
                    return ['Have/Has/Had', 'Past Perfect'];
                }

                return ['Have/Has/Had', 'Present Perfect'];
            }

            // Default based on have form
            if (in_array($answer, ['had', "hadn't"])) {
                return ['Have/Has/Had', 'Past Perfect'];
            }

            return ['Have/Has/Had', 'Present Perfect'];
        }

        return [];
    }

    /**
     * Detect be auxiliary from answer.
     */
    private function detectBeFromAnswer(string $answer, array $window): array
    {
        $beForms = ['am', 'is', 'are', 'was', 'were', 'be', 'been', 'being'];
        $beNegForms = ["isn't", "aren't", "wasn't", "weren't", "am not"];

        $allBeForms = array_merge($beForms, $beNegForms);

        if (in_array($answer, $allBeForms)) {
            // Check for continuous patterns (-ing in context)
            if (preg_match('/\b\w+ing\b/', $window['after'])) {
                if (in_array($answer, ['was', 'were', "wasn't", "weren't"])) {
                    return ['Be (am/is/are/was/were)', 'Past Continuous'];
                }

                return ['Be (am/is/are/was/were)', 'Present Continuous'];
            }

            // Simple be usage
            if (in_array($answer, ['was', 'were', "wasn't", "weren't"])) {
                return ['Be (am/is/are/was/were)', 'To Be'];
            }

            return ['Be (am/is/are/was/were)', 'To Be'];
        }

        return [];
    }

    /**
     * Detect do/does/did from answer.
     */
    private function detectDoFromAnswer(string $answer, array $window): array
    {
        $doForms = ['do', 'does', 'did', "don't", "doesn't", "didn't"];

        if (in_array($answer, $doForms)) {
            if (in_array($answer, ['did', "didn't"])) {
                return ['Do/Does/Did', 'Past Simple'];
            }

            return ['Do/Does/Did', 'Present Simple'];
        }

        return [];
    }

    /**
     * Detect patterns from options when answer doesn't match any known auxiliary.
     */
    private function detectFromOptions(array $options, array $window): array
    {
        $doForms = ['do', 'does', 'did', "don't", "doesn't", "didn't"];
        $beForms = ['am', 'is', 'are', 'was', 'were', "isn't", "aren't", "wasn't", "weren't"];
        $haveForms = ['have', 'has', 'had', "haven't", "hasn't", "hadn't"];
        $modalForms = ['will', 'would', 'can', 'could', 'should', 'must', 'may', 'might', "won't", "wouldn't", "can't", "couldn't", "shouldn't", "mustn't"];

        $doCnt = 0;
        $beCnt = 0;
        $haveCnt = 0;
        $modalCnt = 0;

        foreach ($options as $opt) {
            if (in_array($opt, $doForms)) {
                $doCnt++;
            }
            if (in_array($opt, $beForms)) {
                $beCnt++;
            }
            if (in_array($opt, $haveForms)) {
                $haveCnt++;
            }
            if (in_array($opt, $modalForms)) {
                $modalCnt++;
            }
        }

        // Return the dominant pattern from options
        $maxCnt = max($doCnt, $beCnt, $haveCnt, $modalCnt);
        if ($maxCnt < 2) {
            return [];
        }

        if ($doCnt === $maxCnt) {
            if (preg_match('/\b(yesterday|last\s+\w+|ago)\b/i', $window['full'])) {
                return ['Do/Does/Did', 'Past Simple'];
            }

            return ['Do/Does/Did', 'Present Simple'];
        }

        if ($beCnt === $maxCnt) {
            return ['Be (am/is/are/was/were)', 'To Be'];
        }

        if ($haveCnt === $maxCnt) {
            return ['Have/Has/Had', 'Present Perfect'];
        }

        if ($modalCnt === $maxCnt) {
            return ['Modal Verbs'];
        }

        return [];
    }

    /**
     * Detect patterns from verb_hint (fallback only).
     */
    private function detectFromVerbHint(?string $verbHint): array
    {
        if ($verbHint === null) {
            return [];
        }

        $hint = strtolower($verbHint);

        // Modal patterns
        if (preg_match('/modal/i', $hint)) {
            return ['Modal Verbs'];
        }

        // Tense patterns
        if (preg_match('/present\s*simple/i', $hint)) {
            return ['Present Simple'];
        }

        if (preg_match('/past\s*simple/i', $hint)) {
            return ['Past Simple'];
        }

        if (preg_match('/present\s*continuous/i', $hint)) {
            return ['Present Continuous'];
        }

        if (preg_match('/past\s*continuous/i', $hint)) {
            return ['Past Continuous'];
        }

        if (preg_match('/present\s*perfect/i', $hint)) {
            return ['Present Perfect'];
        }

        if (preg_match('/past\s*perfect/i', $hint)) {
            return ['Past Perfect'];
        }

        if (preg_match('/future/i', $hint)) {
            return ['Future Simple'];
        }

        // Auxiliary patterns
        if (preg_match('/\bto\s*be\b|\bbe\b/i', $hint)) {
            return ['To Be'];
        }

        if (preg_match('/auxiliary/i', $hint)) {
            return ['Do/Does/Did'];
        }

        return [];
    }
}
