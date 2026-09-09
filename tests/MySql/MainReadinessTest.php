<?php

namespace Tests\MySql;

/** Run exactly the SQLite readiness fixtures and assertions on opt-in real MySQL. */
class MainReadinessTest extends \Tests\Feature\MainTheoryTestSitemapReadinessTest
{
    use \Tests\Support\UsesDisposableMySql;
}
