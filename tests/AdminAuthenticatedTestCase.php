<?php

namespace Tests;

abstract class AdminAuthenticatedTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['admin_authenticated' => true]);
    }
}
