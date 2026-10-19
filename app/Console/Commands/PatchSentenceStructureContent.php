<?php

namespace App\Console\Commands;

use App\Services\SentenceStructureContentPatch;

class PatchSentenceStructureContent extends PatchLinkingWordsContent
{
    protected const PATCH = SentenceStructureContentPatch::class;
    protected const PRIVATE_DIRECTORY = 'seo-m13-local';
    protected const LABEL = 'M13';

    protected $signature = 'content:patch-sentence-structure-m13
        {--plan= : New private preview basename, or inspected preview for apply}
        {--apply : Apply the exact inspected preview}
        {--restore : Restore the exact inspected backup, never reseed}
        {--backup= : New backup basename for apply; existing backup for restore}
        {--database= : Exact inspected local database name, mandatory for MySQL writes}
        {--local-target= : Explicit verified Windows gramlyze.loc opt-in; does not change APP_ENV}
        {--local-proof= : Fresh private loopback web-runtime proof basename for local-target}
        {--only=* : Optional exact full M13 Page.seeder identities for independent pages}';
    protected $description = 'Preview/apply/restore only three M13 sentence structure UK lessons';
}
