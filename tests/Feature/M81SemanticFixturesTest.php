<?php

namespace Tests\Feature;

use App\Support\ComposeTokenCase;
use App\Support\M81BuilderSelectionPresentation as Builder;
use App\Support\M81TransformationsCommonMistakes as Package;
use PHPUnit\Framework\TestCase;

/** Independent literal editorial fixtures, not an arbitrary-paraphrase analyser. */
class M81SemanticFixturesTest extends TestCase
{
    private function release(): array
    {
        $release = Package::load(dirname(__DIR__, 2));
        Package::validate($release[0], $release[1], $release[2], $release[3]);

        return $release;
    }

    private function tasks(array $lesson): array
    {
        $practices = array_values(array_filter($lesson['blocks'], static fn (array $b): bool => $b['type'] === 'practice-set'));
        self::assertCount(1, $practices);
        $result = [];
        foreach (['selects', 'choices', 'inputs'] as $group) {
            self::assertCount(2, $practices[0]['data'][$group]);
            foreach ($practices[0]['data'][$group] as $task) {
                self::assertArrayNotHasKey($task['id'], $result);
                $result[$task['id']] = $task;
            }
        }
        self::assertCount(6, $result);

        return $result;
    }

    private function reviewed(array $task, string $answer, array $aliases, array $visibleFragments, array $rejected): void
    {
        self::assertSame($answer, $task['answer']);
        self::assertSame($aliases, $task['accepted']);
        $visible = $task['label'] ?? $task['after'] ?? '';
        self::assertStringContainsString('<p lang="en">', $visible);
        foreach ($visibleFragments as $fragment) {
            self::assertStringContainsString($fragment, $visible, $task['id'].' visible criterion/source');
        }
        foreach (['hint', 'placeholder', 'source_sentence', 'prompt', 'strict', 'punctuation_sensitive'] as $field) {
            self::assertArrayNotHasKey($field, $task);
        }
        $acceptedKeys = array_map(Package::comparisonKey(...), $aliases);
        foreach ($aliases as $alias) {
            self::assertContains(Package::comparisonKey($alias), $acceptedKeys);
            $feedback = $task['feedback'][mb_strtolower(trim($alias))];
            self::assertStringContainsString('—', $feedback, 'Feedback includes a literal translation.');
            self::assertDoesNotMatchRegularExpression('/<\/?[a-z][^>]*>|&(?:#[0-9]+|[a-z]+);/iu', $feedback);
        }
        foreach ($rejected as $candidate) {
            self::assertNotContains(Package::comparisonKey($candidate), $acceptedKeys, $task['id'].' finite rejected probe');
        }
        if (isset($task['before'])) {
            $groups = preg_split('/\s*\/\s*/u', $task['before']);
            foreach ($groups as $group) {
                self::assertLessThanOrEqual(3, count(preg_split('/\s+/u', trim($group))));
            }
            self::assertNotSame($answer, implode(' ', $groups), 'A shuffled bank is not an ordered target hint.');
            $actualWords = preg_split('/\s+/u', implode(' ', $groups));
            $expectedWords = preg_split('/\s+/u', $answer);
            sort($actualWords); sort($expectedWords);
            self::assertSame($expectedWords, $actualWords, 'Exact words and multiplicity are retained.');
        }
    }

    public function test_conditionals_preserve_unless_scope_regret_time_preference_actor_and_warning_force(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][0]; $t = $this->tasks($lesson);
        $this->reviewed($t['m81-conditionals-form-unless'], 'hurry', ['hurry'],
            ['If you don’t hurry', 'без зміни цієї умови'], ['don’t hurry', 'will hurry']);
        $this->reviewed($t['m81-conditionals-form-past-regret'], 'had studied', ['had studied'],
            ['минулорічне навчання', 'вже завершилося', 'last year'], ['studied', 'study']);
        $this->reviewed($t['m81-conditionals-meaning-train'], 'Ні: попередження не виключає інших перешкод.',
            ['Ні: попередження не виключає інших перешкод.'], ['Відоме лише це попередження', 'обов’язково встигнеш'],
            ['Так: поспіх сам по собі гарантує успіх.']);
        $this->reviewed($t['m81-conditionals-meaning-advice-force'], 'Ні: had better додає сильніший відтінок попередження.',
            ['Ні: had better додає сильніший відтінок попередження.'], ['спокійна загальна рекомендація', 'You should take a short break.'],
            ['Так: обидві конструкції завжди однаково нейтральні.']);
        $this->reviewed($t['m81-conditionals-sentence-preference'], 'I’d rather you didn’t call after ten tonight.',
            ['I’d rather you didn’t call after ten tonight.', 'I would rather you didn’t call after ten tonight.',
                'I’d rather you did not call after ten tonight.', 'I would rather you did not call after ten tonight.'],
            ['I’d prefer you not to call after ten tonight.', 'іншої людини', 'не наказ'],
            ['I’d rather not call after ten tonight.', 'I’d rather you don’t call after ten tonight.', 'You must not call after ten tonight.']);
        $this->reviewed($t['m81-conditionals-sentence-warning'], 'You had better not touch this wire because it may be live.',
            ['You had better not touch this wire because it may be live.', 'You’d better not touch this wire because it may be live.'],
            ['сильну пораду-попередження', 'This wire may be live', 'причину подай після поради'],
            ['You had better touch this wire because it may be live.', 'You had better not touch this wire because it is live.',
                'You had better not to touch this wire because it may be live.']);
        self::assertStringContainsString('не гарантує успіху', $lesson['blocks'][1]['data']['sections'][0]['note']);
        self::assertSame('We’ll walk home unless it isn’t safe.', $lesson['details'][0]['fragment']['data']['sections'][0]['examples'][0]['en']);
        self::assertStringContainsString('не можна приписати першій', $lesson['details'][0]['fragment']['data']['sections'][0]['note']);
    }

    public function test_relative_linking_preserves_role_actor_reason_and_purpose_instead_of_merely_shortening(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][1]; $t = $this->tasks($lesson);
        $this->reviewed($t['m81-relative-clauses-linking-words-form-role'], 'which', ['which'],
            ['Office — додаток до visited', 'підмет we збережений'], ['where', 'who']);
        $this->reviewed($t['m81-relative-clauses-linking-words-form-cause'], 'because of', ['because of'],
            ['Злива була причиною', 'the heavy rain'], ['because', 'despite']);
        $this->reviewed($t['m81-relative-clauses-linking-words-meaning-actor'], 'Ні: скорочена версія приписує втому Олені.',
            ['Ні: скорочена версія приписує втому Олені.'], ['Втомилася Марта', 'закінчила звіт Олена'],
            ['Так: being tired може без уточнення означати Мартину втому.']);
        $this->reviewed($t['m81-relative-clauses-linking-words-meaning-relative-subject'],
            'Ні: викладає син, а нова версія називає викладачкою жінку.',
            ['Ні: викладає син, а нова версія називає викладачкою жінку.'], ['Her son teaches chemistry.', 'who teaches chemistry.'],
            ['Так: who автоматично включає й сина цієї жінки.']);
        $this->reviewed($t['m81-relative-clauses-linking-words-sentence-office'], 'The office which I visited was empty.',
            ['The office which I visited was empty.', 'The office that I visited was empty.'],
            ['I visited an office. The office was empty.', 'явним означальним займенником', 'не точність розстановки ком'],
            ['The office where I visited was empty.', 'The office which visited was empty.', 'The office I visited was empty.']);
        $this->reviewed($t['m81-relative-clauses-linking-words-sentence-despite'], 'Despite being tired, Danylo finished the report.',
            ['Despite being tired, Danylo finished the report.', 'In spite of being tired, Danylo finished the report.'],
            ['Although Danylo was tired, he finished the report.', 'той самий Данило', 'форму на -ing'],
            ['Despite being tired, Marta finished the report.', 'Because Danylo was tired, he finished the report.',
                'Despite of being tired, Danylo finished the report.']);
        self::assertSame('Despite the fact that Marta was tired, Olena finished the report.',
            $lesson['details'][0]['fragment']['data']['sections'][0]['examples'][1]['en']);
        self::assertStringContainsString('не доводить', $lesson['blocks'][1]['data']['sections'][3]['note']);
    }

    public function test_articles_quantifiers_preserve_nonzero_amount_exact_advice_unit_and_negative_observation(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][2]; $t = $this->tasks($lesson);
        $this->reviewed($t['m81-articles-quantifiers-form-unit'], 'pieces of advice', ['pieces of advice'],
            ['дві окремі корисні рекомендації', 'two useful'], ['advices', 'piece of advice']);
        $this->reviewed($t['m81-articles-quantifiers-form-any'], 'any', ['any'],
            ['не отримала жодних', 'допоміжне заперечення вже є', 'yesterday'], ['many', 'a']);
        $this->reviewed($t['m81-articles-quantifiers-meaning-advice-number'], 'Ні: exactly one додає невідомий факт про кількість.',
            ['Ні: exactly one додає невідомий факт про кількість.'], ['some useful advice', 'більше про кількість нічого не відомо'],
            ['Так: some advice завжди означає рівно одну пораду.']);
        $this->reviewed($t['m81-articles-quantifiers-meaning-nonzero'], 'Ні: перша версія сама по собі не встановлює, що стільці ще є.',
            ['Ні: перша версія сама по собі не встановлює, що стільці ще є.'], ['Інших фактів про їхню кількість немає', 'only a few'],
            ['Так: not many завжди точно дорівнює only a few.']);
        $this->reviewed($t['m81-articles-quantifiers-sentence-no-errors'], 'I found no errors in the report.',
            ['I found no errors in the report.'], ['I didn’t find any errors in the report.', 'минул', 'об’єктивно не існувало'],
            ['There were no errors in the report.', 'I find no errors in the report.', 'I did not find no errors in the report.']);
        $this->reviewed($t['m81-articles-quantifiers-sentence-one-advice'], 'She gave me exactly one useful piece of advice about packing.',
            ['She gave me exactly one useful piece of advice about packing.', 'She gave me just one useful piece of advice about packing.',
                'She gave me only one useful piece of advice about packing.'],
            ['exactly one useful suggestion about packing.', 'хто дав її кому', 'тему'],
            ['She gave me some useful advice about packing.', 'She gave me two useful pieces of advice about packing.',
                'She gave him exactly one useful piece of advice about packing.']);
        self::assertStringContainsString('достатність не випливає', $lesson['blocks'][1]['data']['sections'][1]['description']);
        self::assertSame('She gave me some advice about packing.', $lesson['details'][0]['fragment']['data']['sections'][0]['examples'][0]['en']);
        self::assertStringContainsString('не оголошено рівнозначними', $lesson['details'][0]['fragment']['data']['sections'][0]['note']);
    }

    public function test_emphasis_preserves_only_scope_tense_explicit_cleft_contrast_and_fact_not_intensity(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][3]; $t = $this->tasks($lesson);
        $this->reviewed($t['m81-word-order-emphasis-form-only-after'], 'did I understand', ['did I understand'],
            ['Почни з Only after', 'Past Simple', 'в головній частині'], ['I understood', 'had I understood']);
        $this->reviewed($t['m81-word-order-emphasis-form-emphatic-does'], 'does call', ['does call'],
            ['Ольга не телефонує', 'теперішню звичку', 'не змінюй часу'], ['does calls', 'did call']);
        $this->reviewed($t['m81-word-order-emphasis-meaning-only-subject'], 'Інверсія не потрібна: only входить до підмета Only Tom.',
            ['Інверсія не потрібна: only входить до підмета Only Tom.'], ['Only Tom understood the rule.', 'підметової групи'],
            ['Після будь-якого початкового only обов’язково ставимо did перед Tom.']);
        $this->reviewed($t['m81-word-order-emphasis-meaning-intensity'],
            'Ні: підтвердження наявності бажання не обов’язково передає ту саму його силу.',
            ['Ні: підтвердження наявності бажання не обов’язково передає ту саму його силу.'],
            ['саме сильне бажання', 'ніхто не сумнівається', 'I really want to help.', 'I do want to help.'],
            ['Так: do завжди означає дуже сильне бажання.']);
        $this->reviewed($t['m81-word-order-emphasis-sentence-cleft-contrast'], 'It was Tom who broke the printer, not me.',
            ['It was Tom who broke the printer, not me.', 'It was Tom that broke the printer, not me.'],
            ['Tom broke the printer, not me.', 'прямо збережи', 'не додавай значення «тільки»'],
            ['It was Tom who broke the printer.', 'It was only Tom who broke the printer, not me.', 'It was me who broke the printer, not Tom.']);
        $this->reviewed($t['m81-word-order-emphasis-sentence-only-after'], 'Only after I read the example did I understand the rule.',
            ['Only after I read the example did I understand the rule.'],
            ['I understood the rule only after I read the example.', 'Past Simple', 'не всередині after'],
            ['Only after did I read the example I understood the rule.', 'Only after I read the example had I understood the rule.',
                'Only after I read the example did I understood the rule.']);
        self::assertStringContainsString('тільки', $lesson['blocks'][1]['data']['sections'][1]['note']);
        self::assertSame('Do be careful.', $lesson['blocks'][1]['data']['sections'][2]['examples'][1]['en']);
        self::assertStringContainsString('не оголошуємо всі літературні інверсії',
            $lesson['blocks'][3]['data']['items'][3]['note']);
    }

    public function test_articles_diagnoses_context_pronunciation_school_and_correct_initial_sentence(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][4]; $t = $this->tasks($lesson);
        $this->reviewed($t['m81-articles-common-mistakes-form-mba-sound'], 'an', ['an'],
            ['«ем-бі-ей»', 'перший звук голосний', 'неозначений артикль'], ['a', 'the']);
        $this->reviewed($t['m81-articles-common-mistakes-form-general-coffee'], 'Без артикля', ['Без артикля'],
            ['напій загалом', 'не певну порцію або сорт', 'I drink ___ coffee every morning.'], ['the', 'a', '', ' ']);
        $this->reviewed($t['m81-articles-common-mistakes-meaning-already-correct'],
            'Залишити без змін: конкретну каву визначено в ситуації.',
            ['Залишити без змін: конкретну каву визначено в ситуації.'], ['одну конкретну чашку', 'The coffee in this cup is cold.'],
            ['Прибрати the: перед coffee артикль ніколи не можливий.']);
        $this->reviewed($t['m81-articles-common-mistakes-meaning-school-building'], 'Ні: the зберігає посилання на визначену школу.',
            ['Ні: the зберігає посилання на визначену школу.'], ['дорослий відвідувач', 'не навчатися', 'to meet the headteacher.'],
            ['Так: school у будь-якому значенні вживається без артикля.']);
        $this->reviewed($t['m81-articles-common-mistakes-sentence-one-teacher'], 'I saw a teacher at the station.',
            ['I saw a teacher at the station.'], ['слухач не може ідентифікувати', 'Мінімально', 'I saw teacher at the station.'],
            ['I saw the teacher at the station.', 'I see a teacher at the station.', 'I saw a teacher.']);
        $this->reviewed($t['m81-articles-common-mistakes-sentence-general-coffee'], 'I love coffee, but I do not drink it at night.',
            ['I love coffee, but I do not drink it at night.', "I love coffee, but I don't drink it at night."],
            ['напій загалом', 'лише зайвий артикль', 'I love the coffee, but I do not drink it at night.'],
            ['I love the coffee, but I do not drink it at night.', 'I love coffee, and I drink it at night.', 'I love coffee.']);
        self::assertSame([], $lesson['details']);
        self::assertStringContainsString('my', $lesson['blocks'][1]['data']['sections'][0]['description']);
        self::assertStringContainsString('не замінюй', $lesson['blocks'][1]['data']['sections'][0]['note']);
    }

    public function test_word_order_diagnoses_subject_embedded_be_modal_and_lexical_do_without_losing_negation(): void
    {
        [, , $master] = $this->release(); $lesson = $master['lessons'][5]; $t = $this->tasks($lesson);
        $this->reviewed($t['m81-word-order-common-mistakes-form-direct-going'], 'are you', ['are you'],
            ['прямо питає', 'не вкладена', 'не запит про підмет'], ['you are', 'do you']);
        $this->reviewed($t['m81-word-order-common-mistakes-form-negative-be'], 'is not', ['is not'],
            ['зараз не готова', 'be в Present Simple', 'повну форму'], ['does not be', 'not is']);
        $this->reviewed($t['m81-word-order-common-mistakes-meaning-subject-question'],
            'Залишити без змін: це правильне питання про підмет.', ['Залишити без змін: це правильне питання про підмет.'],
            ['Who є підметом', 'Who broke the window?'], ['Обов’язково додати did перед who в будь-якому питанні.']);
        $this->reviewed($t['m81-word-order-common-mistakes-meaning-embedded-main'],
            'Залишити без змін: головне Do you know та внутрішнє who called мають свої ролі.',
            ['Залишити без змін: головне Do you know та внутрішнє who called мають свої ролі.'],
            ['Початкове речення правильне', 'Do you know who called?'],
            ['Прибрати Do й переставити called перед who: після who завжди потрібна інверсія.']);
        $this->reviewed($t['m81-word-order-common-mistakes-sentence-embedded-station'], 'Can you tell me where the station is?',
            ['Can you tell me where the station is?'], ['Can you tell me where is the station?', 'лише порядок слів усередині', 'Збережи Can you tell me'],
            ['Can you tell me where is the station?', 'You can tell me where the station is.', 'Can you tell where the station is?']);
        $this->reviewed($t['m81-word-order-common-mistakes-sentence-negative-lexical'], 'He does not like spicy food.',
            ['He does not like spicy food.', "He doesn't like spicy food."], ['He not likes spicy food.', 'Збережи підмет', 'їжу й not'],
            ['He likes spicy food.', 'He does not likes spicy food.', 'He is not like spicy food.']);
        self::assertSame([], $lesson['details']);
        self::assertSame('The baby sleeps.', $lesson['blocks'][1]['data']['sections'][0]['examples'][1]['en']);
        self::assertSame('He cannot swim.', $lesson['blocks'][1]['data']['sections'][3]['examples'][1]['en']);
        self::assertStringContainsString('часто', $lesson['blocks'][1]['data']['sections'][1]['description']);
    }

    public function test_all_thirty_selected_widget_sentences_retain_native_case_multiplicity_and_topic_conditions(): void
    {
        $this->release(); $audit = Builder::loadAudit(dirname(__DIR__, 2));
        $expected = [
            0 => [
                'stcon-poly-b1-01' => ['Unless you hurry, you will be late.', 'Якщо ти не поспішиш, ти запізнишся.', 'не гарантує успіху'],
                'stcon-poly-b1-06' => ['I wish I had listened.', 'Шкода, що я не послухав.', 'вже минулу дію'],
                'stcon-poly-b1-07' => ['I would rather you did not smoke here.', 'Я б волів, щоб ти тут не курив.', 'іншого виконавця'],
                'stcon-poly-b1-08' => ['You had better leave now.', 'Тобі краще піти зараз.', 'небажаний наслідок'],
                'stcon-poly-b1-12' => ['I wish I knew the answer.', 'Шкода, що я не знаю відповіді.', 'теперішню ситуацію'],
            ],
            1 => [
                'strcl-poly-b1-02' => ['I bought a phone that works offline.', 'Я купив телефон, який працює без мережі.', 'не додавай повторний it'],
                'strcl-poly-b1-05' => ['The man standing near the door is my uncle.', 'Чоловік, який стоїть біля дверей, — мій дядько.', 'Скороти тільки означальну частину'],
                'strcl-poly-b1-06' => ['We stayed inside because it was cold.', 'Ми залишилися всередині, бо було холодно.', 'тієї самої причини'],
                'strcl-poly-b1-07' => ['Although it rained, we went out.', 'Хоча йшов дощ, ми вийшли.', 'Дощ ішов саме тоді'],
                'strcl-poly-b1-09' => ['I called him to ask a question.', 'Я подзвонив йому, щоб поставити запитання.', 'обидві дії виконує мовець'],
            ],
            2 => [
                'staq-poly-b1-03' => ['I need a piece of advice.', 'Мені потрібна одна порада.', 'одна порада'],
                'staq-poly-b1-05' => ['I have little time.', 'У мене мало часу.', 'наголошує на нестачі'],
                'staq-poly-b1-10' => ['We need some information.', 'Нам потрібна інформація.', 'не перетворюй інформацію на одну лічильну одиницю'],
                'staq-poly-b1-11' => ['We visited the museum.', 'Ми відвідали той музей.', 'співрозмовник знає'],
                'staq-poly-b1-12' => ['I have a few friends here.', 'У мене тут є кілька друзів.', 'достатність'],
            ],
            3 => [
                'stwoe-poly-b2-03' => ['Never had I seen such a view.', 'Я ніколи не бачив такого краєвиду до того моменту.', 'не змінюючи Past Perfect'],
                'stwoe-poly-b2-05' => ['I do understand your point.', 'Я справді розумію твою думку.', 'не додавай ступінь глибини'],
                'stwoe-poly-b2-08' => ['It was in June that we met.', 'Ми зустрілися саме в червні.', 'зміна акценту навмисна'],
                'stwoe-poly-b2-11' => ['Only then did I understand the problem.', 'Лише тоді я зрозумів проблему.', 'Past Simple'],
                'stwoe-poly-b2-12' => ['She did finish the report.', 'Вона справді завершила звіт.', 'Хтось помилково вважає'],
            ],
            4 => [
                'cma-poly-a2-02' => ['She has an old umbrella.', 'У неї є стара парасолька.', 'She has a old umbrella.'],
                'cma-poly-a2-03' => ['Please close the window in this room.', 'Будь ласка, зачини вікно в цій кімнаті.', 'єдине вікно'],
                'cma-poly-a2-04' => ['Dogs need regular exercise.', 'Собакам потрібні регулярні фізичні вправи.', 'не про окрему відому групу'],
                'cma-poly-a2-08' => ['He is a doctor.', 'Він лікар.', 'He is doctor.'],
                'cma-poly-a2-10' => ['She goes to school by bus.', 'Вона їздить до школи автобусом.', 'Збережи спосіб пересування'],
            ],
            5 => [
                'cmw-poly-a2-01' => ['She likes coffee.', 'Вона любить каву.', 'нейтральне твердження'],
                'cmw-poly-a2-03' => ['Do you speak English?', 'Ти говориш англійською?', 'не перепитування'],
                'cmw-poly-a2-04' => ['She does not like tea.', 'Вона не любить чай.', 'повну форму заперечення'],
                'cmw-poly-a2-05' => ['I often drink coffee.', 'Я часто п’ю каву.', 'не вилучай прислівник'],
                'cmw-poly-a2-12' => ['He never arrives late.', 'Він ніколи не приходить пізно.', 'один заперечний зміст'],
            ],
        ];
        foreach ($expected as $owner => $cases) {
            $samples = $audit['targets'][$owner]['first_five'];
            self::assertSame(array_keys($cases), array_column($samples, 'uuid'));
            foreach ($samples as $sample) {
                [$literal, $translation, $criterion] = $cases[$sample['uuid']];
                self::assertSame($literal, preg_replace('/\s+([?.!,;:])/', '$1', $sample['expected_word_key']));
                self::assertSame($literal, $sample['actual_correct_visible']);
                self::assertSame($literal, $sample['actual_selected_visible']);
                self::assertSame($translation, $sample['translation_uk']);
                self::assertStringContainsString($criterion, $sample['visible_condition_uk']);
                self::assertStringNotContainsString($literal, $sample['visible_condition_uk']);
                $tokens = ComposeTokenCase::normalize($sample['raw_correct_tokens']);
                self::assertSame($tokens, $sample['correct_tokens']);
                self::assertSame($literal, preg_replace('/\s+([?.!,;:])/', '$1', implode(' ', $tokens)));
                self::assertCount($sample['source_ordered_instance_count'], $tokens);
                self::assertSame([], $sample['token_presentations']);
                self::assertSame([], $sample['option_presentations']);
                self::assertFalse($sample['is_new']);
            }
        }
        // Known medial-casing defects remain literal and excluded, not repaired globally.
        self::assertSame(['The', 'office', 'Where', 'we', 'worked', '.'],
            ComposeTokenCase::normalize(['the', 'office', 'Where', 'we', 'worked', '.']));
        self::assertSame(['I', 'saw', 'Who', 'was', 'there', '.'],
            ComposeTokenCase::normalize(['I', 'saw', 'Who', 'was', 'there', '.']));
        self::assertNotContains('staq-poly-b1-06', array_column($audit['targets'][2]['first_five'], 'uuid'),
            'The existing no-token formatter defect is excluded, not repaired or falsely reported as a matching visible sentence.');
        self::assertSame(['There', 'are', 'no', 'seats', 'left', '.'],
            ComposeTokenCase::normalize(['there', 'are', 'no', 'seats', 'left', '.']));
    }

    public function test_sixteen_transformation_table_functions_have_two_real_bilingual_versions_and_reviewed_repairs(): void
    {
        [, , $master] = $this->release();
        $pairs = [
            0 => [
                ['If you don’t hurry, you’ll miss the train.', 'Unless you hurry, you’ll miss the train.', 'не гарантує'],
                ['I regret not studying harder last year.', 'I wish I had studied harder last year.', 'завершене минуле'],
                ['I’d prefer you not to call so late tonight.', 'I’d rather you didn’t call so late tonight.', 'Виконавець дзвінка — you'],
                ['You should leave now, or you may miss the last bus.', 'You had better leave now, or you may miss the last bus.', 'саме попереджає'],
            ],
            1 => [
                ['The woman lives next door. She teaches chemistry.', 'The woman who lives next door teaches chemistry.', 'Вона ж живе поруч'],
                ['Although he was tired, he finished the report.', 'In spite of being tired, he finished the report.', 'той самий чоловік'],
                ['The café is near the station. We met there.', 'The café where we met is near the station.', 'we лишається підметом'],
                ['She went to bed early because she had an exam the next morning.', 'She went to bed early because of her exam the next morning.', 'її запланований іспит'],
            ],
            2 => [
                ['There aren’t many chairs left. Only three remain.', 'There are only a few chairs left: three.', 'Саме three виключає нуль'],
                ['We have very little time left: just two minutes.', 'We don’t have much time left: just two minutes.', 'десять хвилин'],
                ['I didn’t see any mistakes.', 'I saw no mistakes.', 'об’єктивну безпомилковість'],
                ['She gave me some useful advice: exactly one suggestion about packing.', 'She gave me exactly one useful piece of advice about packing.', 'одну рекомендацію'],
            ],
            3 => [
                ['I had never seen such a confusing task before.', 'Never before had I seen such a confusing task.', 'Past Perfect'],
                ['Tom broke the printer, not me.', 'It was Tom who broke the printer, not me.', 'явний контраст not me'],
                ['I want to help.', 'I do want to help.', 'співрозмовник'],
                ['You should not press this button under any circumstances.', 'Under no circumstances should you press this button.', 'should'],
            ],
        ];
        foreach ($pairs as $owner => $expectedRows) {
            $rows = $master['lessons'][$owner]['blocks'][2]['data']['rows']; self::assertCount(4, $rows);
            foreach ($expectedRows as $i => [$source, $target, $context]) {
                $cells = $rows[$i]['cells']; self::assertCount(3, $cells);
                self::assertStringContainsString('<p lang="en">'.$source.'</p>', $cells[0]);
                self::assertStringContainsString('<p lang="en">'.$target.'</p>', $cells[1]);
                foreach ([0, 1] as $column) {
                    self::assertMatchesRegularExpression('/<p>[\s\S]*[\x{0400}-\x{04ff}][\s\S]*<\/p>/u', $cells[$column]);
                }
                self::assertStringContainsString($context, $cells[2]);
            }
        }
    }

    public function test_every_option_uses_literal_native_feedback_keys_and_no_invisible_criterion_or_empty_key(): void
    {
        [, , $master] = $this->release(); $seen = [];
        foreach ($master['lessons'] as $lesson) {
            $data = $lesson['blocks'][6]['data'];
            self::assertArrayNotHasKey('intro', $data);
            foreach (['select_intro', 'choice_intro', 'input_intro'] as $field) {
                self::assertMatchesRegularExpression('/[\x{0400}-\x{04ff}]/u', $data[$field]);
            }
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($data[$group] as $task) {
                    self::assertArrayNotHasKey($task['id'], $seen); $seen[$task['id']] = true;
                    self::assertArrayNotHasKey('prompt', $task);
                    $rawValues = $group === 'inputs' ? $task['accepted'] : $task['options'];
                    foreach ($rawValues as $value) {
                        self::assertNotSame('', trim($value));
                        self::assertArrayHasKey(mb_strtolower(trim($value)), $task['feedback'],
                            'Native feedback lookup does not normalize curly apostrophes.');
                    }
                    if ($group !== 'inputs') {
                        $normalized = array_map(Package::comparisonKey(...), $task['options']);
                        self::assertCount(count($normalized), array_unique($normalized));
                        self::assertCount(1, array_filter($normalized,
                            static fn (string $key): bool => $key === Package::comparisonKey($task['answer'])));
                    }
                    $keys = array_map(Package::comparisonKey(...), $task['accepted']);
                    foreach (['', ' ', "\t\n"] as $empty) {
                        self::assertNotContains(Package::comparisonKey($empty), $keys, 'Untouched/reset cannot earn a point.');
                    }
                }
            }
        }
        self::assertCount(36, $seen);
    }
}
