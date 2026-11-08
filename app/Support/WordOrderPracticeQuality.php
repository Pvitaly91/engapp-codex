<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Support\Database\QuestionUuidResolver;
use App\Models\Question;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Finite UK Word Order banks. Original banks, frozen releases and course metadata stay intact. */
final class WordOrderPracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'word-order-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'basic' => ['BasicWordOrder', 'BasicWordOrder'],
        'questions-negatives' => ['WordOrderQuestionsNegatives', 'WordOrderQuestionsNegatives'],
        'adverbs-adverbials' => ['WordOrderAdverbsAdverbials', 'WordOrderAdverbsAdverbials'],
        'verbs-objects' => ['WordOrderVerbsObjects', 'WordOrderVerbsObjects'],
        'advanced-emphasis' => ['AdvancedWordOrderEmphasis', 'AdvancedWordOrderEmphasis'],
        'inversion-basics' => ['InversionBasics', 'WordOrder/InversionBasics'],
        'negative-adverbial-inversion' => ['InversionAfterNegativeAdverbials', 'WordOrder/InversionAfterNegativeAdverbials'],
        'fronting-emphasis' => ['AdvancedFrontingAndEmphasis', 'WordOrder/AdvancedFrontingAndEmphasis'],
        'transformations' => ['SentenceTransformationsWordOrderEmphasis', 'SentenceTransformations/SentenceTransformationsWordOrderEmphasis'],
        'common-mistakes' => ['CommonMistakesWordOrder', 'CommonMistakes/CommonMistakesWordOrder'],
    ];

    /** Raw-byte pins bind metadata changes to the existing canonical originals, never arbitrary payloads. */
    private const ORIGINALS = [
        'basic' => [
            'standard' => ['wo-bwo-v3-test', '59e7b5944a46adb904eccec642f7ec8176b684c05cc4f2ed4f9379037ea426c2'],
            'builder' => ['wo-bwo-poly-test', '983bd94a4155b53b0890e5d150b82b70ba6476d2c444580bb03ca0cc71824847'],
        ],
        'questions-negatives' => [
            'standard' => ['wo-qn-v3-test', 'ddc9982ed9283456e3a2c2e2cf5fcabeef94bc61c17f352039b3aeaa5e710664'],
            'builder' => ['wo-qn-poly-test', 'b667ac4e4505da5ddc7c5689d2c5180f18cdd82e737f08f7dda98739491aa977'],
        ],
        'adverbs-adverbials' => [
            'standard' => ['wo-aa-v3-test', '9cc18569e620f9c961eb19584f055a803934e04be90621214bb0b1ae5fad870f'],
            'builder' => ['wo-aa-poly-test', '8699c7c756d0618e4babada756860053b6c184f378a92a99c71d3173eeca6fe0'],
        ],
        'verbs-objects' => [
            'standard' => ['wo-vo-v3-test', 'b53c216801335a17f4ef02d80860207e5ad44040eb75d8c532782b3e91388529'],
            'builder' => ['wo-vo-poly-test', '803c7bf5289dc35749d0eb6c1ca2211c58e0a49b934fd6da8bec8f0292fdb413'],
        ],
        'advanced-emphasis' => [
            'standard' => ['wo-adv-v3-test', '8cac6f8e197c0c87aa1a9b9ce66815f0419a367232716a4eb23347188d8e6578'],
            'builder' => ['wo-adv-poly-test', '49d1f0946e63312f52ba1e30fb910d1b6905f9a70b72712d4a0d9b68e3a6485a'],
        ],
        'inversion-basics' => [
            'standard' => ['ib-v3-all', '667580ac6a44591326e5b8fbd73aa88919bbeba19d4757e49ba8d8292ee523dd'],
            'builder' => ['ib-poly-all', '03ef015db10f63602f3b0eeb957b76f678a3a7df430c251cc72371d33947105c'],
        ],
        'negative-adverbial-inversion' => [
            'standard' => ['ina-v3-all', '00b58dfc5bb929cb8cc3c8ed6a18689ac124e27b30908466480dcb8e43a7aaa0'],
            'builder' => ['ina-poly-all', 'f37ae43a529977735bca0fa38b0243306bc1a26638ae520628f37b067f613a30'],
        ],
        'fronting-emphasis' => [
            'standard' => ['afe-v3-all', '69d0cbb11638de19ea664ba5ef550c44d0ce0ea942c6ad15b346fb5f76c8800b'],
            'builder' => ['afe-poly-all', 'e17bc63e121af1a324c1b2e3cd7d300e21781e2310b5e58eac786bd68bdd67d6'],
        ],
        'transformations' => [
            'standard' => ['stwoe-v3-all', 'dd562e1951b636deb2ac416a8039cda7db757b9c1ce13363a749b17b7a8a58a9'],
            'builder' => ['stwoe-poly-all', 'a15132299e275aac8b6e8d301110f1ad30074101cb687b3fd1509fe107a0726c'],
        ],
        'common-mistakes' => [
            'standard' => ['cmw-v3-all', '5bd149f62abaeacbc3389681f7086ba6e9348dd37fe9de56dc22bfaa5561799b'],
            'builder' => ['cmw-poly-all', 'de10c0c1d168e938c376b242f68fad25666c133fa9526f17e3cd45e9cc4f0653'],
        ],
    ];

    protected const UUID_PREFIX = 'woq-';
    protected const SOURCE_PREFIX = 'word_order_quality_';
    protected const NAMESPACE_PREFIX = 'word-order-quality-';
    protected const LABEL = 'Word Order Quality';
    protected const META_PREFIX = 'word_order_quality';
    public const CATEGORY_ID = 48;
    public const CATEGORY_SLUG = 'word-order';
    public const CATEGORY_TOPICS = ['basic', 'questions-negatives', 'adverbs-adverbials', 'verbs-objects',
        'advanced-emphasis', 'inversion-basics', 'negative-adverbial-inversion', 'fronting-emphasis'];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Word Order topic.'); }
        [$stem, $oldDirectory] = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/word-order-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'WordOrder' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? $oldDirectory.'AllLevelsV3Seeder' : 'Polyglot/Polyglot'.$stem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0],
                'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        return $map;
    }

}
