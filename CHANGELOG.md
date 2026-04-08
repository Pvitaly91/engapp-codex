# Changelog

## 2026-04-08

### Changed
- Refactored existing `Page_V3` theory JSON seeders to align `Basic Grammar`, `Nouns/Articles/Quantity`, `Pronouns`, `Future Forms`, `Questions & Negations`, `Adjectives`, `Some / Any`, and `Passive Voice` with their real, existing content scopes.
- Normalized category and page titles, subtitles, hero/summary copy, tags, topic tables, and navigation chips to remove promises about non-existent pages.
- Corrected IA overlap and scope duplication between `BasicGrammar` and `QuestionsNegations`, and between `QuestionsNegations` and non-existent question types.
- Scoped `Some / Any` to current places-only coverage and adjusted related navigation/copy accordingly.
- Reframed `Passive Voice` structure with consistent navigation across `Basics`, `Tenses`, `Infinitives & Gerunds`, and `Special Cases`.

### Not Added
- Did not create new top-level sections or new page definitions.
- Did not modify routes, controllers, tests, or seeder pipeline structure.
- Did not add placeholder/“coming soon” pages.
