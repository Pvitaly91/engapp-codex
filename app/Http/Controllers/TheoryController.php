<?php

namespace App\Http\Controllers;

class TheoryController extends PageController
{
    protected ?string $pageType = 'theory';

    protected string $routePrefix = 'theory';

    protected string $showView = 'engram.theory.show-v3';

    protected string $indexView = 'engram.theory.index';

    protected string $categoryView = 'engram.theory.category';

    protected string $sectionTitle = 'Теорія';
}
