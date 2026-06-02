# Site Tree Link Method Tracking

## Overview

The site tree admin interface now displays **how** each connection between a tree item and a theory page was established. This helps administrators understand the linking process and troubleshoot any issues.

## Link Methods

There are four possible link methods, each displayed with a distinct colored badge:

### 1. **Exact Title** (Green Badge)
- **Label**: "точна назва"
- **Color**: Green (`bg-green-100 text-green-700`)
- **How it works**: The tree item title exactly matches the page title (case-insensitive)
- **Example**: Tree item "Present Simple — Теперішній простий час" matches page with the same title
- **Reliability**: Highest - this is the most reliable matching method

### 2. **Seeder Name** (Purple Badge)
- **Label**: "сидер"
- **Color**: Purple (`bg-purple-100 text-purple-700`)
- **How it works**: The page's seeder class name contains a normalized version of the tree item title
- **Example**: Tree item "Advanced word order and emphasis" matches page with seeder `AdvancedWordOrderEmphasisSeeder`
- **Reliability**: High - uses structured naming conventions

### 3. **Slug Match** (Yellow Badge)
- **Label**: "slug"
- **Color**: Yellow (`bg-yellow-100 text-yellow-700`)
- **How it works**: The page slug and generated slug from tree item title contain each other
- **Example**: Tree item "Basic word order" generates slug "basic-word-order" which matches page slug
- **Reliability**: Medium - based on slug patterns

### 4. **Manual** (Blue Badge)
- **Label**: "вручну"
- **Color**: Blue (`bg-blue-100 text-blue-700`)
- **How it works**: Administrator manually linked the item via the admin interface
- **Reliability**: Varies - depends on administrator's judgment

## How to View Link Methods

1. Navigate to `/admin/site-tree`
2. Look at each tree item
3. Linked items show:
   - A green checkmark (✓) that opens the page on the site
   - A colored badge indicating the link method
   - Hover over the badge to see a detailed tooltip

## Implementation Details

### Database
- **Migration**: `2025_12_07_215045_add_link_method_to_site_tree_items_table.php`
- **Column**: `link_method` (nullable string) in `site_tree_items` table
- **Values**: `exact_title`, `seeder_name`, `slug_match`, `manual`, or `null`

### Automatic Linking (Seeder)
The `SiteTreeSeeder::linkPagesToTreeItems()` method tries linking strategies in order:
1. Exact title match → sets `link_method = 'exact_title'`
2. Seeder name match → sets `link_method = 'seeder_name'`
3. Slug pattern match → sets `link_method = 'slug_match'`

### Manual Linking (Admin UI)
When an administrator uses the "Зв'язати з /theory" button:
- The `SiteTreeController::update()` method sets `link_method = 'manual'`
- Removing a link clears the `link_method` to `null`

## Troubleshooting

### Items Not Automatically Linked
If a tree item isn't automatically linked:
1. Check if the page title exactly matches
2. Verify the page seeder class name follows naming conventions
3. Check if the page slug is similar to the tree item title
4. Use manual linking as a fallback

### Incorrect Automatic Links
If an item is incorrectly linked:
1. Check the link method badge to understand why it was linked
2. Manually unlink and re-link to the correct page
3. The link method will change to "manual" (blue badge)

## Future Enhancements

Potential improvements:
- Add a filter to show only items linked via specific methods
- Track link history (when/how it changed)
- Add validation warnings for potentially incorrect automatic links
- Support multiple link strategies per item (fallback chain)
