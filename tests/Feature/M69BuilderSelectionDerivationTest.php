<?php

namespace Tests\Feature;

use App\Support\M68BuilderSelectionPresentation;
use App\Support\M69BuilderSelectionPresentation;
use App\Support\TheoryBuilderSelectionPresentation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/** Pure source derivation from frozen M68; no application bootstrap, renderer, formatter, DB or HTTP. */
class M69BuilderSelectionDerivationTest extends TestCase
{
    public function test_generic_selection_is_exact_m68_extraction_with_only_trusted_profile_and_clone_provenance_changes(): void
    {
        $root = dirname(__DIR__, 2);
        $source = file_get_contents($root.'/app/Support/M68BuilderSelectionPresentation.php');
        self::assertSame('5d4fd6df845da57c95e7523c21b23a4f0894714c7a224e7dcae5a95b035f7b53', hash('sha256', $source));
        self::assertSame('56792701b0816823f2c52085d12e95eab29147fe31bee2116e1197fd31d80d8b',
            hash_file('sha256', $root.'/tests/Feature/M68BuilderSelectionPresentationTest.php'), 'Accepted M68 assertions stay unchanged.');
        $replace = static function (string $old, string $new, int $count = 1) use (&$source): void {
            self::assertSame($count, substr_count($source, $old), 'Bounded extraction: '.substr($old, 0, 100));
            $source = str_replace($old, $new, $source);
        };
        $replace("/** Finite M68 selection and condition-only clones; never grades, formats tokens, queries or mutates original models. */\nfinal class M68BuilderSelectionPresentation",
            "/** Exact, package-bound selection and condition-only clones; no grading, token formatting, queries or original-model writes. */\nabstract class TheoryBuilderSelectionPresentation");
        self::assertSame(1, substr_count($source, '    public const AUDIT ='));
        self::assertSame(1, substr_count($source, '    private static ?WeakMap'));
        $first = strpos($source, '    public const AUDIT ='); $last = strpos($source, '    private static ?WeakMap', $first);
        self::assertGreaterThan($first, $last);
        $source = substr($source, 0, $first)
            ."    /** Trusted PHP package and finite audit policy; never selected from learner content. */\n"
            ."    abstract protected static function packageClass(): string;\n\n"
            ."    abstract protected static function policy(): array;\n\n".substr($source, $last);
        $replace('self::$canonicalClones[$copy]=true', 'self::$canonicalClones[$copy]=static::class');
        $replace('if (self::$canonicalClones===null || !isset(self::$canonicalClones[$block]))',
            "if (self::\$canonicalClones===null || !isset(self::\$canonicalClones[\$block])\n            || self::\$canonicalClones[\$block]!==static::class)");
        $replace("public static function validateAudit(array \$audit,array \$patch,array \$contract): void\n    {",
            "public static function validateAudit(array \$audit,array \$patch,array \$contract): void\n    {\n        \$package=static::packageClass();\$policy=static::policy();");
        $replace("(\$audit['package']??null)!=='m68-comparison-basics-bank-audit'", "(\$audit['package']??null)!==\$policy['package']");
        $replace("(\$audit['version']??null)!=='1.0.0'", "(\$audit['version']??null)!==\$policy['version']");
        $replace("(\$audit['scope']??null)!==self::SCOPE", "(\$audit['scope']??null)!==\$policy['scope']");
        $replace("(\$audit['independent_before_sha256']??null)!=='e23f859491c59bcb182dc22a73b69b2b8bad14fb85b565e3bd8fd6cbcc1dac46'",
            "(\$audit['independent_before_sha256']??null)!==\$policy['before_sha256']");
        $replace("M26DetailPackage::digest(\$audit['targets'])!==self::TARGETS_SHA", "M26DetailPackage::digest(\$audit['targets'])!==\$policy['targets_sha256']");
        $replace("\$root??=base_path();\$file=\$root.'/'.self::AUDIT;",
            "\$package=static::packageClass();\$policy=static::policy();\n        \$root??=base_path();\$file=\$root.'/'.\$policy['audit'];");
        $replace("!hash_equals(self::AUDIT_SHA,hash_file('sha256',\$file))", "!hash_equals(\$policy['audit_sha256'],hash_file('sha256',\$file))");
        $replace("private static function binding(TextBlock \$block): ?array\n    {",
            "private static function binding(TextBlock \$block): ?array\n    {\n        \$package=static::packageClass();");
        $replace('M68ComparisonBasics::', '$package::', 12);
        $count = 0;
        $source = preg_replace_callback("/throw new RuntimeException\\('M68 ([^']+)'\\);/",
            static fn (array $match): string => "throw new RuntimeException(\$policy['label'].' ".$match[1]."');", $source, -1, $count);
        self::assertSame(6, $count);
        self::assertStringNotContainsString('M68', $source);
        self::assertSame($source, file_get_contents($root.'/app/Support/TheoryBuilderSelectionPresentation.php'),
            'No additional selection, snapshot, formatter, grading, relation-loading or mutation semantics may change.');
    }

    public function test_public_api_remains_static_compatible_and_only_thin_m69_profile_uses_the_new_abstract_selector(): void
    {
        $accepted = new ReflectionClass(M68BuilderSelectionPresentation::class);
        $generic = new ReflectionClass(TheoryBuilderSelectionPresentation::class);
        $m69 = new ReflectionClass(M69BuilderSelectionPresentation::class);
        self::assertTrue($accepted->isFinal()); self::assertTrue($generic->isAbstract()); self::assertTrue($m69->isFinal());
        self::assertSame(TheoryBuilderSelectionPresentation::class, $m69->getParentClass()->getName());
        foreach (['packageClass', 'policy'] as $name) {
            $method = $generic->getMethod($name);
            self::assertTrue($method->isAbstract()); self::assertTrue($method->isProtected()); self::assertTrue($method->isStatic());
        }
        foreach (['matchingBlock', 'exclusions', 'bindings', 'validateAudit', 'loadAudit'] as $name) {
            $old = $accepted->getMethod($name); $new = $generic->getMethod($name);
            self::assertTrue($new->isPublic()); self::assertTrue($new->isStatic());
            self::assertSame((string) $old->getReturnType(), (string) $new->getReturnType());
            self::assertSame($old->getNumberOfParameters(), $new->getNumberOfParameters());
            foreach ($old->getParameters() as $i=>$parameter) {
                $current = $new->getParameters()[$i];
                self::assertSame($parameter->getName(), $current->getName());
                self::assertSame((string) $parameter->getType(), (string) $current->getType());
                self::assertSame($parameter->isDefaultValueAvailable(), $current->isDefaultValueAvailable());
                if ($parameter->isDefaultValueAvailable()) { self::assertSame($parameter->getDefaultValue(), $current->getDefaultValue()); }
            }
        }
    }
}
