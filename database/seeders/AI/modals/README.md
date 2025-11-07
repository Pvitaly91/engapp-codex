# Modal Verbs Comprehensive Subthemes Seeder

## Overview
This seeder generates comprehensive modal verb questions across all CEFR levels (A1-C2) for six modal verb subthemes.

## Subthemes
1. **Can / Could** - Ability, Permission, Possibility
2. **May / Might** - Permission, Possibility, Uncertainty  
3. **Must / Have to** - Obligation, Necessity, Deduction
4. **Should / Ought to** - Advice, Recommendation, Expectation
5. **Will / Would** - Future, Conditional, Willingness
6. **Need / Needn't** - Necessity, Lack of Necessity

## Question Distribution
- **Total Questions**: ~360
- **Per Subtheme**: ~60 questions
- **Per Level**: 10 questions (A1, A2, B1, B2, C1, C2)

## Features

### Question Structure
- **Single-gap questions** for A1 level
- **Multi-gap questions** (2-3 gaps) for levels A2-C2
- **No duplicate answers** in multi-gap questions
- **Type**: 0 (fill-in-the-blank)
- **Flag**: 2 (AI-generated)

### Hints and Explanations
Each question includes:

1. **Question Hint (`question_hints`)**:
   - Context description without revealing the answer
   - Formula/pattern explanation
   - Usage guidelines  
   - Example sentence with modal verb placeholder

2. **ChatGPT Explanation (`chatgpt_explanations`)**:
   - Correct answer explanation with context
   - Wrong answer explanations for each distractor
   - Examples showing usage
   - Does NOT contain the correct answer directly

3. **Verb Hint (`verb_hint`)**:
   - Base form or leading hint
   - Does NOT contain the correct answer
   - Example: "modal для можливості" instead of "may/might"

### Tags
Each question is tagged with:
- **Modal Verbs** (general grammar theme)
- **Specific modal pair** (Can/Could, May/Might, etc.)

## Usage

Run the seeder with:
```bash
php artisan db:seed --class="Database\\Seeders\\AI\\modals\\ModalVerbsComprehensiveSubthemesAiSeeder"
```

## Example Questions

### A1 Level (Single Gap)
```
I {a1} swim very well.
Options: can, could, must, should
Answer: can
```

### A2 Level (Multi-Gap)
```
Yesterday I {a1} finish my homework and {a2} go to bed.
Options: could, can, must, should
Answers: {a1: could, a2: could} (no duplicates enforced)
```

### C2 Level (Advanced Multi-Gap)
```
The paradigm shift {a1} potentially undermine assumptions that {a2} underpinned the discipline for decades.
```

## Implementation Details

- Extends `QuestionSeeder` base class
- Uses `QuestionSeedingService` for database operations
- Implements comprehensive hint and explanation generation
- Ensures grammatical correctness across all levels
- Follows PSR-4 autoloading standards

## Notes
- Questions increase in complexity from A1 to C2
- Multi-gap questions have different correct answers to prevent repetition
- All explanations are in Ukrainian (uk locale)
- Compatible with existing question bank structure
