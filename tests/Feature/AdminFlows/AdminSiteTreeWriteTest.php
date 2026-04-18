<?php

namespace Tests\Feature\AdminFlows;

use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;

class AdminSiteTreeWriteTest extends SeededAdminFlowTestCase
{
    public function test_authenticated_admin_can_create_a_site_tree_item(): void
    {
        $response = $this->withSession($this->adminSession())
            ->postJson(route('site-tree.store'), [
                'title' => 'Admin Write Created Node',
                'linked_page_title' => 'Admin Write Linked Page',
                'linked_page_url' => '/theory/admin-write-linked-page',
                'level' => 'B1',
                'variant_id' => $this->baseVariantId(),
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('item.title', 'Admin Write Created Node');

        $this->assertDatabaseHas('site_tree_items', [
            'title' => 'Admin Write Created Node',
            'variant_id' => $this->baseVariantId(),
            'linked_page_title' => 'Admin Write Linked Page',
            'linked_page_url' => '/theory/admin-write-linked-page',
            'level' => 'B1',
            'is_checked' => 1,
        ]);

        $this->withSession($this->adminSession())
            ->get(route('site-tree.index'))
            ->assertOk()
            ->assertSeeText('Admin Write Created Node');
    }

    public function test_authenticated_admin_can_update_a_site_tree_item(): void
    {
        $item = $this->createSiteTreeItem('Admin Write Update Source Node');

        $response = $this->withSession($this->adminSession())
            ->putJson(route('site-tree.update', $item), [
                'title' => 'Admin Write Updated Node',
                'level' => 'C1',
                'is_checked' => false,
                'linked_page_title' => 'Admin Write Updated Page',
                'linked_page_url' => '/theory/admin-write-updated-page',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('item.title', 'Admin Write Updated Node');
        $response->assertJsonPath('item.link_method', 'manual');

        $this->assertDatabaseHas('site_tree_items', [
            'id' => $item->id,
            'title' => 'Admin Write Updated Node',
            'level' => 'C1',
            'is_checked' => 0,
            'linked_page_title' => 'Admin Write Updated Page',
            'linked_page_url' => '/theory/admin-write-updated-page',
            'link_method' => 'manual',
        ]);

        $this->withSession($this->adminSession())
            ->get(route('site-tree.index'))
            ->assertOk()
            ->assertSeeText('Admin Write Updated Node');
    }

    public function test_authenticated_admin_can_delete_a_site_tree_item(): void
    {
        $item = $this->createSiteTreeItem(
            'Admin Write Delete Node',
            parentId: SiteTreeItem::query()->where('title', 'Admin Grammar Section')->value('id')
        );

        $response = $this->withSession($this->adminSession())
            ->deleteJson(route('site-tree.destroy', $item));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('site_tree_items', [
            'id' => $item->id,
        ]);

        $this->withSession($this->adminSession())
            ->get(route('site-tree.index'))
            ->assertOk()
            ->assertDontSeeText('Admin Write Delete Node');
    }

    public function test_unauthenticated_user_cannot_modify_site_tree_items(): void
    {
        $item = $this->createSiteTreeItem('Admin Write Protected Node');

        $createResponse = $this->post(route('site-tree.store'), [
            'title' => 'Admin Write Blocked Node',
            'variant_id' => $this->baseVariantId(),
        ]);
        $this->assertRedirectsToLogin($createResponse, route('site-tree.store'));
        $this->assertDatabaseMissing('site_tree_items', [
            'title' => 'Admin Write Blocked Node',
        ]);

        $updateResponse = $this->put(route('site-tree.update', $item), [
            'title' => 'Admin Write Should Not Update Node',
            'level' => 'B2',
        ]);
        $this->assertRedirectsToLogin($updateResponse, route('site-tree.update', $item));
        $this->assertDatabaseHas('site_tree_items', [
            'id' => $item->id,
            'title' => 'Admin Write Protected Node',
        ]);

        $deleteResponse = $this->delete(route('site-tree.destroy', $item));
        $this->assertRedirectsToLogin($deleteResponse, route('site-tree.destroy', $item));
        $this->assertDatabaseHas('site_tree_items', [
            'id' => $item->id,
            'title' => 'Admin Write Protected Node',
        ]);
    }

    private function baseVariantId(): int
    {
        return SiteTreeVariant::getBase()->id;
    }

    private function createSiteTreeItem(string $title, ?int $parentId = null): SiteTreeItem
    {
        $variantId = $this->baseVariantId();
        $maxSortOrder = SiteTreeItem::query()
            ->where('variant_id', $variantId)
            ->where('parent_id', $parentId)
            ->max('sort_order') ?? -1;

        return SiteTreeItem::create([
            'parent_id' => $parentId,
            'variant_id' => $variantId,
            'title' => $title,
            'linked_page_title' => null,
            'linked_page_url' => null,
            'level' => 'A2',
            'is_checked' => true,
            'sort_order' => $maxSortOrder + 1,
        ]);
    }
}
