<?php

namespace Database\Seeders\Page_v2\FutureForms;

use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder;

class FutureFormsCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'maibutni-formy';
    }

    protected function description(): array
    {
        return [
            'title' => 'Майбутні форми',
            'subtitle_html' => '<p><strong>Майбутні форми</strong> допомагають говорити про плани, наміри, передбачення, обіцянки та рішення. У цьому розділі ти розберешся, коли вживати <strong>will</strong>, коли <strong>be going to</strong>, а також як помічати різницю між планом, думкою та видимим доказом.</p>',
            'subtitle_text' => 'Майбутні форми в англійській мові: will, be going to, плани, спонтанні рішення, передбачення та типові помилки.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цьому розділі ти навчишся <strong>розрізняти основні способи говорити про майбутнє</strong> та обирати форму залежно від значення.',
                        'rules' => [
                            [
                                'label' => 'WILL',
                                'color' => 'blue',
                                'text' => 'Спонтанні рішення, обіцянки, прохання, нейтральні прогнози:',
                                'example' => 'I\'ll help you. / I think it will rain.',
                            ],
                            [
                                'label' => 'BE GOING TO',
                                'color' => 'emerald',
                                'text' => 'Плани та передбачення на основі того, що видно зараз:',
                                'example' => 'I\'m going to call her. / Look at those clouds!',
                            ],
                            [
                                'label' => 'KEY IDEA',
                                'color' => 'amber',
                                'text' => 'Питай себе: це рішення зараз, уже готовий план чи висновок із доказу?',
                                'example' => 'moment now / plan before / evidence now',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Що входить у розділ',
                        'intro' => 'Майбутні форми покривають кілька типових значень:',
                        'rows' => [
                            [
                                'en' => 'Will',
                                'ua' => 'спонтанне рішення, обіцянка, думка',
                                'note' => 'I\'ll answer it. / I think she will come.',
                            ],
                            [
                                'en' => 'Be going to',
                                'ua' => 'намір або план, що вже існує',
                                'note' => 'We\'re going to move house.',
                            ],
                            [
                                'en' => 'Be going to',
                                'ua' => 'передбачення на основі доказу',
                                'note' => 'It\'s going to rain.',
                            ],
                            [
                                'en' => 'Choice of form',
                                'ua' => 'вибір залежить від значення, а не тільки від часу',
                                'note' => 'same future idea, different reason',
                            ],
                        ],
                        'warning' => '💡 У майбутніх формах важливо не лише “коли?”, а й “чому саме ця форма?”.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => 'На що звертати увагу',
                        'items' => [
                            'Якщо рішення виникло <strong>прямо зараз</strong>, зазвичай обираємо <strong>will</strong>.',
                            'Якщо план був <strong>ще до моменту мовлення</strong>, зазвичай обираємо <strong>be going to</strong>.',
                            'Якщо це <strong>думка або припущення</strong> без прямого доказу, частіше вживаємо <strong>will</strong>.',
                            'Якщо є <strong>видимий доказ</strong> зараз, частіше вживаємо <strong>be going to</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'maibutni-formy',
            'title' => 'Майбутні форми',
            'language' => 'uk',
        ];
    }
}
