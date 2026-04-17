<?php
$pages = [
 'gerund','to-infinitive','bare-infinitive','gerund-vs-infinitive','verbs-plus-gerund','verbs-plus-infinitive','stop-remember-forget-try-regret','be-used-to-get-used-to-used-to'
];
$locales = ['uk' => '', 'en' => '/en', 'pl' => '/pl'];
foreach ($locales as $locale => $prefix) {
    echo "== $locale ==\n";
    foreach ($pages as $slug) {
        $url = "http://127.0.0.1:8000{$prefix}/theory/verb-patterns/{$slug}";
        $html = file_get_contents($url);
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xp = new DOMXPath($dom);
        $title = trim($xp->query('//title')->item(0)?->textContent ?? '');
        $h1 = trim($xp->query('//h1')->item(0)?->textContent ?? '');
        $branchLinks = [];
        foreach ($xp->query('//a[contains(@href, "/theory/verb-patterns/")]') as $a) {
            $text = trim(preg_replace('/\s+/u', ' ', $a->textContent));
            if ($text !== '') {
                $branchLinks[] = $text;
            }
        }
        $hasPublicLeak = str_contains($html, 'public.');
        $hasTestLeak = preg_match('/(public\.[a-z0-9_.-]+)/i', $html) === 1;
        $relatedTestSnippets = [];
        foreach ($xp->query('//a[contains(@href, "/test") or contains(@href, "/tests") or contains(@href, "tests-cards")]') as $a) {
            $href = $a->getAttribute('href');
            $text = trim(preg_replace('/\s+/u', ' ', $a->textContent));
            if ($text !== '') {
                $relatedTestSnippets[] = $text . ' [' . $href . ']';
            }
        }
        echo $slug, "\n";
        echo 'TITLE: ', $title, "\n";
        echo 'H1: ', $h1, "\n";
        echo 'BRANCH_LINK_COUNT: ', count($branchLinks), "\n";
        echo 'BRANCH_LINKS: ', implode(' | ', array_slice($branchLinks, 0, 10)), "\n";
        echo 'HAS_PUBLIC_KEY: ', ($hasPublicLeak ? 'yes' : 'no'), "\n";
        echo 'HAS_TEST_KEY: ', ($hasTestLeak ? 'yes' : 'no'), "\n";
        echo 'TEST_LINKS: ', implode(' | ', array_slice($relatedTestSnippets, 0, 6)), "\n";
        echo "---\n";
    }
}
