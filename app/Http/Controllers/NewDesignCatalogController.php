<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewDesignCatalogController extends GrammarTestController
{
    /**
     * Show the new-design catalog page, reusing all data logic from the parent
     * but rendering a new-design view.
     */
    public function catalogAggregated(Request $request)
    {
        /** @var \Illuminate\View\View $response */
        $response = parent::catalogAggregated($request);

        return view('new-design.catalog.tests-cards', $response->getData());
    }
}
