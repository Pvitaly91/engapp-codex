<?php

namespace App\Support;

use InvalidArgumentException;

/** Exact UK Verb to Be banks; historical questions and course metadata remain intact. */
final class VerbToBePracticeQuality extends FinitePracticeQualityCatalog
{
    public const REVISION = 'verb-to-be-v1';
    public const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    public const TOPICS = [
        'present' => ['VerbToBePresent', 'VerbToBePresentForms', 'VerbToBePresent'],
        'negatives' => ['VerbToBeNegatives', 'VerbToBeNegatives', 'VerbToBeNegatives'],
        'questions' => ['VerbToBeQuestions', 'VerbToBeQuestionsAndShortAnswers', 'VerbToBeQuestions'],
        'past' => ['VerbToBePast', 'VerbToBePastForms', 'VerbToBePast'],
        'future' => ['VerbToBeFuture', 'VerbToBeFuture', 'VerbToBeFuture'],
        'there' => ['ThereIsThereAre', 'ThereIsThereAre', 'ThereIsThereAre'],
    ];
    protected const UUID_PREFIX = 'beq-';
    protected const SOURCE_PREFIX = 'verb_to_be_quality_';
    protected const NAMESPACE_PREFIX = 'verb-to-be-quality-';
    protected const LABEL = 'Verb to Be Quality';
    protected const META_PREFIX = 'verb_to_be_quality';
    public const CATEGORY_ID = 8;
    public const CATEGORY_SLUG = 'verb-to-be';
    public const CATEGORY_TOPICS = ['present', 'negatives', 'questions', 'past', 'future', 'there'];

    private const ORIGINALS = [
        'present' => [
            'standard' => ['verb-to-be-pres-opus46-saved', '58dad072c07924c7944516e8a8ff42a4fb869fa7a8f1a8d918b60c0c736be51d'],
            'builder' => ['poly-070a16a11f67f50e', '3b8010923c3ce43fab74f5889aaa058308b4643c8199fa08562281abb67f6d6a'],
        ],
        'negatives' => [
            'standard' => ['verb-to-be-neg-opus46-v3-saved', '4e3d4b9afb24e6cdb2cad0e270884ae7f7c9289de18e78b36f25f9a2f284f2e2'],
            'builder' => ['poly-7da5537d776a1bad', '5c9905be8231a3e6e4485bded03051d86aef1620c01f4fc3501b1f250bbbb0a1'],
        ],
        'questions' => [
            'standard' => ['verb-tobe-q-opus46-v3-saved', '5591e6cb2d73dce9bc8dc34d54dcbfb54c6fe0d8c12de8ca832995bce7e92187'],
            'builder' => ['poly-f679962ce035b1f1', '45f4233a5c084633b1d2fba80efa5e4cc9df4f4e369e1034ee2c8415e78839e6'],
        ],
        'past' => [
            'standard' => ['vtbp-opus46-v3-saved', 'bc7d7d54a7c361b3e09d0cf50667937c323ba9eaa513ddf30abe63f250074827'],
            'builder' => ['poly-50f640f1ce7c3774', 'cb83d9637fdfbc491f29616eba7057bbcd8729e08c0cced2c432a0cdff503ee8'],
        ],
        'future' => [
            'standard' => ['verb-to-be-future-opus46-saved', '24dfba387115b283e79278e49ba395817a8d033b071e3888e9560da9e3a476c9'],
            'builder' => ['poly-8398291f3662844d', 'b8c6e6b15970f779c7dedf721c48fa591554291ff6868554f6a647a76379fef1'],
        ],
        'there' => [
            'standard' => ['there-is-are-opus46-saved', '4447dbcf89f26d9324032a071a2ae5999e9b9d1a6ed6763c61578b86754027cd'],
            'builder' => ['poly-d0d3d56203f06fe5', '37dfa33ea0abb6eafa5117b0a416461a9b99fc24a99807890fcc7ce894e9eaf4'],
        ],
    ];

    public static function mapping(string $topic): array
    {
        if (!isset(self::TOPICS[$topic])) { throw new InvalidArgumentException('Unknown Verb to Be topic.'); }
        [$stem, $standardStem, $builderStem] = self::TOPICS[$topic];
        $map = ['topic' => $topic, 'stem' => $stem, 'author' => 'docs/content/verb-to-be-quality/'.$topic.'.v1.json'];
        foreach (['standard', 'builder'] as $kind) {
            $namespace = $kind === 'standard' ? 'VerbToBe' : 'Polyglot';
            $name = 'UkrainianMixed'.$stem.'Quality'.($kind === 'standard' ? 'Standard' : 'Builder').'Seeder';
            $directory = 'database/seeders/V3/'.$namespace.'/'.$name;
            $oldName = $kind === 'standard' ? 'AI/Copilot/Opus46/'.$standardStem.'V3QuestionsOnlySeeder'
                : 'Polyglot/Polyglot'.$builderStem.'AllLevelsLessonSeeder';
            $map[$kind] = ['class' => 'Database\\Seeders\\V3\\'.$namespace.'\\'.$name,
                'definition' => $directory.'/definition.json', 'loader' => $directory.'.php',
                'implementation' => $directory.'/'.$name.'.php',
                'old_definition' => 'database/seeders/V3/'.$oldName.'/definition.json',
                'old_class' => 'Database\\Seeders\\V3\\'.str_replace('/', '\\', $oldName),
                'old_saved_test_uuid' => self::ORIGINALS[$topic][$kind][0],
                'old_sha256' => self::ORIGINALS[$topic][$kind][1]];
        }
        // These two additional page-local standard sources are part of the
        // existing aggregate. Route only their UK mixed selection to the same
        // reviewed bank; their standalone filters/questions and other locales stay untouched.
        $extra = match ($topic) {
            'present' => ['VerbToBePresentForms', '105aa7fb94d644e2c071c216eef02064f48418d892695c4a41ce65cbaee07b70'],
            'future' => ['VerbToBeFuture', '9bfd4742da933455a9c1a0080fc35c64e55640828fe1057d6f937759b976ad3e'],
            default => null,
        };
        if ($extra !== null) {
            $class = 'Database\\Seeders\\V3\\AI\\Copilot\\Sonate\\'.$extra[0].'V3QuestionsOnlySeeder';
            $path = 'database/seeders/V3/AI/Copilot/Sonate/'.$extra[0].'V3QuestionsOnlySeeder/definition.json';
            $map['additional_originals'] = [['path' => $path, 'sha256' => $extra[1], 'class' => $class]];
            $map['additional_locale_overrides'] = [$class => $map['standard']['class']];
        }
        return $map;
    }
}
