You are an AI coding assistant inside a Laravel project.

CONFIG (EDIT THESE VALUES BEFORE RUNNING):
- TOPIC_NAME: {{ $topicName }} 
  (example: "Mixed Conditionals", "Comparative and Superlative Adjectives", "Present Perfect vs Past Simple")
- OPTIONAL_THEORY_URL: {{ $theoryUrl }} 
  (example: "https://gramlyze.com/pages/conditions/mixed-conditional" or "none")
- BASE_SEEDER_CLASS: {{ $baseSeederClass }} 
  (example: "V2\\ConditionalsMixedPracticeV2Seeder")
- NEW_SEEDER_NAMESPACE_PATH: {{ $newSeederNamespacePath }} 
  (example: "database/seeders/AI/Claude")
- NEW_SEEDER_CLASS_NAME: {{ $newSeederClassName }} 
  (example: "MixedConditionalsAIGeneratedSeeder")
- HINTS_LANGUAGE: {{ $hintsLanguage }} 
  (language for chatgpt_explanations & question_hints, default: "Ukrainian")

- LEVELS_TO_USE: {!! json_encode($levels, JSON_UNESCAPED_UNICODE) !!} 
  (example: ["A1", "A2", "B1", "B2", "C1", "C2"])
- QUESTIONS_PER_LEVEL: {{ $questionsPerLevel }} 
  (example: 12)

GENERAL TASK:
Create a NEW Laravel seeder for grammar questions on the topic:
TOPIC_NAME (see config above).

If OPTIONAL_THEORY_URL is not "none", you may use it only as a reference for the grammar rules, 
but you must create completely original questions.

You must:
- Inspect BASE_SEEDER_CLASS.
- Reuse the same structure, methods and logic that BASE_SEEDER_CLASS uses to build and insert questions.
- Follow the same array keys / fields / models that BASE_SEEDER_CLASS relies on.

FILE & NAMESPACE:
- Place the new seeder class in NEW_SEEDER_NAMESPACE_PATH.
- Use NEW_SEEDER_CLASS_NAME as the class name.
- Follow the same namespace and base class conventions as other seeders in this project.

LANGUAGE:
- Question text and all answer options must be in ENGLISH.
- Fields chatgpt_explanations and question_hints must be generated in HINTS_LANGUAGE.

QUESTION QUANTITY & LEVELS:
- Use CEFR levels from LEVELS_TO_USE list.
- For EACH level in LEVELS_TO_USE generate EXACTLY QUESTIONS_PER_LEVEL UNIQUE questions.
- Total number of questions = LEVELS_TO_USE.length × QUESTIONS_PER_LEVEL.
- Each question must have a field that stores its level (for example: "level" => "B1").

GENERAL REQUIREMENTS FOR QUESTIONS:
- Topic: strictly TOPIC_NAME (do not mix other grammar topics).
- Use your own original questions, DO NOT copy from existing seeders or external sources.
- If this grammar topic allows it:
  - Include different sentence forms: affirmative, negative, interrogative.
  - Include a variety of time references: present, past, future.
- Avoid questions that are too similar in meaning.
- NO duplicated questions (text or structure).

FIELDS PER QUESTION 
(ADAPT FIELD NAMES TO MATCH BASE_SEEDER_CLASS, BUT KEEP THIS INFORMATION):

For each question, generate:
- text / question text with a gap for the missing part (e.g. "If I ____ more time, I would travel the world.").
- possible answers (options).
- correct answer.
- level (one of LEVELS_TO_USE).
- tags (an array of detailed tags).
- verb_hint
- chatgpt_explanations
- question_hints
- source
- questions.flag
- questions.type

VERB_HINT RULES:
- verb_hint must be in a base form of the verb or a neutral hint expression.
  - Example: if the correct answer is "had gone", verb_hint should be "go", NOT "had gone".
- verb_hint MUST NOT contain the exact correct answer.
- If a pure base form would immediately reveal the answer or is not appropriate:
  - use a short leading hint instead, such as:
    - "Use a past perfect form"
    - "Use would + base verb"
    - "Use would have + V3"

EXPLANATIONS & HINTS (chatgpt_explanations, question_hints):
- Both chatgpt_explanations and question_hints MUST be written in HINTS_LANGUAGE.
- For each question:

  chatgpt_explanations:
  - Extended explanation in HINTS_LANGUAGE that:
    - clearly explains the grammar rule or formula used in this question,
      e.g. patterns like "If + past perfect, would have + V3", "adjective + -er than", "the + superlative".
    - includes 1–2 extra example sentences (in English) that illustrate the rule.
    - DOES NOT contain the exact correct answer phrase from this question.

  question_hints:
  - Shorter hints in HINTS_LANGUAGE that:
    - remind the student about the pattern, formula or typical structure.
    - may give a similar example or partial pattern.
    - AGAIN: must NOT include the exact correct answer phrase.

LEVEL EVALUATION:
- Assign a CEFR level (from LEVELS_TO_USE) to each question based on:
  - structure complexity
  - vocabulary
  - length
  - type of grammar pattern
- Store this value in a dedicated field (e.g. "level" => "B1") in each question.

TAGS:
- For every question generate a detailed "tags" field (array of strings).
- Use tags to describe:
  - main grammar area (e.g. "conditionals", "comparison", "present perfect", "passive voice").
  - sub-type (e.g. "mixed conditional", "second conditional", "superlative adjectives").
  - tense/aspect if relevant (e.g. "past perfect", "present simple", "would have + V3").
  - sentence form (e.g. "affirmative", "negative", "interrogative").
  - CEFR level (e.g. "B1").
- Example:
  - ["grammar", "conditionals", "mixed conditional", "past perfect", "would have + V3", "B2", "negative"]

SOURCE, FLAG, TYPE (FIXED REQUIREMENTS):
- Add a field like "source" indicating that these questions were generated by AI for this topic.
  - Example: "AI generated: TOPIC_NAME (SET 1)".
- All questions must have:
  - questions.flag = 2
  - questions.type = 0
- Use the exact keys/columns and structure that BASE_SEEDER_CLASS uses when inserting questions.

CODE STYLE:
- Follow the exact collection/array structure of BASE_SEEDER_CLASS so that the new seeder can be executed without manual changes.
- Implement a run() method that inserts ALL generated questions (all levels, all quantities).
- Do not add any external dependencies. Use only standard Laravel seeder patterns already used in BASE_SEEDER_CLASS.
