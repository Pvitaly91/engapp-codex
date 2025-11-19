# AJAX Seed-Run Testing Checklist

## Overview
This checklist helps verify that all AJAX functionality for seed-run operations works correctly without page reload.

## Pre-Testing Setup
1. Ensure you have test seeders available
2. Clear browser cache
3. Open browser developer console to monitor network requests
4. Check that you have both pending and executed seeders

---

## Test Scenarios

### ✅ Basic AJAX Functionality

#### 1. Seed Execution
- [ ] **Execute Single Seeder (Pending)**
  - Click "Виконати" on a pending seeder
  - Verify: Preloader shows
  - Verify: Success message appears
  - Verify: Page reloads after ~500ms
  - Verify: Seeder moved to "Виконані сидери" section
  - Check Console: Network request shows XHR with JSON response

- [ ] **Execute All Pending Seeders**
  - Click "Виконати всі невиконані" button
  - Verify: Confirmation modal appears (if >1 seeder)
  - Confirm: Click "Підтвердити"
  - Verify: Preloader shows
  - Verify: Success message with count appears
  - Verify: Page reloads
  - Verify: All seeders moved to executed section

- [ ] **Mark as Executed**
  - Click "Позначити виконаним" on a pending seeder
  - Verify: Preloader shows
  - Verify: Success message appears
  - Verify: Page reloads
  - Verify: Seeder moved to executed section without actual execution

#### 2. File Deletion (Pending Seeders)
- [ ] **Delete Single Seeder File**
  - Click "Видалити файл" on a pending seeder
  - Verify: Confirmation dialog appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message appears
  - Verify: Page reloads
  - Verify: Seeder removed from list

- [ ] **Bulk Delete Seeder Files**
  - Check checkboxes for 2-3 pending seeders
  - Verify: "Видалити вибрані файли" button becomes enabled
  - Click bulk delete button
  - Verify: Confirmation dialog appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message with count appears
  - Verify: Page reloads
  - Verify: All selected seeders removed

#### 3. Record Deletion (Executed Seeders)
- [ ] **Delete Seed Run Record Only**
  - Navigate to an executed seeder
  - Find "Видалити лог" action
  - Click it
  - Verify: Confirmation dialog appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message appears
  - Verify: Page reloads
  - Verify: Seeder removed from executed section (file still exists)

- [ ] **Delete Seed Run with Questions**
  - Find an executed seeder with questions
  - Click "Видалити з питаннями"
  - Verify: Confirmation dialog with questions warning appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message includes question count
  - Verify: Page reloads
  - Verify: Seeder and questions removed

- [ ] **Delete Executed Seeder File**
  - Find an executed seeder
  - Click "Видалити файл"
  - Verify: Confirmation dialog appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message appears
  - Verify: Page reloads
  - Verify: Seeder removed (both file and record)

- [ ] **Delete Executed Seeder File with Questions**
  - Enable "Також видалити питання" checkbox
  - Click "Видалити файл"
  - Verify: Confirmation mentions questions
  - Confirm deletion
  - Verify: Success message includes question count
  - Verify: Page reloads
  - Verify: Seeder and questions removed

#### 4. Folder Operations
- [ ] **Delete Folder (Records Only)**
  - Find a folder with multiple executed seeders
  - Click folder's "Видалити логи" button
  - Verify: Confirmation dialog with folder name appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message with count appears
  - Verify: Page reloads
  - Verify: Folder's seed run records removed

- [ ] **Delete Folder with Questions**
  - Find a folder with multiple executed seeders with questions
  - Click "Видалити з питаннями"
  - Verify: Confirmation dialog appears
  - Confirm deletion
  - Verify: Preloader shows
  - Verify: Success message includes counts
  - Verify: Page reloads
  - Verify: All data removed

---

## Error Handling Tests

### ✅ Error Scenarios

- [ ] **Invalid Seeder Execution**
  - Try to execute a non-existent seeder (modify URL)
  - Verify: Error message appears
  - Verify: Preloader hides
  - Verify: Page doesn't reload

- [ ] **Network Error Simulation**
  - Disconnect network
  - Try any operation
  - Verify: Error message appears
  - Verify: Preloader hides

- [ ] **Server Error Simulation**
  - Verify 500 errors are handled gracefully

---

## UI/UX Tests

### ✅ User Experience

- [ ] **Confirmation Modal**
  - Open confirmation modal for any delete operation
  - Click "Скасувати"
  - Verify: Modal closes
  - Verify: No operation performed

- [ ] **Preloader Visibility**
  - Verify preloader shows immediately on operation start
  - Verify preloader hides after operation completes
  - Verify preloader shows during entire operation

- [ ] **Feedback Messages**
  - Verify success messages appear in green
  - Verify error messages appear in red
  - Verify messages are clear and informative
  - Verify messages auto-hide after delay

- [ ] **Multiple Operations**
  - Try starting another operation while one is in progress
  - Verify: Preloader prevents multiple simultaneous operations

---

## Browser Compatibility

Test in the following browsers:

- [ ] **Chrome/Chromium** (Latest)
- [ ] **Firefox** (Latest)
- [ ] **Safari** (Latest, if available)
- [ ] **Edge** (Latest)

---

## Console Verification

For each operation, verify in browser console:

- [ ] No JavaScript errors
- [ ] XHR requests use JSON
- [ ] Requests include `X-Requested-With: XMLHttpRequest` header
- [ ] Responses are JSON format
- [ ] Response status codes are appropriate (200, 404, 422, 500)

---

## Performance Tests

- [ ] **Large Dataset**
  - Test with 50+ pending seeders
  - Test bulk operations
  - Verify: No performance degradation

- [ ] **Network Speed**
  - Test on slow 3G connection
  - Verify: Operations complete successfully
  - Verify: Appropriate loading indicators

---

## Regression Tests

Ensure existing functionality still works:

- [ ] **File Editor Modal**
  - Open seeder file for editing
  - Make changes
  - Save file
  - Verify: Still works as before

- [ ] **Search Functionality**
  - Search for executed seeders
  - Verify: Search still works

- [ ] **Folder Navigation**
  - Expand/collapse folders
  - Verify: Navigation still works

- [ ] **Question Management**
  - Delete individual questions
  - Verify: Still works via AJAX

---

## Security Tests

- [ ] **CSRF Protection**
  - Verify CSRF token is sent with requests
  - Test with invalid CSRF token
  - Verify: Request rejected

- [ ] **Authorization**
  - Test operations without proper authentication
  - Verify: Proper error handling

---

## Notes for Testing

1. **Clear Cache**: Clear browser cache before testing
2. **Console Logging**: Keep browser console open to catch errors
3. **Network Tab**: Monitor network requests to verify AJAX calls
4. **Database**: Check database to verify operations actually complete
5. **Files**: Check filesystem to verify file operations

---

## Known Limitations

1. Page reloads after successful operation (intentional design)
2. Doesn't support undo functionality yet
3. No real-time progress for long operations

---

## Reporting Issues

When reporting issues, include:
- Browser and version
- Console errors (screenshot)
- Network request details (from DevTools)
- Steps to reproduce
- Expected vs actual behavior
