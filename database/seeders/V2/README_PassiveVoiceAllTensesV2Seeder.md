# Passive Voice All Tenses V2 Seeder

## Overview
This seeder creates test questions for practicing passive voice in all tenses (B1-B2 level).

## Source
Based on: https://test-english.com/grammar-points/b1-b2/passive-voice-all-tenses/

## Structure

### Pages
- **Page 1**: 10 multiple-choice questions covering various passive voice tenses
- **Page 2**: 10 multiple-choice questions with more complex passive voice structures
- **Page 3**: 10 fill-in-the-blank questions with verb hints

### Question Format
Each question includes:
- Question text with `{a1}` markers for blanks
- Multiple answer options (3-4 options per question)
- Correct answer
- Verb hint (e.g., "test", "tell", "examine")
- Ukrainian hints (підказки) - 2 hints per question explaining grammar rules
- Detailed Ukrainian explanations for each option with ✅ (correct) or ❌ (incorrect) indicators

### Categories and Tags
- **Category**: Passive Voice
- **Tags**:
  - Passive Voice Practice (theme)
  - Passive Voice All Tenses (detail)
  - Passive Construction (structure)
  - Mixed Tenses (focus)

### Difficulty Levels
- **B1**: Questions 1, 3, 7, 8, 11, 14-15, 21-24, 28-29
- **B2**: Questions 2, 4-6, 9-10, 12-13, 16-17, 20, 25-27

## Running the Seeder

```bash
php artisan db:seed --class=Database\\Seeders\\V2\\PassiveVoiceAllTensesV2Seeder
```

## Grammar Topics Covered

1. **Past Continuous Passive** - was/were being + past participle
2. **Modal Perfect Passive** - modal + have been + past participle
3. **Present Perfect Passive** - has/have been + past participle
4. **Past Perfect Passive** - had been + past participle
5. **Conditional Passive** - would/wouldn't be + past participle
6. **Gerund Passive** - being + past participle
7. **Future Passive (going to)** - is/are going to be + past participle
8. **Present Continuous Passive** - is/are being + past participle
9. **Infinitive Passive** - to be + past participle
10. **Future Simple Passive** - will be + past participle
11. **Third Conditional Passive** - would/wouldn't have been + past participle

## Example Questions

### Multiple Choice (Pages 1-2)
```
The new chemical {a1} when it exploded.
Options: was being tested, had being tested, was testing
Answer: was being tested
```

### Fill-in-the-Blank with Verb Hint (Page 3)
```
I don't like {a1} what to do. (tell)
Options: being told, to be told, been told, be told
Answer: being told
```

## Notes
- All explanations are in Ukrainian to help Ukrainian learners
- Each option explanation includes the grammar rule and example usage
- Verb hints provide the base form of the verb to help students understand the transformation
- Some questions test understanding of when NOT to use passive voice (e.g., "disappear", "provide")
