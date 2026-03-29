# Active and Passive Voice V2 Seeder

## Overview
This seeder creates test questions for practicing active and passive voice transformations (A2-B1 level).

## Source
Custom questions based on common active/passive voice patterns from traditional English grammar exercises.

## Structure

### Pages
- **Page 1**: Questions 1-7 covering basic passive voice transformations
- **Page 2**: Questions 8-14 covering passive constructions with "by" and "for"
- **Page 3**: Questions 15-20 covering mixed active/passive voice recognition

### Question Format
Each question includes:
- Question text with `{a1}` markers for blanks
- Multiple answer options (4 options per question)
- Correct answer
- Verb hint (e.g., "solve", "write", "make")
- Ukrainian hints (підказки) - 2-3 hints per question explaining grammar rules
- Detailed Ukrainian explanations for each option with ✅ (correct) or ❌ (incorrect) indicators

### Categories and Tags
- **Category**: Passive Voice
- **Tags**:
  - Passive Voice Practice (theme)
  - Active and Passive Voice (detail)
  - Voice Transformation (structure)
  - Mixed Tenses (focus)

### Difficulty Levels
- **A2**: Questions 1-9, 12-13, 16-20 (basic passive/active recognition)
- **B1**: Questions 10-11, 14-15 (more complex structures and conditional passive)

## Running the Seeder

```bash
php artisan db:seed --class=Database\\Seeders\\V2\\ActivePassiveV2Seeder
```

## Grammar Topics Covered

1. **Past Simple Passive** - was/were + past participle
   - Examples: "was solved", "was written", "was made"

2. **Present Simple Passive** - is/are + past participle
   - Examples: "is solved", "are said"

3. **Future Simple Passive** - will be + past participle
   - Examples: "will be done", "will be bought"

4. **Passive vs Active Voice Recognition**
   - When to use passive (subject receives action)
   - When to use active (subject performs action)

5. **Prepositions in Passive**
   - "by" for the agent (who did the action)
   - "for" for purpose/benefit (less common in these exercises)

6. **Past Participles**
   - Regular: solved, scored
   - Irregular: written (write), built (build), broken (break), made (make), taught (teach)

7. **First Conditional Passive**
   - "will be" + past participle with "if" clauses

## Example Questions

### Past Simple Passive
```
This problem {a1} by your brother yesterday
Options: was solved, will be solved, is solved, solves
Answer: was solved
Hint: Yesterday indicates past time, needs Past Simple Passive
```

### Active vs Passive Recognition
```
Mr Johnson {a1} this book
Options: is translated, translated by, translated, was translated
Answer: translated
Hint: Mr Johnson is the subject performing the action (active voice)
```

### Future Simple Passive
```
This job {a1} by my friend next week
Options: is done, did, will be done, was done
Answer: will be done
Hint: "Next week" indicates future time
```

### Passive Construction with "by"
```
This house was {a1} my grandfather
Options: build for, build by, built for, built by
Answer: built by
Hint: After "was" need past participle "built", and "by" indicates the agent
```

## Common Student Mistakes Addressed

1. **Confusing past simple and past participle**
   - ❌ "was broke" → ✅ "was broken"
   - ❌ "was write" → ✅ "was written"

2. **Using active when passive is needed**
   - ❌ "The jar broke by the maid" → ✅ "The jar was broken by the maid"

3. **Confusing "by" and "for" in passive**
   - ✅ "built by" (agent) vs ❌ "built for" (purpose)

4. **Using passive when active is needed**
   - ❌ "Mr Johnson was translated" → ✅ "Mr Johnson translated"
   - ❌ "The policeman was arrested by" → ✅ "The policeman arrested"

5. **Wrong auxiliary verb forms**
   - ❌ "will being opened" → ✅ "will be opened"
   - ❌ "be teached" → ✅ "be taught"

## Notes
- All explanations are in Ukrainian to help Ukrainian learners
- Each option explanation includes the grammar rule and example usage
- Verb hints provide the base form of the verb to help students understand the transformation
- Questions progressively increase in difficulty from A2 (basic forms) to B1 (conditional passive and complex structures)
- Special attention is given to irregular past participles and common mistakes
- The seeder includes questions that test both when to use passive AND when to use active voice
