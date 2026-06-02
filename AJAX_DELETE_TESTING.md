# AJAX Tag Deletion - Manual Testing Guide

This document describes how to manually test the AJAX tag deletion feature.

## Feature Description

The tag deletion functionality on `/admin/test-tags` and `/admin/test-tags/aggregations` pages has been enhanced to use AJAX requests instead of full page reloads.

## Changes Made

### Backend (Controller)
Modified the following methods in `TestTagController` to support JSON responses:
- `destroy()` - Delete a single tag
- `destroyCategory()` - Delete a tag category
- `destroyEmptyTags()` - Delete all empty tags
- `destroyAggregation()` - Delete an aggregation
- `destroyAggregationCategory()` - Delete an aggregation category

### Frontend (JavaScript)
Added AJAX handlers in both pages:
- `submitFormViaAjax()` - Submits delete forms via AJAX
- `showStatusMessage()` - Displays success/error messages
- `removeDeletedElement()` - Removes deleted items from DOM
- `updateTagCounts()` - Updates tag counts after deletion

## Manual Testing Steps

### 1. Test Tag Deletion on /admin/test-tags

1. Navigate to `/admin/test-tags`
2. Find any tag in the list
3. Click the "Видалити" (Delete) button next to the tag
4. Confirm the deletion in the modal
5. **Expected Result:**
   - The page should NOT reload
   - A success message should appear at the top
   - The deleted tag should disappear from the list
   - Tag counts should be updated

### 2. Test Category Deletion on /admin/test-tags

1. Navigate to `/admin/test-tags`
2. Find any category with tags
3. Click the "Видалити" (Delete) button next to the category name
4. Confirm the deletion in the modal
5. **Expected Result:**
   - The page should NOT reload
   - A success message should appear at the top
   - The entire category block should disappear
   - Total tag count should be updated

### 3. Test Empty Tags Deletion on /admin/test-tags

1. Navigate to `/admin/test-tags`
2. Click the "Видалити пусті теги" (Delete Empty Tags) button
3. Confirm the deletion in the modal
4. **Expected Result:**
   - The page should NOT reload
   - A success message should appear showing how many tags were deleted
   - Empty tags should disappear from the list
   - Tag counts should be updated

### 4. Test Aggregation Deletion on /admin/test-tags/aggregations

1. Navigate to `/admin/test-tags/aggregations`
2. Expand any category to see aggregations
3. Click the "Видалити" (Delete) button next to an aggregation
4. Confirm the deletion in the modal
5. **Expected Result:**
   - The page should NOT reload
   - A success message should appear at the top
   - The deleted aggregation should disappear
   - The tags from the aggregation should move to the "Non-aggregated tags" section

### 5. Test Aggregation Category Deletion on /admin/test-tags/aggregations

1. Navigate to `/admin/test-tags/aggregations`
2. Find any aggregation category
3. Click the trash icon next to the category name
4. Confirm the deletion in the modal
5. **Expected Result:**
   - The page should NOT reload
   - A success message should appear at the top
   - The entire category block should disappear
   - All aggregations in that category should be removed

## Error Handling

Test error scenarios:
1. Try to delete with network disconnected
   - **Expected:** Error message should appear
2. Try to delete a non-existent item (modify form action to invalid ID)
   - **Expected:** Error message should appear with details

## Browser Compatibility

Test in:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)

## Notes

- All existing functionality should continue to work
- The confirmation modal still appears before deletion
- Error messages are displayed in a dismissible banner at the top
- Success messages auto-dismiss after 5 seconds
