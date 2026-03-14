<?php

namespace App\Http\Controllers;

class NewDesignTestController extends TestJsV2Controller
{
    /**
     * Show the new-design interactive test page for the default (card) variant.
     */
    public function show(string $slug)
    {
        $view = 'new-design-show';
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $stateKey = $this->jsStateSessionKey($test, $view);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($resolved, empty($savedState));

        return view('new-design.test.show', [
            'test'          => $test,
            'questionData'  => $questions,
            'jsStateMode'   => $view,
            'savedState'    => $savedState,
            'usesUuidLinks' => $resolved->usesUuidLinks,
            'isAdmin'       => $this->isAdminUser(),
        ]);
    }
}
