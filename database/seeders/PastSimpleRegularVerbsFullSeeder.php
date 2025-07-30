<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastSimpleRegularVerbsFullSeeder extends Seeder
{

    public function run()
    {
        $cat_past = 1;
        $source1 = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs - 1'])->id;
        $source2 = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs – Positive'])->id;
        $source3 = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs – Negative'])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'past_simple_regular_verbs_full']);

        // A) Write the past forms of the regular verbs below.
        $verbs = [
            ['play', 'played'],
            ['study', 'studied'],
            ['wash', 'washed'],
            ['die', 'died'],
            ['paint', 'painted'],
            ['watch', 'watched'],
            ['try', 'tried'],
            ['start', 'started'],
            ['phone', 'phoned'],
            ['stay', 'stayed'],
            ['love', 'loved'],
            ['help', 'helped'],
            ['carry', 'carried'],
            ['walk', 'walked'],
            ['skip', 'skipped'],
            ['answer', 'answered'],
            ['enjoy', 'enjoyed'],
            ['cry', 'cried'],
            ['laugh', 'laughed'],
            ['invite', 'invited'],
            ['visit', 'visited'],
            ['move', 'moved'],
            ['tidy', 'tidied'],
            ['like', 'liked'],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($verbs as $i => [$inf, $past]) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $items[] = [
                'uuid'        => $uuid,
                'question'    => ucfirst($inf) . ' → {a1} (past form)',
                'difficulty'  => 1,
                'category_id' => $cat_past,
                'flag'        => 0,
                'source_id'   => $source1,
                'tag_ids'     => [$themeTag->id],
                'answers'     => [
                    ['marker' => 'a1', 'answer' => $past, 'verb_hint' => $inf],
                ],
                'options'     => [$past, $inf],
            ];
        }

        $service->seed($items);
    }
}
