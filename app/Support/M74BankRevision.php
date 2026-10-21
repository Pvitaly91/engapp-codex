<?php

namespace App\Support;

use RuntimeException;

/** One reviewed bank-only successor; never changes owners, rendering or questions. */
final class M74BankRevision
{
    public const LEGACY_CONTRACT = 'docs/content/theory-sentence-builders/m74-other-pronouns-basics.v1.json';
    public const LEGACY_CONTRACT_SHA = '9511dd71f6e56e01bc446b3b7b0ea2dfa5ed7c075a1f6d65b0cf1ea833a29a1d';
    public const CONTRACT = 'docs/content/theory-sentence-builders/revisions/m74-other-pronouns-basics.v1.0.1.json';
    public const CONTRACT_SHA = '74b1df94815b59ba6c75741d39410cf1a2075c50e21ec42e64387546e7fd7849';
    public const REVISION = 'docs/content/m74-bank-selection-revision.v1.0.1.json';
    public const REVISION_SHA = '141c36efde944de48dd135f1592e617307920de7ca7cf808bd1e9fcbfb757e57';

    /** Resolve only this exact immutable predecessor. Normal duplicate/schema guards still run. */
    public static function contractFile(string $root, string $file): string
    {
        $expected = rtrim(str_replace('\\', '/', $root), '/').'/'.self::LEGACY_CONTRACT;
        if (str_replace('\\', '/', $file) !== $expected) { return $file; }
        $next = rtrim($root, '/\\').'/'.self::CONTRACT;
        foreach ([$file => self::LEGACY_CONTRACT_SHA, $next => self::CONTRACT_SHA] as $path => $sha) {
            if (preg_match('/^[a-f0-9]{64}$/D', $sha) !== 1 || !is_file($path) || is_link($path)
                || !hash_equals($sha, hash_file('sha256', $path))) {
                throw new RuntimeException('M74 exact frozen contract predecessor/successor differs.');
            }
        }
        return $next;
    }
}
