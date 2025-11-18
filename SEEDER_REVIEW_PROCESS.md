# Seeder Review and Correction Process

## Overview
This document outlines the process for reviewing and correcting seeders in the `database/seeders` directory.

## Task Requirements
1. Review all seeders for incorrect answers, descriptions, and hints
2. Add tag "неправильна відповідь -> правильна відповідь" (incorrect answer -> correct answer) to fixed questions  
3. Add "fixed" tag to all corrected questions

## Repository Statistics
- Total seeder files: 181
- Files containing questions: 110
- Total questions: ~2,694
- Seeder types: V1 (legacy), V2 (parts-based), Ai (AI-generated), Pages (educational content)

## Seeder Structures

### Standard Structure (V1)
```php
$data = [
    [
        'question' => 'Bob always {a1} tea in the morning.',
        'difficulty' => 1,
        'source_id' => $sourceId,
        'flag' => 0,
        'answers' => [
            [
                'marker' => 'a1',
                'answer' => 'drinks',
                'verb_hint' => 'drink',
            ]
        ],
        'options' => ['drink', 'drinks'],
    ],
];
```

### Parts-Based Structure (V2)
```php
$entries = [
    [
        'question' => 'If Tom {a1} finish his chores, he won\'t get dessert.',
        'level' => 'A1',
        'source' => 'exercise1',
        'parts' => [
            'a1' => [
                'pattern' => 'negative_present',
                'answers' => ["doesn't"],
                'options' => ["doesn't", "don't", "didn't"],
            ]
        ],
    ],
];
```

## Review Process

### Step 1: Identify Issues
Review questions for:
- Grammatical correctness of the question text
- Correctness of the provided answer(s)
- Appropriateness of verb hints
- Correctness of options (all must be grammatically valid alternatives)

### Step 2: Fix Issues
When an incorrect answer is found:

1. **Document the error**:
   - Note the file path
   - Note the question text
   - Note the incorrect answer
   - Note the correct answer

2. **Update the seeder**:
   ```php
   // BEFORE (incorrect)
   'answers' => [['marker' => 'a1', 'answer' => 'go']],  // Wrong for "He"
   
   // AFTER (correct)
   'answers' => [['marker' => 'a1', 'answer' => 'goes']],
   ```

3. **Add correction tags**:
   ```php
   // Create or get tags
   $fixedTag = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Review Status']);
   $correctionTag = Tag::firstOrCreate(
       ['name' => 'go -> goes'],  // incorrect -> correct
       ['category' => 'Answer Corrections']
   );
   
   // Add to question's tag_ids array
   $d['tag_ids'] = array_merge(
       $d['tag_ids'] ?? [],
       [$fixedTag->id, $correctionTag->id]
   );
   ```

### Step 3: Test Changes
After making corrections:
1. Run the seeder to ensure no syntax errors
2. Verify the question displays correctly in the application
3. Verify all tags are properly associated

## Example Correction

### Before:
```php
[
    'question' => 'She {a1} to the store yesterday.',
    'answers' => [['marker' => 'a1', 'answer' => 'go', 'verb_hint' => 'go']],
    'options' => ['go', 'goes', 'went'],
    'tag_ids' => [$themeTag->id],
],
```

### After:
```php
[
    'question' => 'She {a1} to the store yesterday.',
    'answers' => [['marker' => 'a1', 'answer' => 'went', 'verb_hint' => 'go']],  // Fixed: go -> went
    'options' => ['go', 'goes', 'went'],
    'tag_ids' => [
        $themeTag->id,
        Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Review Status'])->id,
        Tag::firstOrCreate(['name' => 'go -> went'], ['category' => 'Answer Corrections'])->id,
    ],
],
```

## Validation Script

A validation script (`/tmp/seeder_validator.php`) has been created to help identify potential issues:
- Missing answer definitions
- Marker mismatches
- Common typo patterns

## Common Grammar Rules to Check

### Present Simple
- I/You/We/They + base verb
- He/She/It + verb+s/es

### Past Simple  
- All subjects + past form (regular: verb+ed, irregular: special forms)

### Present Continuous
- am/is/are + verb+ing

### Present Perfect
- have/has + past participle (V3)

### Modal Verbs
- Modal + base verb (can go, must do, should be, etc.)

## Files Requiring Attention

Based on validation analysis, the following files may need review:
- All V2/modals/* seeders (different structure, may have parsing issues)
- AI-generated seeders in Ai/* directory
- Recently modified seeders

## Next Steps

1. Get specific examples of known errors
2. Prioritize review based on:
   - Recently modified files
   - Files with flag=1 (possibly indicating issues)
   - AI-generated content (higher likelihood of errors)
3. Systematically review and correct
4. Document all changes with proper tagging

## Questions for Clarification

1. Are there specific seeders known to have errors?
2. What types of errors are most common?
3. Should all ~2,694 questions be manually reviewed?
4. Is there a reference source for validating answers?
