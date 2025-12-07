<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Pages\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class PronounsDemonstrativesReciprocalPronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'reciprocal-pronouns-each-other-one-another';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Reciprocal Pronouns — each other, one another',
            'subtitle_html' => "<p><strong>Reciprocal pronouns</strong> (взаємні займенники) — це займенники, які позначають <strong>взаємну дію між двома або більше особами</strong>. В англійській мові є два взаємні займенники: <strong>each other</strong> (один одного, для двох осіб) і <strong>one another</strong> (один одного, для трьох і більше осіб). Проте в сучасній мові ця різниця часто ігнорується.</p>",
            'subtitle_text' => "Теоретичний огляд взаємних займенників англійської мови: each other і one another — правила вживання, відмінності, присвійні форми та типові помилки.",
            'locale' => 'uk',
            'category' => [
                'slug' => 'zaimennyky-ta-vkazivni-slova',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Reciprocal Pronouns',
                'Each Other',
                'One Another',
                'Pronouns',
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
                        'heading' => 'Reciprocal Pronouns — Взаємні займенники',
                        'html' => "<p>Взаємні займенники показують, що <strong>дві або більше особи виконують дію одне щодо одного</strong>. Це спосіб виразити взаємну дію без повторення іменників або займенників.</p>",
                    ],
                ],
                [
                    'layout' => 'two-columns',
                    'column' => 'left',
                    'columns' => [
                        [
                            'heading' => 'Each Other',
                            'html' => "<p><strong>Each other</strong> — один одного, одне одному (традиційно для двох осіб)</p><ul><li>They love <strong>each other</strong>. — Вони люблять один одного.</li><li>We help <strong>each other</strong>. — Ми допомагаємо один одному.</li></ul>",
                        ],
                        [
                            'heading' => 'One Another',
                            'html' => "<p><strong>One another</strong> — один одного (традиційно для трьох і більше осіб)</p><ul><li>The team members support <strong>one another</strong>. — Члени команди підтримують один одного.</li><li>They respect <strong>one another</strong>. — Вони поважають один одного.</li></ul>",
                        ],
                    ],
                ],
                [
                    'layout' => 'text',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Сучасне використання',
                        'html' => "<p>У сучасній англійській мові різниця між <strong>each other</strong> і <strong>one another</strong> майже зникла. Обидва вирази можна використовувати як для двох, так і для більше осіб.</p><ul><li>The two friends trust <strong>each other</strong> / <strong>one another</strong>. — Двоє друзів довіряють один одному.</li><li>All students helped <strong>each other</strong> / <strong>one another</strong>. — Усі студенти допомагали один одному.</li></ul><p><strong>Рекомендація:</strong> <em>each other</em> вживається частіше в розмовній мові для будь-якої кількості осіб.</p>",
                    ],
                ],
                [
                    'layout' => 'usage-panel',
                    'column' => 'left',
                    'columns' => [
                        [
                            'heading' => '✓ Взаємна дія',
                            'html' => "<p>Використовуйте reciprocal pronouns для дій, що відбуваються <strong>в обох напрямках</strong>:</p><ul><li>They kissed <strong>each other</strong>. — Вони поцілували один одного (він її + вона його).</li><li>The children looked at <strong>each other</strong>. — Діти подивилися один на одного.</li><li>We texted <strong>each other</strong> every day. — Ми писали один одному щодня.</li></ul>",
                        ],
                        [
                            'heading' => '✗ Не взаємна дія',
                            'html' => "<p><strong>Не плутайте</strong> з односторонньою дією або reflexive pronouns:</p><ul><li>❌ They talked to <strong>themselves</strong>. (кожен сам із собою)</li><li>✓ They talked to <strong>each other</strong>. (один з одним)</li><li>❌ She met <strong>herself</strong>.</li><li>✓ They met <strong>each other</strong>. (зустрілися один з одним)</li></ul>",
                        ],
                    ],
                ],
                [
                    'layout' => 'text',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Присвійні форми',
                        'html' => "<p>Взаємні займенники можуть мати <strong>присвійну форму</strong> з апострофом <strong>'s</strong>:</p><ul><li><strong>each other's</strong> — один одного (чий)</li><li><strong>one another's</strong> — один одного (чий)</li></ul><p><strong>Приклади:</strong></p><ul><li>They borrowed <strong>each other's</strong> books. — Вони позичали книги один одного.</li><li>We know <strong>each other's</strong> names. — Ми знаємо імена один одного.</li><li>The students corrected <strong>one another's</strong> mistakes. — Студенти виправляли помилки один одного.</li></ul>",
                    ],
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Each Other vs Reflexive Pronouns',
                        'intro' => 'Порівняння взаємних і зворотних займенників:',
                        'rows' => [
                            [
                                'en' => 'They love <strong>each other</strong>.',
                                'ua' => 'Вони люблять один одного.',
                                'note' => '(Він любить її + вона любить його — взаємна дія)',
                            ],
                            [
                                'en' => 'She loves <strong>herself</strong>.',
                                'ua' => 'Вона любить саму себе.',
                                'note' => '(Дія на себе)',
                            ],
                            [
                                'en' => 'We see <strong>each other</strong> often.',
                                'ua' => 'Ми часто бачимо один одного.',
                                'note' => '(Взаємна дія)',
                            ],
                            [
                                'en' => 'I see <strong>myself</strong> in the mirror.',
                                'ua' => 'Я бачу себе в дзеркалі.',
                                'note' => '(Дія на себе)',
                            ],
                            [
                                'en' => 'They hugged <strong>each other</strong>.',
                                'ua' => 'Вони обійняли один одного.',
                                'note' => '(Взаємна дія)',
                            ],
                            [
                                'en' => 'He hurt <strong>himself</strong>.',
                                'ua' => 'Він поранив себе.',
                                'note' => '(Дія на себе)',
                            ],
                        ],
                    ]),
                ],
                [
                    'layout' => 'text',
                    'column' => 'left',
                    'content' => [
                        'heading' => 'Типові вирази з Each Other',
                        'html' => "<ul><li><strong>know each other</strong> — знати один одного</li><li><strong>help each other</strong> — допомагати один одному</li><li><strong>love each other</strong> — любити один одного</li><li><strong>talk to each other</strong> — розмовляти один з одним</li><li><strong>listen to each other</strong> — слухати один одного</li><li><strong>look at each other</strong> — дивитися один на одного</li><li><strong>write to each other</strong> — писати один одному</li><li><strong>trust each other</strong> — довіряти один одному</li><li><strong>understand each other</strong> — розуміти один одного</li><li><strong>support each other</strong> — підтримувати один одного</li></ul>",
                    ],
                ],
                [
                    'layout' => 'common-mistakes',
                    'column' => 'left',
                    'mistakes' => [
                        [
                            'incorrect' => 'They love <strong>themselves</strong>.',
                            'correct' => 'They love <strong>each other</strong>.',
                            'explanation' => "Якщо дія <strong>взаємна</strong> (він любить її + вона любить його), використовуйте <em>each other</em>, а не reflexive pronoun.",
                        ],
                        [
                            'incorrect' => 'We met <strong>us</strong>.',
                            'correct' => 'We met <strong>each other</strong>.',
                            'explanation' => "Після дієслів взаємної дії використовуйте <em>each other</em>, а не object pronoun.",
                        ],
                        [
                            'incorrect' => "They know <strong>each others</strong> names.",
                            'correct' => "They know <strong>each other's</strong> names.",
                            'explanation' => "Присвійна форма — <em>each other's</em> (з апострофом), а не <em>each others</em>.",
                        ],
                        [
                            'incorrect' => 'She talked to <strong>each other</strong>.',
                            'correct' => 'They talked to <strong>each other</strong>.',
                            'explanation' => "Reciprocal pronouns вимагають <strong>множини</strong> (дві або більше особи). Одна особа не може виконувати взаємну дію.",
                        ],
                    ],
                ],
                [
                    'layout' => 'summary',
                    'column' => 'left',
                    'summary_points' => [
                        '<strong>Each other</strong> і <strong>one another</strong> — взаємні займенники для позначення взаємної дії між двома або більше особами.',
                        'Традиційно <em>each other</em> для двох, <em>one another</em> для трьох і більше, але в сучасній мові ця різниця майже зникла.',
                        'Присвійна форма: <strong>each other\'s</strong> / <strong>one another\'s</strong> (з апострофом).',
                        '<strong>Не плутайте</strong> з reflexive pronouns (myself, yourself): each other = взаємна дія, reflexive = дія на себе.',
                        'Reciprocal pronouns вимагають <strong>множини</strong> — потрібні дві або більше особи для взаємної дії.',
                    ],
                ],
                [
                    'layout' => 'practice',
                    'column' => 'left',
                    'exercises' => [
                        [
                            'instruction' => 'Оберіть правильний варіант:',
                            'items' => [
                                'They love (each other / themselves).',
                                'We helped (each other / ourselves) with homework.',
                                "I know (each other's / her) address.",
                                'The team supported (one another / oneself).',
                                'She talks to (herself / each other) when nervous.',
                            ],
                        ],
                        [
                            'instruction' => 'Виправте помилки:',
                            'items' => [
                                'They met themselves at the party.',
                                'We respect each others opinions.',
                                'She and I understand each other very good.',
                                'The students helped themself.',
                                'He talked to each other.',
                            ],
                        ],
                    ],
                ],
                [
                    'layout' => 'navigation',
                    'column' => 'footer',
                    'links' => [
                        [
                            'title' => '← Reflexive Pronouns',
                            'url' => '/theory/reflexive-pronouns-myself-yourself-themselves',
                        ],
                        [
                            'title' => 'Each / Every / All →',
                            'url' => '/theory/each-every-all',
                        ],
                    ],
                ],
            ],
        ];
    }
}
