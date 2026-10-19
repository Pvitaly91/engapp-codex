<?php

namespace App\Console\Commands;

use App\Services\ModalsSubjunctiveContentPatch;

class PatchModalsSubjunctiveContent extends PatchLinkingWordsContent
{
    protected const PATCH = ModalsSubjunctiveContentPatch::class;

    protected const PRIVATE_DIRECTORY = 'seo-m20-local';

    protected const LABEL = 'M20';

    protected $signature = 'content:patch-modals-subjunctive-m20
        {--plan= : New private preview basename, or inspected preview for apply}
        {--apply : Apply the exact inspected preview}
        {--restore : Restore the exact inspected backup, never reseed}
        {--backup= : New backup basename for apply; existing backup for restore}
        {--database= : Exact inspected local database name, mandatory for MySQL writes}
        {--local-target= : Explicit verified Windows gramlyze.loc opt-in; does not change APP_ENV}
        {--local-proof= : Fresh private loopback web-runtime proof basename for local-target}
        {--only=* : Optional exact full M20 Page.seeder identities for independent pages}';

    protected $description = 'Preview/apply/restore only three M20 modals-subjunctive UK lessons';
}
