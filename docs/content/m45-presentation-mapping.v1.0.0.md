# M45 — явне відображення basic/detail, 1.0.0

Відображення переносить авторські поля без переказу: subtitle та hero → header; slots 1–5 → відповідні наявні theory blocks; practice → один новий deterministic practice-set. Short basic не обчислюється з довгих абзаців у runtime.

У кожному уроці дві картки форм, кожна з трьома рядками: формула, один англійський приклад, точний український переклад. Notes і основні застереження видимі. Details належать лише своїй картці/пункту; вкладених disclosures немає.

## Future Perfect vs Future Continuous

Owner: `Database\Seeders\Page_V3\FutureForms\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder`

Local URL: [відкрити](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-continuous)

| Slot | Section ID | Видимі groups | Detail → owner |
|---|---|---:|---|
| 1 | `m45-a-contrast` | 1 | `m45-a-completion-detail` → `m45-a-process-result` |
| 2 | `m45-a-forms` | 2 | `m45-a-continuous-form-detail` → `m45-a-continuous-forms`<br>`m45-a-perfect-form-detail` → `m45-a-perfect-forms` |
| 3 | `m45-a-usage` | 2 | — |
| 4 | `m45-a-limitations` | 2 | `m45-a-old-alternatives-detail` → `m45-a-context-not-error` |
| 5 | `m45-a-summary` | 1 | — |

Разом: 4 details; 6 tasks; 6 required controls.

Практика зберігає exact IDs, stimuli та stimulus_uk, canonical answers, explicit aliases, token order/multisets і feedback. Canonical answer включається до accepted set незалежно від aliases. Усі required parts потрібні для бала; токени проєктуються як manual + source_kind=tokens.

### Рішення про поглиблення

- `m45-a-completion-detail/reason_uk` — role=`editorial-meta`: Поглиблення розрізняє завершену одиницю роботи й ширшу діяльність та пояснює межу висновків про періоди і стани. Коротке необхідне застереження вже є в basic.
- `m45-a-continuous-form-detail/reason_uk` — role=`editorial-meta`: Це окреме пояснення збереження допоміжних дієслів після скорочень та порядку питальних слів; основні три форми й переклади залишаються видимими.
- `m45-a-perfect-form-detail/reason_uk` — role=`editorial-meta`: Поглиблення пояснює неправильну V3, незмінне have та спільний механізм коротких відповідей. Це не прихована основна формула й не один додатковий приклад без пояснення.
- `m45-a-old-alternatives-detail/reason_uk` — role=`editorial-meta`: Це контекстний аналіз двох історичних wrong/right пар: він відокремлює допустимий інший акцент від невідповідності конкретній вимозі. Основне застереження про by та гарантію результату вже видно в basic.

## Future Perfect vs Future Perfect Continuous

Owner: `Database\Seeders\Page_V3\FutureForms\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder`

Local URL: [відкрити](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous)

| Slot | Section ID | Видимі groups | Detail → owner |
|---|---|---:|---|
| 1 | `m45-b-contrast` | 1 | — |
| 2 | `m45-b-forms` | 2 | `m45-b-forms-answers-detail` → `m45-b-forms-perfect` |
| 3 | `m45-b-usage` | 2 | `m45-b-usage-alternatives-detail` → `m45-b-usage-period-state`<br>`m45-b-usage-repeated-detail` → `m45-b-usage-process` |
| 4 | `m45-b-limitations` | 2 | `m45-b-limitations-meaning-detail` → `m45-b-limitations-focus` |
| 5 | `m45-b-summary` | 1 | — |

Разом: 4 details; 6 tasks; 6 required controls.

Практика зберігає exact IDs, stimuli та stimulus_uk, canonical answers, explicit aliases, token order/multisets і feedback. Canonical answer включається до accepted set незалежно від aliases. Усі required parts потрібні для бала; токени проєктуються як manual + source_kind=tokens.

### Рішення про поглиблення

- `m45-b-forms-answers-detail/reason_uk` — role=`editorial-meta`: Окремо пояснює порядок допоміжних дієслів, короткі відповіді та побудову питання про тривалість; основні формули залишаються видимими.
- `m45-b-usage-alternatives-detail/reason_uk` — role=`editorial-meta`: Розбирає три допустимі альтернативи й виправляє стару заборону Future Perfect із тривалістю; це контекстний аналіз, а не прихована коротка ремарка.
- `m45-b-usage-repeated-detail/reason_uk` — role=`editorial-meta`: Пояснює повторювану діяльність і межі висновків про початок, перерви та завершення, не ховаючи базового застереження.
- `m45-b-limitations-meaning-detail/reason_uk` — role=`editorial-meta`: Відокремлює граматичну будову від невідповідності заданій меті та пояснює, чому how long не є автоматичним перемикачем.

## Future Continuous vs Future Perfect Continuous

Owner: `Database\Seeders\Page_V3\FutureForms\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder`

Local URL: [відкрити](http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous)

| Slot | Section ID | Видимі groups | Detail → owner |
|---|---|---:|---|
| 1 | `m45-c-contrast` | 1 | `m45-c-timeline-detail` → `m45-c-timeline` |
| 2 | `m45-c-forms` | 2 | `m45-c-fc-forms-detail` → `m45-c-fc-forms`<br>`m45-c-fpc-forms-detail` → `m45-c-fpc-forms` |
| 3 | `m45-c-usage` | 2 | `m45-c-planned-duration-detail` → `m45-c-planned-duration` |
| 4 | `m45-c-limitations` | 3 | `m45-c-time-context-detail` → `m45-c-time-context` |
| 5 | `m45-c-summary` | 1 | — |

Разом: 5 details; 6 tasks; 6 required controls.

Практика зберігає exact IDs, stimuli та stimulus_uk, canonical answers, explicit aliases, token order/multisets і feedback. Canonical answer включається до accepted set незалежно від aliases. Усі required parts потрібні для бала; токени проєктуються як manual + source_kind=tokens.

### Рішення про поглиблення

- `m45-c-timeline-detail/reason_uk` — role=`editorial-meta`: Поглиблення пояснює відносну часову перспективу й межі висновків про початок, перерви та завершення; базова різниця залишається видимою.
- `m45-c-fc-forms-detail/reason_uk` — role=`editorial-meta`: Окремо пояснює побудову питань і скорочення, не приховуючи трьох основних форм.
- `m45-c-fpc-forms-detail/reason_uk` — role=`editorial-meta`: Пояснює зміст заперечення конкретного періоду та порядок слів у питанні про тривалість.
- `m45-c-planned-duration-detail/reason_uk` — role=`editorial-meta`: Поглиблення переносить відмінність між запланованим і накопиченим періодом на питальні речення.
- `m45-c-time-context-detail/reason_uk` — role=`editorial-meta`: Поглиблення окремо розбирає три історичні wrong/right приклади, відділяючи граматичну форму, природність і відповідність заданому змісту.
