<?php

namespace Tests\Unit;

use App\Services\TheoryMenuStore;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\Support\IsolatedTestEnvironment;

final class TheoryMenuStoreTest extends TestCase
{
    private string $root;

    private TheoryMenuStore $store;

    protected function setUp(): void
    {
        $this->root = IsolatedTestEnvironment::prepare().'/app/theory-menu-store-'.bin2hex(random_bytes(8));
        $this->store = new TheoryMenuStore($this->root, ['uk', 'pl']);
    }

    public function test_publication_is_content_addressed_and_explicit_old_revisions_survive_rollback(): void
    {
        $first = $this->build('first');
        self::assertSame('published', $first['status']);
        self::assertSame($first['revision'], $this->store->active('development', 'uk')['revision']);
        self::assertSame($first['revision'], $this->store->check('development', 'uk')['revision']);
        $repeat = $this->build('first');
        self::assertSame('unchanged', $repeat['status']);
        self::assertSame($first['revision'], $repeat['revision']);
        self::assertCount(1, $this->activationPaths());

        $second = $this->build('second');
        self::assertNotSame($first['revision'], $second['revision']);
        foreach (TheoryMenuStore::VARIANTS as $variant) {
            self::assertSame($variant.' first', $this->store->read('development', 'uk', $variant, $first['revision'])['html']);
            self::assertSame($variant.' second', $this->store->read('development', 'uk', $variant)['html']);
        }
        self::assertSame($first['revision'], $this->store->rollback('development', 'uk')['revision']);
        self::assertSame($first['revision'], $this->store->active('development', 'uk')['revision']);
        self::assertSame('mobile second', $this->store->read('development', 'uk', 'mobile', $second['revision'])['html']);
    }

    public function test_failed_generation_keeps_both_current_variants_and_never_activates_partial_files(): void
    {
        $first = $this->build('first');
        foreach ([
            static function (): array { throw new RuntimeException('Synthetic renderer failure.'); },
            static fn (): array => ['fragments' => ['desktop' => 'generated', 'mobile' => '  '], 'counts' => ['categories' => 1, 'lessons' => 1]],
        ] as $bad) {
            $error = null;
            try { $this->store->build('development', 'uk', $bad); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error);
            self::assertCount(1, $this->activationPaths());
            self::assertSame($first['revision'], $this->store->check('development', 'uk')['revision']);
            self::assertSame('desktop first', $this->store->read('development', 'uk', 'desktop')['html']);
            self::assertSame('mobile first', $this->store->read('development', 'uk', 'mobile')['html']);
        }
    }

    public function test_corrupt_pair_falls_back_within_context_and_can_be_repaired_without_a_new_content_revision(): void
    {
        $first = $this->build('first');
        $second = $this->build('second');
        $paths = $this->activationPaths();
        $activation = json_decode(file_get_contents($paths[0]), true, flags: JSON_THROW_ON_ERROR);
        $mobile = $this->root.'/development/uk/theory/releases/'.$activation['release'].'/mobile.html';
        file_put_contents($mobile, '');

        self::assertNull($this->store->check('development', 'uk'));
        self::assertNull($this->store->read('development', 'uk', 'desktop', $second['revision']));
        $fallback = $this->store->active('development', 'uk');
        self::assertTrue($fallback['fallback']);
        self::assertSame($first['revision'], $fallback['revision']);
        self::assertSame('mobile first', $this->store->read('development', 'uk', 'mobile')['html']);
        self::assertNull($this->store->read('production', 'uk', 'mobile'));
        self::assertNull($this->store->read('development', 'pl', 'desktop'));

        $repair = $this->build('second');
        self::assertSame('published', $repair['status']);
        self::assertSame($second['revision'], $repair['revision']);
        self::assertSame('mobile second', $this->store->read('development', 'uk', 'mobile', $second['revision'])['html']);
        self::assertFalse($this->store->active('development', 'uk')['fallback']);

        $activation = json_decode(file_get_contents($this->activationPaths()[0]), true, flags: JSON_THROW_ON_ERROR);
        $mobile = $this->root.'/development/uk/theory/releases/'.$activation['release'].'/mobile.html';
        file_put_contents($mobile, str_repeat('x', strlen('mobile second')));
        self::assertNull($this->store->read('development', 'uk', 'mobile', $second['revision']));
        self::assertNull($this->store->check('development', 'uk'));
    }

    public function test_context_and_revision_inputs_cannot_select_arbitrary_paths(): void
    {
        $this->build('first');
        foreach ([
            ['../development', 'uk', 'desktop', null, 'theory'], ['development', 'uk/../../pl', 'desktop', null, 'theory'],
            ['development', 'zz', 'desktop', null, 'theory'], ['development', 'uk', '../desktop', null, 'theory'],
            ['development', 'uk', 'desktop', '../current.json', 'theory'], ['development', 'uk', 'desktop', null, '../theory'],
            ['development', 'uk', 'tablet', null, 'theory'], ['development', 'uk', 'desktop', str_repeat('a', 64), 'theory'],
        ] as $args) self::assertNull($this->store->read(...$args));
    }

    public function test_parallel_builders_serialize_generation_and_publish_consistent_pairs(): void
    {
        $worker = <<<'PHP'
require $argv[1];
$store = new App\Services\TheoryMenuStore($argv[2], ['uk']);
$label = $argv[3];
$result = $store->build('development', 'uk', function () use ($argv, $label) {
    file_put_contents($argv[2].'/trace', "start-$label\n", FILE_APPEND);
    usleep(150000);
    file_put_contents($argv[2].'/trace', "end-$label\n", FILE_APPEND);
    return ['fragments' => ['desktop' => 'desktop '.$label, 'mobile' => 'mobile '.$label], 'counts' => ['categories' => 1, 'lessons' => 1]];
});
echo $result['revision'];
PHP;
        $jobs = [];
        foreach (['alpha', 'beta'] as $label) {
            $jobs[$label] = new Process([PHP_BINARY, '-r', $worker, dirname(__DIR__, 2).'/vendor/autoload.php', $this->root, $label], timeout: 10);
            $jobs[$label]->start();
        }
        try {
            foreach ($jobs as $label => $job) {
                self::assertSame(0, $job->wait(), $job->getErrorOutput());
                $revision = trim($job->getOutput());
                self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $revision);
                self::assertSame('desktop '.$label, $this->store->read('development', 'uk', 'desktop', $revision)['html']);
                self::assertSame('mobile '.$label, $this->store->read('development', 'uk', 'mobile', $revision)['html']);
            }
        } finally {
            foreach ($jobs as $job) if ($job->isRunning()) $job->stop(0.2);
        }
        self::assertContains(file($this->root.'/trace', FILE_IGNORE_NEW_LINES), [
            ['start-alpha', 'end-alpha', 'start-beta', 'end-beta'], ['start-beta', 'end-beta', 'start-alpha', 'end-alpha'],
        ]);
        self::assertCount(2, $this->activationPaths());
        self::assertNotNull($this->store->check('development', 'uk'));
    }

    private function build(string $label): array
    {
        return $this->store->build('development', 'uk', static fn () => ['fragments' => ['desktop' => 'desktop '.$label, 'mobile' => 'mobile '.$label],
            'counts' => ['categories' => 1, 'lessons' => 1]]);
    }

    private function activationPaths(): array
    {
        $paths = glob($this->root.'/development/uk/theory/activations/*.json') ?: [];
        rsort($paths, SORT_STRING);

        return $paths;
    }
}
