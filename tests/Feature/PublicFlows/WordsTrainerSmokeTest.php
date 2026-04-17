<?php

namespace Tests\Feature\PublicFlows;

use Tests\Support\PublicRouteMatrix;

class WordsTrainerSmokeTest extends SeededPublicFlowTestCase
{
    public function test_words_trainer_pages_render_for_each_supported_difficulty(): void
    {
        foreach (PublicRouteMatrix::wordsDifficultyPages() as $paths) {
            $response = $this->get($paths['page']);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertHtmlLocale($html, PublicRouteMatrix::DEFAULT_LOCALE);
            $this->assertNoRawTranslationKeys($html);

            $response->assertSee($paths['state'], false);
            $response->assertSee($paths['check'], false);
            $response->assertSee($paths['reset'], false);
        }
    }

    public function test_words_trainer_state_check_reset_and_language_switch_are_stable(): void
    {
        $easyPaths = PublicRouteMatrix::wordsDifficultyPages()['easy'];

        $stateResponse = $this->getJson($easyPaths['state']);

        $stateResponse->assertOk();
        $stateResponse->assertJsonStructure([
            'needsStudyLanguage',
            'siteLocale',
            'studyLang',
            'availableStudyLangs',
            'question' => [
                'word_id',
                'word',
                'translation',
                'tags',
                'questionType',
                'prompt',
                'options',
                'studyLang',
            ],
            'stats' => ['correct', 'wrong', 'total'],
            'percentage',
            'totalCount',
            'completed',
            'failed',
            'difficulty',
        ]);

        $statePayload = $stateResponse->json();
        $question = $this->currentQuestion($statePayload);

        $this->assertFalse($statePayload['needsStudyLanguage']);
        $this->assertSame('uk', $statePayload['siteLocale']);
        $this->assertSame('uk', $statePayload['studyLang']);
        $this->assertSame('easy', $statePayload['difficulty']);
        $this->assertContains('uk', $statePayload['availableStudyLangs']);
        $this->assertContains('pl', $statePayload['availableStudyLangs']);

        $checkResponse = $this->postJson($easyPaths['check'], [
            'word_id' => $question['word_id'],
            'answer' => $question['translation'],
        ]);

        $checkResponse->assertOk();
        $checkResponse->assertJsonPath('result.isCorrect', true);
        $checkResponse->assertJsonPath('stats.correct', 1);
        $checkResponse->assertJsonPath('stats.total', 1);

        $languageResponse = $this->postJson('/words/test/set-study-language', [
            'lang' => 'pl',
            'difficulty' => 'easy',
        ]);

        $languageResponse->assertOk();
        $languageResponse->assertJsonPath('ok', true);
        $languageResponse->assertJsonPath('studyLang', 'pl');

        $resetResponse = $this->postJson($easyPaths['reset']);

        $resetResponse->assertOk();
        $resetResponse->assertJsonPath('studyLang', 'pl');
        $resetResponse->assertJsonPath('stats.correct', 0);
        $resetResponse->assertJsonPath('stats.wrong', 0);
        $resetResponse->assertJsonPath('stats.total', 0);
    }
}
