<?php

namespace App\Console\Commands;

use App\Services\InversionCleftsContentPatch;

class PatchInversionCleftsContent extends PatchLinkingWordsContent
{
    protected const PATCH = InversionCleftsContentPatch::class;
    protected const PRIVATE_DIRECTORY = 'seo-m12-local';
    protected const LABEL = 'M12';

    protected $signature = 'content:patch-inversion-clefts-m12
        {--plan= : New private preview basename, or inspected preview for apply}
        {--apply : Apply the exact inspected preview}
        {--restore : Restore the exact inspected backup, never reseed}
        {--backup= : New backup basename for apply; existing backup for restore}
        {--database= : Exact inspected local database name, mandatory for MySQL writes}
        {--local-target= : Explicit verified Windows gramlyze.loc opt-in; does not change APP_ENV}
        {--local-proof= : Fresh private loopback web-runtime proof basename for local-target}
        {--only=* : Optional exact full M12 Page.seeder identities for independent pages}';
    protected $description = 'Preview/apply/restore only three M12 inversion and cleft UK lessons';
}
