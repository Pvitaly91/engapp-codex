<?php

namespace App\Http\Controllers;

class NewDesignTestController extends TestJsV2Controller
{
    public function showSavedTestJsNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-v2',
            'test-modes.card-easy',
            'frontend.tests.hero.interactive',
            'frontend.tests.hero.card_description'
        );
    }

    public function showSavedTestJsStepNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-v2',
            'test-modes.step-easy',
            'frontend.tests.templates.step_easy.badge',
            'frontend.tests.templates.step_easy.description'
        );
    }

    public function showSavedTestJsStepSelectNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-select-v2',
            'test-modes.step-medium',
            'frontend.tests.templates.step_medium.badge',
            'frontend.tests.templates.step_medium.description'
        );
    }

    public function showSavedTestJsStepInputNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-input-v2',
            'test-modes.step-hard',
            'frontend.tests.templates.step_hard.badge',
            'frontend.tests.templates.step_hard.description'
        );
    }

    public function showSavedTestJsStepManualNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-manual-v2',
            'test-modes.step-expert',
            'frontend.tests.templates.step_expert.badge',
            'frontend.tests.templates.step_expert.description'
        );
    }

    public function showSavedTestJsSelectNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-select-v2',
            'test-modes.card-medium',
            'frontend.tests.templates.card_medium.badge',
            'frontend.tests.templates.card_medium.description'
        );
    }

    public function showSavedTestJsInputNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-input-v2',
            'test-modes.card-hard',
            'frontend.tests.templates.card_hard.badge',
            'frontend.tests.templates.card_hard.description'
        );
    }

    public function showSavedTestJsManualNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-manual-v2',
            'test-modes.card-expert',
            'frontend.tests.templates.card_expert.badge',
            'frontend.tests.templates.card_expert.description'
        );
    }

    public function showSavedTestJsDragDropNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-drag-drop',
            'test-modes.drag-drop',
            'frontend.tests.mode.drag_drop',
            'frontend.tests.drag_drop.intro'
        );
    }

    public function showSavedTestJsMatchNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-match',
            'test-modes.match',
            'frontend.tests.mode.match',
            'frontend.tests.match.intro'
        );
    }

    public function showSavedTestJsDialogueNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-dialogue',
            'test-modes.dialogue',
            'frontend.tests.mode.dialogue',
            'frontend.tests.dialogue.intro'
        );
    }

    protected function renderNewDesignMode(
        string $slug,
        string $stateMode,
        string $templateView,
        string $heroBadge,
        string $heroDescription
    ) {
        return $this->renderSavedTestShell($slug, $stateMode, 'test-show', [
            'templateView' => $templateView,
            'heroBadge' => __($heroBadge),
            'heroDescription' => __($heroDescription),
        ]);
    }
}
