<?php

namespace App\Http\Controllers;

class TheoryController extends PageController
{
    protected ?string $pageType = 'theory';

    protected string $routePrefix = 'theory';

    protected string $showView = 'theory.show';

    protected string $indexView = 'theory.index';

    protected ?string $categoryView = 'theory.category';

    protected string $sectionTitle = 'Теорія';
}
