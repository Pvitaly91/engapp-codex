<?php

namespace Database\Seeders;

use App\Models\Translate;
use App\Models\Word;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IrregularVerbsSeeder extends Seeder
{
    public function run(): void
    {
        $verbs = [
            ['base' => 'be', 'past' => ['was', 'were'], 'participle' => 'been', 'translation' => 'бути'],
            ['base' => 'become', 'past' => 'became', 'participle' => 'become', 'translation' => 'ставати'],
            ['base' => 'begin', 'past' => 'began', 'participle' => 'begun', 'translation' => 'починати'],
            ['base' => 'bend', 'past' => 'bent', 'participle' => 'bent', 'translation' => 'гнути'],
            ['base' => 'bet', 'past' => 'bet', 'participle' => 'bet', 'translation' => 'закладатися'],
            ['base' => 'bite', 'past' => 'bit', 'participle' => 'bitten', 'translation' => 'кусати'],
            ['base' => 'bleed', 'past' => 'bled', 'participle' => 'bled', 'translation' => 'кровоточити'],
            ['base' => 'blow', 'past' => 'blew', 'participle' => 'blown', 'translation' => 'дути'],
            ['base' => 'break', 'past' => 'broke', 'participle' => 'broken', 'translation' => 'ламати'],
            ['base' => 'bring', 'past' => 'brought', 'participle' => 'brought', 'translation' => 'приносити'],
            ['base' => 'broadcast', 'past' => 'broadcast', 'participle' => 'broadcast', 'translation' => 'транслювати'],
            ['base' => 'build', 'past' => 'built', 'participle' => 'built', 'translation' => 'будувати'],
            ['base' => 'burn', 'past' => ['burned', 'burnt'], 'participle' => ['burned', 'burnt'], 'translation' => 'палити'],
            ['base' => 'buy', 'past' => 'bought', 'participle' => 'bought', 'translation' => 'купувати'],
            ['base' => 'catch', 'past' => 'caught', 'participle' => 'caught', 'translation' => 'ловити'],
            ['base' => 'choose', 'past' => 'chose', 'participle' => 'chosen', 'translation' => 'вибирати'],
            ['base' => 'come', 'past' => 'came', 'participle' => 'come', 'translation' => 'приходити'],
            ['base' => 'cost', 'past' => 'cost', 'participle' => 'cost', 'translation' => 'коштувати'],
            ['base' => 'cut', 'past' => 'cut', 'participle' => 'cut', 'translation' => 'різати'],
            ['base' => 'deal', 'past' => 'dealt', 'participle' => 'dealt', 'translation' => 'мати справу'],
            ['base' => 'dig', 'past' => 'dug', 'participle' => 'dug', 'translation' => 'копати'],
            ['base' => 'do', 'past' => 'did', 'participle' => 'done', 'translation' => 'робити'],
            ['base' => 'draw', 'past' => 'drew', 'participle' => 'drawn', 'translation' => 'малювати'],
            ['base' => 'drink', 'past' => 'drank', 'participle' => 'drunk', 'translation' => 'пити'],
            ['base' => 'drive', 'past' => 'drove', 'participle' => 'driven', 'translation' => 'керувати'],
            ['base' => 'eat', 'past' => 'ate', 'participle' => 'eaten', 'translation' => 'їсти'],
            ['base' => 'fall', 'past' => 'fell', 'participle' => 'fallen', 'translation' => 'падати'],
            ['base' => 'feed', 'past' => 'fed', 'participle' => 'fed', 'translation' => 'годувати'],
            ['base' => 'feel', 'past' => 'felt', 'participle' => 'felt', 'translation' => 'відчувати'],
            ['base' => 'fight', 'past' => 'fought', 'participle' => 'fought', 'translation' => 'битися'],
            ['base' => 'find', 'past' => 'found', 'participle' => 'found', 'translation' => 'знаходити'],
            ['base' => 'fly', 'past' => 'flew', 'participle' => 'flown', 'translation' => 'літати'],
            ['base' => 'forget', 'past' => 'forgot', 'participle' => 'forgotten', 'translation' => 'забувати'],
            ['base' => 'forgive', 'past' => 'forgave', 'participle' => 'forgiven', 'translation' => 'пробачати'],
            ['base' => 'freeze', 'past' => 'froze', 'participle' => 'frozen', 'translation' => 'замерзати'],
            ['base' => 'get', 'past' => 'got', 'participle' => ['got', 'gotten'], 'translation' => 'отримувати'],
            ['base' => 'give', 'past' => 'gave', 'participle' => 'given', 'translation' => 'давати'],
            ['base' => 'go', 'past' => 'went', 'participle' => 'gone', 'translation' => 'йти'],
            ['base' => 'grow', 'past' => 'grew', 'participle' => 'grown', 'translation' => 'рости'],
            ['base' => 'hang', 'past' => ['hung', 'hanged'], 'participle' => ['hung', 'hanged'], 'translation' => 'висіти'],
            ['base' => 'have', 'past' => 'had', 'participle' => 'had', 'translation' => 'мати'],
            ['base' => 'hear', 'past' => 'heard', 'participle' => 'heard', 'translation' => 'чути'],
            ['base' => 'hide', 'past' => 'hid', 'participle' => 'hidden', 'translation' => 'ховати'],
            ['base' => 'hit', 'past' => 'hit', 'participle' => 'hit', 'translation' => 'вдаряти'],
            ['base' => 'hold', 'past' => 'held', 'participle' => 'held', 'translation' => 'тримати'],
            ['base' => 'hurt', 'past' => 'hurt', 'participle' => 'hurt', 'translation' => 'боліти'],
            ['base' => 'keep', 'past' => 'kept', 'participle' => 'kept', 'translation' => 'тримати'],
            ['base' => 'know', 'past' => 'knew', 'participle' => 'known', 'translation' => 'знати'],
            ['base' => 'lead', 'past' => 'led', 'participle' => 'led', 'translation' => 'вести'],
            ['base' => 'leave', 'past' => 'left', 'participle' => 'left', 'translation' => 'залишати'],
            ['base' => 'lend', 'past' => 'lent', 'participle' => 'lent', 'translation' => 'позичати'],
            ['base' => 'let', 'past' => 'let', 'participle' => 'let', 'translation' => 'дозволяти'],
            ['base' => 'lie', 'past' => 'lay', 'participle' => 'lain', 'translation' => 'лежати'],
            ['base' => 'light', 'past' => ['lit', 'lighted'], 'participle' => ['lit', 'lighted'], 'translation' => 'запалювати'],
            ['base' => 'lose', 'past' => 'lost', 'participle' => 'lost', 'translation' => 'втрачати'],
            ['base' => 'make', 'past' => 'made', 'participle' => 'made', 'translation' => 'робити'],
            ['base' => 'mean', 'past' => 'meant', 'participle' => 'meant', 'translation' => 'означати'],
            ['base' => 'meet', 'past' => 'met', 'participle' => 'met', 'translation' => 'зустрічати'],
            ['base' => 'pay', 'past' => 'paid', 'participle' => 'paid', 'translation' => 'платити'],
            ['base' => 'put', 'past' => 'put', 'participle' => 'put', 'translation' => 'класти'],
            ['base' => 'read', 'past' => 'read', 'participle' => 'read', 'translation' => 'читати'],
            ['base' => 'ride', 'past' => 'rode', 'participle' => 'ridden', 'translation' => 'їхати верхи'],
            ['base' => 'ring', 'past' => 'rang', 'participle' => 'rung', 'translation' => 'дзвонити'],
            ['base' => 'rise', 'past' => 'rose', 'participle' => 'risen', 'translation' => 'підніматися'],
            ['base' => 'run', 'past' => 'ran', 'participle' => 'run', 'translation' => 'бігти'],
            ['base' => 'say', 'past' => 'said', 'participle' => 'said', 'translation' => 'говорити'],
            ['base' => 'see', 'past' => 'saw', 'participle' => 'seen', 'translation' => 'бачити'],
            ['base' => 'sell', 'past' => 'sold', 'participle' => 'sold', 'translation' => 'продавати'],
            ['base' => 'send', 'past' => 'sent', 'participle' => 'sent', 'translation' => 'надсилати'],
            ['base' => 'set', 'past' => 'set', 'participle' => 'set', 'translation' => 'встановлювати'],
            ['base' => 'shake', 'past' => 'shook', 'participle' => 'shaken', 'translation' => 'трясти'],
            ['base' => 'shine', 'past' => ['shone', 'shined'], 'participle' => ['shone', 'shined'], 'translation' => 'світити'],
            ['base' => 'shoot', 'past' => 'shot', 'participle' => 'shot', 'translation' => 'стріляти'],
            ['base' => 'show', 'past' => 'showed', 'participle' => ['shown', 'showed'], 'translation' => 'показувати'],
            ['base' => 'shut', 'past' => 'shut', 'participle' => 'shut', 'translation' => 'закривати'],
            ['base' => 'sing', 'past' => 'sang', 'participle' => 'sung', 'translation' => 'співати'],
            ['base' => 'sink', 'past' => 'sank', 'participle' => 'sunk', 'translation' => 'тонути'],
            ['base' => 'sit', 'past' => 'sat', 'participle' => 'sat', 'translation' => 'сидіти'],
            ['base' => 'sleep', 'past' => 'slept', 'participle' => 'slept', 'translation' => 'спати'],
            ['base' => 'speak', 'past' => 'spoke', 'participle' => 'spoken', 'translation' => 'говорити'],
            ['base' => 'spend', 'past' => 'spent', 'participle' => 'spent', 'translation' => 'витрачати'],
            ['base' => 'stand', 'past' => 'stood', 'participle' => 'stood', 'translation' => 'стояти'],
            ['base' => 'steal', 'past' => 'stole', 'participle' => 'stolen', 'translation' => 'красти'],
            ['base' => 'stick', 'past' => 'stuck', 'participle' => 'stuck', 'translation' => 'приклеювати'],
            ['base' => 'swim', 'past' => 'swam', 'participle' => 'swum', 'translation' => 'плавати'],
            ['base' => 'take', 'past' => 'took', 'participle' => 'taken', 'translation' => 'брати'],
            ['base' => 'teach', 'past' => 'taught', 'participle' => 'taught', 'translation' => 'вчити'],
            ['base' => 'tear', 'past' => 'tore', 'participle' => 'torn', 'translation' => 'рвати'],
            ['base' => 'tell', 'past' => 'told', 'participle' => 'told', 'translation' => 'розповідати'],
            ['base' => 'think', 'past' => 'thought', 'participle' => 'thought', 'translation' => 'думати'],
            ['base' => 'throw', 'past' => 'threw', 'participle' => 'thrown', 'translation' => 'кидати'],
            ['base' => 'understand', 'past' => 'understood', 'participle' => 'understood', 'translation' => 'розуміти'],
            ['base' => 'wake', 'past' => 'woke', 'participle' => 'woken', 'translation' => 'прокидатися'],
            ['base' => 'wear', 'past' => 'wore', 'participle' => 'worn', 'translation' => 'носити'],
            ['base' => 'win', 'past' => 'won', 'participle' => 'won', 'translation' => 'вигравати'],
            ['base' => 'write', 'past' => 'wrote', 'participle' => 'written', 'translation' => 'писати'],
        ];

        DB::transaction(function () use ($verbs) {
            foreach ($verbs as $verb) {
                $forms = [
                    'base' => (array) ($verb['base'] ?? []),
                    'past' => (array) ($verb['past'] ?? []),
                    'participle' => (array) ($verb['participle'] ?? []),
                ];

                foreach ($forms as $type => $words) {
                    foreach ($words as $form) {
                        $word = Word::updateOrCreate(
                            ['word' => $form],
                            ['type' => $type]
                        );

                        Translate::updateOrCreate(
                            ['word_id' => $word->id, 'lang' => 'uk'],
                            ['translation' => $verb['translation']]
                        );
                    }
                }
            }
        });
    }
}

