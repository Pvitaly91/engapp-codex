<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sentence;
use Illuminate\Support\Str;

class SentenceTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sentences = [
            ['uk' => 'З ким вона розмовляла вчора ?', 'en' => 'Who was she talking to yesterday?'],
            ['uk' => 'Про що ця книга?', 'en' => 'What is this book about?'],
            ['uk' => 'Де він працює?', 'en' => 'Where does he work?'],
            ['uk' => 'Як вона добирається додому ?', 'en' => 'How does she get home?'],
            ['uk' => 'Коли він прокидається?', 'en' => 'When does he wake up?'],
            ['uk' => 'З ким вона зустрічалася вчора?', 'en' => 'Who did she meet yesterday?'],
            ['uk' => 'О котрій годині починається фільм', 'en' => 'What time does the movie start?'],
            ['uk' => 'Коли він буде на роботі?', 'en' => 'When will he be at work?'],
            ['uk' => 'З ким вона розмовляла вчора?', 'en' => 'Who was she talking to yesterday?'],
            ['uk' => 'Де вони працюють?', 'en' => 'Where do they work?'],
            ['uk' => 'О котрій годині він закінчує роботу?', 'en' => 'What time does he finish work?'],
            ['uk' => 'Що вона робить кожен день?', 'en' => 'What does she do every day?'],
            ['uk' => 'З ким він зустрічався вчора?', 'en' => 'Who did he meet yesterday?'],
            ['uk' => 'Про що вони розмовляли минулого вечора?', 'en' => 'What were they talking about last night?'],
            ['uk' => 'Де його книжка?', 'en' => 'Where is his book?'],
            ['uk' => 'Якого кольору її машина?', 'en' => 'What color is her car?'],
            ['uk' => 'Він живе в іншій країні.', 'en' => 'He lives in another country.'],
            ['uk' => 'Вони не прибирають кожного дня.', 'en' => "They don't clean every day."],
            ['uk' => 'Мій брат не дзвонив вчора.', 'en' => "My brother didn't call yesterday."],
            ['uk' => 'Вона слухала музику минулого вечора.', 'en' => 'She was listening to music last night.'],
        ];

        foreach ($sentences as $i => $row) {
            Sentence::create([
                'uuid' => Str::slug(class_basename(self::class)) . '-' . ($i + 1),
                'text_uk' => $row['uk'],
                'text_en' => $row['en'],
            ]);
        }
    }
}
