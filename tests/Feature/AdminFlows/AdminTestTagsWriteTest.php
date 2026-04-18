<?php

namespace Tests\Feature\AdminFlows;

use App\Models\Tag;

class AdminTestTagsWriteTest extends SeededAdminFlowTestCase
{
    public function test_authenticated_admin_can_create_a_test_tag(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('test-tags.store'), [
                'name' => 'Admin Write Created Tag',
                'category' => '',
                'new_category' => 'Admin Write Category',
            ]);

        $response->assertRedirect(route('test-tags.index'));
        $response->assertSessionHas('status', 'Тег успішно створено.');

        $this->assertDatabaseHas('tags', [
            'name' => 'Admin Write Created Tag',
            'category' => 'Admin Write Category',
        ]);

        $this->withSession($this->adminSession())
            ->get(route('test-tags.index'))
            ->assertOk()
            ->assertSeeText('Admin Write Created Tag')
            ->assertSeeText('Admin Write Category');
    }

    public function test_authenticated_admin_can_update_a_test_tag(): void
    {
        $tag = Tag::create([
            'name' => 'Admin Write Update Source Tag',
            'category' => 'Admin Write Source Category',
        ]);

        $response = $this->withSession($this->adminSession())
            ->put(route('test-tags.update', $tag), [
                'name' => 'Admin Write Updated Tag',
                'category' => '',
                'new_category' => 'Admin Write Updated Category',
            ]);

        $response->assertRedirect(route('test-tags.index'));
        $response->assertSessionHas('status', 'Тег оновлено.');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Admin Write Updated Tag',
            'category' => 'Admin Write Updated Category',
        ]);

        $this->withSession($this->adminSession())
            ->get(route('test-tags.index'))
            ->assertOk()
            ->assertSeeText('Admin Write Updated Tag')
            ->assertSeeText('Admin Write Updated Category');
    }

    public function test_authenticated_admin_can_delete_a_test_tag(): void
    {
        $tag = Tag::create([
            'name' => 'Admin Write Delete Tag',
            'category' => 'Admin Write Delete Category',
        ]);

        $response = $this->withSession($this->adminSession())
            ->delete(route('test-tags.destroy', $tag));

        $response->assertRedirect(route('test-tags.index'));
        $response->assertSessionHas('status', 'Тег видалено.');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $this->withSession($this->adminSession())
            ->get(route('test-tags.index'))
            ->assertOk()
            ->assertDontSeeText('Admin Write Delete Tag');
    }

    public function test_unauthenticated_user_cannot_modify_test_tags(): void
    {
        $tag = Tag::create([
            'name' => 'Admin Write Protected Tag',
            'category' => 'Admin Write Protected Category',
        ]);

        $createResponse = $this->post(route('test-tags.store'), [
            'name' => 'Admin Write Blocked Tag',
            'category' => '',
            'new_category' => 'Admin Write Blocked Category',
        ]);
        $this->assertRedirectsToLogin($createResponse, route('test-tags.store'));
        $this->assertDatabaseMissing('tags', [
            'name' => 'Admin Write Blocked Tag',
        ]);

        $updateResponse = $this->put(route('test-tags.update', $tag), [
            'name' => 'Admin Write Should Not Update',
            'category' => '',
            'new_category' => 'Admin Write Should Not Update Category',
        ]);
        $this->assertRedirectsToLogin($updateResponse, route('test-tags.update', $tag));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Admin Write Protected Tag',
            'category' => 'Admin Write Protected Category',
        ]);

        $deleteResponse = $this->delete(route('test-tags.destroy', $tag));
        $this->assertRedirectsToLogin($deleteResponse, route('test-tags.destroy', $tag));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Admin Write Protected Tag',
        ]);
    }
}
