<?php

namespace App\Console\Commands;

use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\Theory\TextBlockToQuestionsMatcherService;
use Illuminate\Console\Command;

class TheoryTextBlockQuestionAuditCommand extends Command
{
    protected $signature = 'theory:audit-textblock-questions
        {--category=types-of-questions : Page category slug to audit}
        {--blocks=5 : Number of text blocks to sample}
        {--limit=5 : Number of questions per block}';

    protected $description = 'Audit practice question matching for theory text blocks using tag-based matching.';

    public function handle(TextBlockToQuestionsMatcherService $matcher): int
    {
        $categorySlug = (string) $this->option('category');
        $blockLimit = (int) $this->option('blocks');
        $questionLimit = (int) $this->option('limit');

        $category = PageCategory::query()
            ->where('slug', $categorySlug)
            ->first();

        if (! $category) {
            $this->error("Category not found: {$categorySlug}");

            return self::FAILURE;
        }

        $blocks = TextBlock::query()
            ->whereHas('page', fn ($query) => $query
                ->where('page_category_id', $category->getKey())
                ->where('type', 'theory'))
            ->with(['tags:id,name', 'page:id,title,slug', 'page.category:id,slug'])
            ->whereHas('tags')
            ->limit($blockLimit)
            ->get();

        if ($blocks->isEmpty()) {
            $this->error('No text blocks found for the category.');

            return self::FAILURE;
        }

        $selectedByBlock = [];
        $failed = false;

        foreach ($blocks as $block) {
            $scoredCandidates = $matcher->scoreQuestionCandidatesForTextBlock($block);

            if ($scoredCandidates->isEmpty()) {
                $this->error("No scored questions for block {$block->uuid}.");
                $failed = true;
                continue;
            }

            $questions = $matcher->findBestQuestionsForTextBlock($block, $questionLimit);

            if ($questions->isEmpty()) {
                $this->error("No matched questions for block {$block->uuid}.");
                $failed = true;
                continue;
            }

            $invalidMatches = $questions->filter(fn ($question) => ($question->match_score ?? 0) <= 0 || empty($question->matched_tag_ids));

            if ($invalidMatches->isNotEmpty()) {
                $this->error("Weak matches detected for block {$block->uuid}.");
                $failed = true;
            }

            $scoredCandidates = $scoredCandidates->sortByDesc('score')->values();
            $topCandidate = $scoredCandidates->first();
            $maxMatchedCount = $scoredCandidates->max(fn (array $item) => count($item['matched_tag_ids']));
            $topMatchedCount = count($topCandidate['matched_tag_ids'] ?? []);

            if ($topMatchedCount < $maxMatchedCount) {
                $this->error("Top candidate for block {$block->uuid} does not have the strongest tag overlap.");
                $failed = true;
            }

            $selectedByBlock[] = $questions->pluck('id')->unique()->values()->all();

            $this->info("Block {$block->uuid}: selected {$questions->count()} question(s).");
        }

        if (count($selectedByBlock) > 1) {
            $uniqueQuestions = collect($selectedByBlock)->flatten()->unique();
            $firstCount = count($selectedByBlock[0] ?? []);

            if ($uniqueQuestions->count() <= $firstCount) {
                $this->error('Selected questions do not vary between blocks.');
                $failed = true;
            }
        }

        if ($failed) {
            return self::FAILURE;
        }

        $this->info('Audit completed successfully.');

        return self::SUCCESS;
    }
}
