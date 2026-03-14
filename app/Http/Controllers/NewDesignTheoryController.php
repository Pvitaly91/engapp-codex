<?php

namespace App\Http\Controllers;

class NewDesignTheoryController extends PageController
{
    protected ?string $pageType = 'theory';

    protected string $routePrefix = 'new-design.theory';

    protected string $showView = 'new-design.theory.show';

    protected string $indexView = 'new-design.theory.index';

    protected ?string $categoryView = 'new-design.theory.category';

    protected string $sectionTitle = 'Теорія';
}
