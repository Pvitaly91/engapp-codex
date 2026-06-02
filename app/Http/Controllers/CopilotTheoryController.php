<?php

namespace App\Http\Controllers;

class CopilotTheoryController extends PageController
{
    protected ?string $pageType = 'theory';

    protected string $routePrefix = 'copilot.theory';

    protected string $showView = 'copilot.theory.show';

    protected string $indexView = 'copilot.theory.index';

    protected ?string $categoryView = 'copilot.theory.category';

    protected string $sectionTitle = 'Теорія';
}
