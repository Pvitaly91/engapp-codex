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
            'Interactive test',
            'Відповідайте на питання в card mode, використовуйте клавіші `1-4`, підказки, словниковий пошук і повертайтесь до каталогу без втрати функціоналу поточної сторінки тесту.'
        );
    }

    public function showSavedTestJsStepNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-v2',
            'test-modes.step-easy',
            'Step-by-step test',
            'Проходьте тест по одному питанню за раз у step mode, використовуйте пошук, підказки і стандартну навігацію без зміни поточної логіки.'
        );
    }

    public function showSavedTestJsStepSelectNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-select-v2',
            'test-modes.step-medium',
            'Step medium',
            'Середній step-режим залишає покрокову навігацію і працює в новій публічній оболонці без змін у функціоналі.'
        );
    }

    public function showSavedTestJsStepInputNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-input-v2',
            'test-modes.step-hard',
            'Step hard',
            'Hard step-режим з ручним введенням відповіді тепер відкривається в новому дизайні, зберігаючи стару поведінку перевірки і прогресу.'
        );
    }

    public function showSavedTestJsStepManualNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-step-manual-v2',
            'test-modes.step-expert',
            'Step expert',
            'Expert step-режим доступний у новому публічному інтерфейсі з тим самим сценарієм проходження, станом сесії та логікою оцінювання.'
        );
    }

    public function showSavedTestJsSelectNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-select-v2',
            'test-modes.card-medium',
            'Card medium',
            'Medium card-режим з варіантами відповідей працює в новому shell, але використовує той самий JS-функціонал і persistence.'
        );
    }

    public function showSavedTestJsInputNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-input-v2',
            'test-modes.card-hard',
            'Card hard',
            'Hard card-режим з введенням відповіді вручну перенесено в новий інтерфейс без зміни правил перевірки.'
        );
    }

    public function showSavedTestJsManualNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-manual-v2',
            'test-modes.card-expert',
            'Card expert',
            'Expert card-режим відображається в новому дизайні, але зберігає поточну логіку введення, оцінки і відновлення стану.'
        );
    }

    public function showSavedTestJsDragDropNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-drag-drop',
            'test-modes.drag-drop',
            'Drag & drop',
            'Перетягуйте або обирайте варіанти для пропусків у drag & drop-режимі всередині нового layout.'
        );
    }

    public function showSavedTestJsMatchNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-match',
            'test-modes.match',
            'Match mode',
            'Match-режим зіставлення речень і пояснень тепер також доступний у новому публічному інтерфейсі.'
        );
    }

    public function showSavedTestJsDialogueNewDesign(string $slug)
    {
        return $this->renderNewDesignMode(
            $slug,
            'saved-test-js-dialogue',
            'test-modes.dialogue',
            'Dialogue mode',
            'Dialogue-режим із покроковим проходженням реплік відкривається в новому дизайні без зміни існуючої логіки.'
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
            'heroBadge' => $heroBadge,
            'heroDescription' => $heroDescription,
        ]);
    }
}
