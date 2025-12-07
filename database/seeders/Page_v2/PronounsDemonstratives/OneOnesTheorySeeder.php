<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Pages\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class OneOnesTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'one-ones';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'One / Ones — Заміна іменників',
            'subtitle_html' => "<p><strong>One</strong> і <strong>ones</strong> — це <strong>займенники-замінники</strong>, які використовуються замість іменника, щоб <strong>уникнути повторення</strong>. <strong>One</strong> замінює іменник в однині, <strong>ones</strong> — у множині. Вони допомагають зробити мову більш природною та лаконічною.</p>",
            'subtitle_text' => "Теоретичний огляд використання one і ones як займенників-замінників іменників: правила вживання, позиція в реченні, поєднання з прикметниками та означеннями.",
            'locale' => 'uk',
            'category' => [
                'slug' => '3',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'One',
                'Ones',
                'Pronouns',
                'Substitution',
                'Grammar',
                'Theory',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'layout' => 'hero',
                    'column' => 'header',
                    'hero_image' => null,
                    'content' => [
                        'heading' => 'One / Ones — Займенники-замінники',
                        'html' => "<p>Замість повторення іменника в реченні, ми використовуємо <strong>one</strong> (однина) або <strong>ones</strong> (множина). Це робить мову більш природною та економною.</p>",
                    ],
                ],
                [
                    'layout' => 'usage-panel',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Основне правило',
                        'html' => "<p><strong>One</strong> замінює обчислюваний іменник в однині, <strong>ones</strong> — у множині.</p>
<ul>
<li><strong>Which bag do you want?</strong> — The red <strong>one</strong>. (замість: the red <em>bag</em>)</li>
<li><strong>Which shoes do you like?</strong> — The black <strong>ones</strong>. (замість: the black <em>shoes</em>)</li>
<li><strong>I need a pen.</strong> — Here's a blue <strong>one</strong>. (замість: a blue <em>pen</em>)</li>
<li><strong>These apples are sour.</strong> — Try those sweet <strong>ones</strong>. (замість: those sweet <em>apples</em>)</li>
</ul>
<p><strong>Увага:</strong> One/ones використовуються тільки з <strong>обчислюваними</strong> іменниками. З необчислюваними (water, advice, information) вони не вживаються.</p>",
                    ],
                ],
                [
                    'layout' => 'usage-panel',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Використання з прикметниками',
                        'html' => "<p><strong>One/ones</strong> часто йдуть після прикметників або означень:</p>
<ul>
<li><strong>I don't like this dress.</strong> — Try the <strong>blue one</strong>.</li>
<li><strong>These cakes are expensive.</strong> — Let's buy the <strong>cheap ones</strong>.</li>
<li><strong>Do you have a bigger bag?</strong> — Yes, we have some <strong>larger ones</strong>.</li>
<li><strong>I prefer old books to new ones.</strong> (старі книжки vs нові)</li>
</ul>",
                    ],
                ],
                [
                    'layout' => 'usage-panel',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Використання з означеннями',
                        'html' => "<p><strong>One/ones</strong> вживаються з артиклями (the, a/an) та вказівними займенниками (this, that, these, those):</p>
<ul>
<li><strong>The one</strong> on the left — той/та, що зліва</li>
<li><strong>A red one</strong> — червоний/червона</li>
<li><strong>This one</strong> — цей/ця</li>
<li><strong>That one</strong> — той/та</li>
<li><strong>These ones</strong> — ці</li>
<li><strong>Those ones</strong> — ті</li>
<li><strong>Which one?</strong> — Який/яка?</li>
<li><strong>The big ones</strong> — великі</li>
</ul>
<p><strong>Приклади:</strong></p>
<ul>
<li><strong>I like this shirt.</strong> — I prefer <strong>that one</strong>.</li>
<li><strong>Can I see those shoes?</strong> — Do you mean <strong>these ones</strong>?</li>
</ul>",
                    ],
                ],
                [
                    'layout' => 'usage-panel',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Коли НЕ використовуємо one/ones',
                        'html' => "<p><strong>1. Після присвійних займенників</strong> (my, your, his, her, our, their):</p>
<ul>
<li>❌ <strong>My one</strong> is broken.</li>
<li>✅ <strong>Mine</strong> is broken. (використовуємо присвійний займенник)</li>
<li>❌ <strong>Your ones</strong> are better.</li>
<li>✅ <strong>Yours</strong> are better.</li>
</ul>
<p><strong>2. З необчислюваними іменниками:</strong></p>
<ul>
<li>❌ I need <strong>some water</strong>. — Here's a cold <strong>one</strong>.</li>
<li>✅ I need <strong>some water</strong>. — Here's <strong>some</strong>.</li>
<li>❌ Do you want <strong>advice</strong>? — I need a good <strong>one</strong>.</li>
<li>✅ Do you want <strong>advice</strong>? — I need <strong>some</strong>.</li>
</ul>
<p><strong>3. Після some, any, both, either, neither:</strong></p>
<ul>
<li>✅ I need <strong>some</strong>. (не <em>some ones</em>)</li>
<li>✅ Do you have <strong>any</strong>? (не <em>any ones</em>)</li>
<li>✅ I like <strong>both</strong>. (не <em>both ones</em>)</li>
</ul>",
                    ],
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'heading' => 'One vs Ones — Порівняння',
                        'rows' => [
                            [
                                'en' => '<strong>One</strong> (однина)',
                                'ua' => '<strong>Ones</strong> (множина)',
                                'note' => 'Використання',
                            ],
                            [
                                'en' => 'The <strong>red one</strong>',
                                'ua' => 'The <strong>red ones</strong>',
                                'note' => 'З прикметником',
                            ],
                            [
                                'en' => 'A <strong>big one</strong>',
                                'ua' => 'Some <strong>big ones</strong>',
                                'note' => 'З артиклем',
                            ],
                            [
                                'en' => 'This <strong>one</strong>',
                                'ua' => 'These <strong>ones</strong>',
                                'note' => 'З вказівним займенником',
                            ],
                            [
                                'en' => 'Which <strong>one</strong>?',
                                'ua' => 'Which <strong>ones</strong>?',
                                'note' => 'У питаннях',
                            ],
                            [
                                'en' => 'The <strong>one</strong> on the table',
                                'ua' => 'The <strong>ones</strong> in the box',
                                'note' => 'З прийменниковою фразою',
                            ],
                        ],
                    ]),
                ],
                [
                    'layout' => 'common-mistakes',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Типові помилки',
                        'html' => "<ul>
<li>❌ <strong>I like your one.</strong><br>✅ <strong>I like yours.</strong> (після присвійних займенників не використовуємо one)</li>
<li>❌ <strong>I need some ones.</strong><br>✅ <strong>I need some.</strong> (після some не використовуємо ones)</li>
<li>❌ <strong>Do you have any ones?</strong><br>✅ <strong>Do you have any?</strong> (після any не використовуємо ones)</li>
<li>❌ <strong>I want a coffee. A hot one.</strong><br>✅ <strong>I want a coffee. A hot one.</strong> або <strong>I want some coffee. Hot (coffee).</strong> (coffee — обчислюваний як «чашка кави»)</li>
<li>❌ <strong>Give me water. A cold one.</strong><br>✅ <strong>Give me water. Cold (water).</strong> або <strong>Give me some. Cold.</strong> (water — необчислюваний)</li>
<li>❌ <strong>The ones mine are broken.</strong><br>✅ <strong>Mine are broken.</strong> (не поєднуємо one з присвійними займенниками)</li>
</ul>",
                    ],
                ],
                [
                    'layout' => 'usage-panel',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Резюме',
                        'html' => "<p><strong>One</strong> і <strong>ones</strong> — це зручні займенники-замінники, які допомагають уникнути повторення іменників:</p>
<ul>
<li><strong>One</strong> — для однини</li>
<li><strong>Ones</strong> — для множини</li>
<li>Використовуються з <strong>обчислюваними</strong> іменниками</li>
<li>Вживаються з артиклями, прикметниками, вказівними займенниками</li>
<li><strong>НЕ використовуються</strong> після присвійних займенників (my, your, his тощо)</li>
<li><strong>НЕ використовуються</strong> після some, any, both, either, neither</li>
<li><strong>НЕ використовуються</strong> з необчислюваними іменниками</li>
</ul>",
                    ],
                ],
                [
                    'layout' => 'practice-exercises',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Практичні вправи',
                        'html' => "<p><strong>Виберіть правильний варіант:</strong></p>
<ol>
<li>I don't like this bag. Can I see the blue ___? (one / ones)</li>
<li>These apples are too small. Do you have bigger ___? (one / ones)</li>
<li>Which car is yours? — The red ___. (one / ones)</li>
<li>I need a pen. — Here's a black ___. (one / ones)</li>
<li>Do you want my book? — No, I prefer ___. (your one / yours)</li>
<li>I need some chairs. — We have some wooden ___. (one / ones)</li>
<li>Can I have water? — Do you want a cold ___? (one / some)</li>
<li>I like both dresses, but ___ is more expensive. (this one / this)</li>
</ol>
<p><strong>Відповіді:</strong> 1. one, 2. ones, 3. one, 4. one, 5. yours, 6. ones, 7. some, 8. this one</p>",
                    ],
                ],
                [
                    'layout' => 'navigation',
                    'column' => 'footer',
                    'content' => [
                        'category_back' => true,
                        'category_next' => false,
                    ],
                ],
            ],
        ];
    }
}
