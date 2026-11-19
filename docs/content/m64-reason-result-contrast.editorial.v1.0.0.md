# M64 — редакційна карта BEFORE → AFTER

База: `b5f521d9886a1a9b1c5298f84f581bf7b54e2713`. Три UK owners, Page 36/37/30, category 34. Це нова авторська редакція конкретних уроків, а не копія M63 overview. Незалежний source/DB/HTTP BEFORE зберігається приватно; навчальний BEFORE — у versioned content patch.

## Що збережено

Повні назви/H1, slugs, ancestry, subtitle strong-title, hero B1 (display B1-B2), сім continuing slots/UUID/anchors, порядок і columns, null levels п’яти колишніх box-блоків; дев’ять navigation destinations із labels/order/current flags. Навігація не замінена практикою.

## Предметні рішення

- Reason: збережено because/since/as та because of; колишні короткі labels про «найсильнішу» й «м’якшу» причину уточнено як фокус і подання фону. Це педагогічне спрощення, не твердження про об’єктивну силу причини. Додано Why-dialogue проти формального фрагмента, початкове Because, причина/час, прозоре питання й контекст заперечення. As не оголошено граматичною помилкою через стилістичну перевагу because.
- Result: збережено so, therefore та as a result; нові власні ситуації відокремлюють наслідок, рішення, висновок і мету. Причина й наслідок не міняються місцями. Therefore не названо автоматично логічнішим, so не заборонено в письмі. Повні punctuation-моделі показують period/semicolon/internal position та and therefore; comma splice пояснено, але grader не змінено.
- Contrast: збережено although/though/however; уточнено clause pattern, поступку й зіставлення, final though та however у значенні degree. Додано скорочення з явним логічним підметом; збережено обидва факти й не створено універсальної заборони на кому після although.

Старі короткі приклади замінено власними повними EN/UK ситуаціями, щоб явно показати час, учасників, заперечення й причинний напрямок. Нові приклади та 18 tasks не видаються за старий source. Однакові повтори перенесено в компактний summary; basic лишається самодостатнім.

## Карта компонентів і details

У кожній сторінці slots 1–5: box → usage-panels / forms-grid / comparison-table / mistakes-grid / summary-list. Це 15 явних type/heading transitions, а не body-only update. Columns і levels не змінюються. Hero і navigation-chips збережені; append slot 7 — practice-set B1. Точна карта: `m64-reason-result-contrast.components.v1.json`.

Три окремі змістовні details:
Reason — reason-focus /sections/2 (два об’єкти заперечення);
Result — result-meaning /sections/1 (намір, рішення, здійснений наслідок);
Contrast — contrast-meaning /sections/3 (власник ознаки у скороченні).
Це семантичні finite decisions, не runtime word-count threshold. Необхідні правила, переклади й застереження залишаються у basic.

## Практика та межі matcher

18 власних tasks: 6 selects, 6 meaning choices, 6 inputs. Усі inputs мають manual/token mode; разом 7 accepted variants (один явний Result word-order alias). Окремо 15 selected widget questions: 7 reused + 8 new, розподіл new 3/2/3. Старі 36 B1 кандидати прочитано; 29 не відібрано через якість, дублювання або недостатній контекст. Їхні DB keys не переписуються.

Незмінний normalize ігнорує пунктуацію. Тому punctuation A/B мають різні українські semantic values, а не англійські значення, що відрізняються лише комою. Manual/token grading не є перевіркою ком. Контекст і критерій видно до Check; переклад meaning-choice і пояснення обох stimuli — після будь-якого Check. Feedback — plain text; невідомі typo зберігають generic fallback. Граматична альтернатива, що не виконує явної умови, не названа неграматичною.

## Мова та джерела

Cambridge Grammar Today / dictionary, Purdue і Google Search Central використано для перевірки правил, пунктуації й helpful-content підходу. Точні actual URLs, перевірене питання й спосіб доступу містить master.sources. Direct 403 не названо успішним direct-body: де доступний publisher-indexed body, це зазначено окремо. Учнівські коментарі та видавничі exercise banks не використано. Автор-рецензент, search-volume або обіцянки зростання позицій не вигадані.

## Приватність і freeze

Master 1.0.0, native patch, teaching BEFORE, builder contract і question supplement заморожено до apply. Новий bank-contract містить лише погоджені навчальні поля, IDs і opaque hashes; raw SQL rows, timestamps, runtime identity, screenshots та proofs залишаються приватними. Дозвіл на цей teaching-only контракт отримано окремо.

Ця редакційна карта не є звітом про live apply/browser acceptance. Фактичний результат і межі перевірок фіксуються у підсумковому звіті M64.
