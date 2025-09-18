<?php

namespace Tests\Feature;

use App\Models\Test;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SavedTestJsStateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('tests')) {
            Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_20_184450_create_tests_table.php']);
            Artisan::call('migrate', ['--path' => 'database/migrations/2025_08_04_000002_add_description_to_tests_table.php']);
        }

        Test::query()->delete();
    }

    private function createSavedTest(): Test
    {
        return Test::create([
            'name' => 'Sample Test',
            'slug' => uniqid('sample-test-', true),
            'filters' => [],
            'questions' => [],
        ]);
    }

    private function postState(Test $test, array $payload, array $session = [])
    {
        $session = array_merge(['_token' => 'test-token'], $session);

        return $this->withSession($session)->postJson(
            route('saved-test.js.state', $test->slug),
            $payload,
            ['X-CSRF-TOKEN' => 'test-token']
        );
    }

    public function test_it_stores_state_in_session(): void
    {
        $test = $this->createSavedTest();

        $payload = [
            'mode' => 'saved-test-js',
            'state' => ['foo' => 'bar'],
        ];

        $response = $this->postState($test, $payload);

        $response->assertNoContent();

        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');
        $this->assertSame($payload['state'], session($key));
    }

    public function test_it_clears_state_when_null(): void
    {
        $test = $this->createSavedTest();
        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');

        $response = $this->postState($test, [
            'mode' => 'saved-test-js',
            'state' => null,
        ], [
            $key => ['foo' => 'bar'],
        ]);

        $response->assertNoContent();
        $this->assertNull(session($key));
    }

    public function test_it_rejects_invalid_payload(): void
    {
        $test = $this->createSavedTest();

        $this->postState($test, [
            'mode' => 'not-valid',
            'state' => ['allowed' => true],
        ])->assertStatus(422);

        $invalidKey = sprintf('saved_test_js_state:%s:%s', $test->slug, 'not-valid');
        $this->assertNull(session($invalidKey));

        $this->postState($test, [
            'mode' => 'saved-test-js',
            'state' => 'nope',
        ])->assertStatus(422);

        $validKey = sprintf('saved_test_js_state:%s:%s', $test->slug, 'saved-test-js');
        $this->assertNull(session($validKey));
    }
}
