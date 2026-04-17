<?php

namespace Tests\Feature\PublicFlows;

class VerbsTrainerSmokeTest extends SeededPublicFlowTestCase
{
    public function test_verbs_trainer_page_renders_with_embedded_dataset(): void
    {
        $response = $this->get('/verbs/test');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertHtmlLocale($html, 'uk');
        $this->assertNoRawTranslationKeys($html);

        $response->assertSee('window.__VERBS__', false);
        $response->assertSee('"base":"go"', false);
        $response->assertSee('"translation":"\u0439\u0442\u0438"', false);
        $response->assertSee('"f1":"goes"', false);
    }

    public function test_verbs_trainer_data_route_returns_expected_shape(): void
    {
        $response = $this->getJson('/verbs/test/data');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'base',
                'base_forms',
                'translation',
                'f1',
                'f2',
                'f3',
                'f4',
            ],
        ]);

        $go = $this->assertJsonItemExists($response->json(), 'base', 'go');

        $this->assertSame('goes', $go['f1']);
        $this->assertSame(['went'], $go['f2']);
        $this->assertSame(['gone'], $go['f3']);
        $this->assertSame('going', $go['f4']);
    }
}
