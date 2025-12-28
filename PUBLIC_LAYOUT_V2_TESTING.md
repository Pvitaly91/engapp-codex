# New Public Layout V2 - Testing Checklist

## Pre-Deployment Checklist

### Phase 1: Setup Verification
- [ ] Database connection configured
- [ ] Language Manager module activated
- [ ] At least 2 languages configured in database
- [ ] Sample pages/tests exist in database
- [ ] Sample words with translations exist

### Phase 2: Route Testing

#### Search Route Migration
- [ ] Visit `/search` (should work)
- [ ] Visit `/admin/search` (should still work for admin, but public shouldn't use it)
- [ ] Test search with query: `/search?q=test`
- [ ] Test JSON response: `/search?q=test` with `Accept: application/json` header
- [ ] Verify no broken links from old route

#### API Endpoints
- [ ] Test word search API: `/api/search?lang=uk&q=go`
- [ ] Test word search API: `/api/search?lang=en&q=go`
- [ ] Verify response format matches expectations

### Phase 3: Layout and Design

#### Header Component
Desktop (≥ 1024px):
- [ ] Logo displays correctly
- [ ] Logo links to home page
- [ ] Navigation menu is horizontal
- [ ] All nav links work (Catalog, Theory, Words, Verbs)
- [ ] Search button displays and is clickable
- [ ] Language switcher displays correctly
- [ ] Theme toggle button displays
- [ ] Dark/light mode toggle works
- [ ] No horizontal scrolling

Mobile (< 1024px):
- [ ] Logo displays (compact version)
- [ ] Hamburger menu button visible
- [ ] Hamburger menu opens/closes
- [ ] Search button visible
- [ ] Language switcher works on mobile
- [ ] Theme toggle works on mobile
- [ ] Mobile menu contains all navigation links
- [ ] Mobile menu closes when link clicked
- [ ] No content overflow

#### Footer Component
Desktop:
- [ ] 4-column layout displays
- [ ] Brand section with logo
- [ ] Navigation links section
- [ ] Resources section
- [ ] Trust badges section
- [ ] Copyright year is current
- [ ] All links work

Mobile:
- [ ] Footer stacks vertically
- [ ] All sections visible
- [ ] No content overflow
- [ ] Links remain clickable

### Phase 4: Language Switcher

#### Basic Functionality
- [ ] Switcher displays current language code
- [ ] Switcher displays country flag
- [ ] Clicking opens dropdown
- [ ] Dropdown displays all available languages
- [ ] Each language shows flag + name + code
- [ ] Current language is highlighted
- [ ] Current language has checkmark
- [ ] Clicking language changes locale
- [ ] Page reloads with new language
- [ ] Dropdown closes after selection

#### Search Functionality (if 5+ languages)
- [ ] Search input appears in dropdown
- [ ] Typing filters language list
- [ ] Filter works by code (e.g., "en")
- [ ] Filter works by name (e.g., "English")
- [ ] Case-insensitive search works
- [ ] "No results" message shows when nothing matches
- [ ] Clearing search shows all languages again

#### Edge Cases
- [ ] Works with 2 languages
- [ ] Works with 10+ languages
- [ ] Scrollbar appears when list is long
- [ ] Long language names don't overflow
- [ ] Clicking outside closes dropdown
- [ ] ESC key closes dropdown
- [ ] No console errors

### Phase 5: Search Modal

#### Opening/Closing
- [ ] Search button in header opens modal
- [ ] Modal appears with animation
- [ ] Backdrop overlay appears
- [ ] Clicking backdrop closes modal
- [ ] ESC key closes modal
- [ ] Close button (X) works
- [ ] Input is auto-focused when opened

#### Pages/Tests Tab
- [ ] Tab is active by default
- [ ] Search input displays placeholder
- [ ] Typing triggers search (after 300ms debounce)
- [ ] Loading spinner appears during search
- [ ] Results display after search completes
- [ ] Each result shows title and type (Theory/Test)
- [ ] Clicking result navigates to page
- [ ] Empty state shows when no results
- [ ] "Start typing" message shows initially
- [ ] Enter key submits to full results page
- [ ] Special characters in query work (e.g., "ї", "é")

#### Dictionary Tab
- [ ] Clicking tab switches to Dictionary
- [ ] Input is re-focused after tab switch
- [ ] Search input displays different placeholder
- [ ] Typing triggers search (after 300ms debounce)
- [ ] Loading spinner appears during search
- [ ] Results show English word
- [ ] Results show translation below word
- [ ] "No translation available" shows when empty
- [ ] Empty state shows when no results
- [ ] "Start typing" message shows initially
- [ ] Dictionary icon appears in results
- [ ] Special characters work

#### Edge Cases
- [ ] Switching tabs preserves current tab's input
- [ ] Rapid typing doesn't cause multiple API calls
- [ ] Network errors are handled gracefully
- [ ] Long words don't overflow
- [ ] Many results are scrollable
- [ ] No console errors

### Phase 6: Page-Specific Testing

For each migrated page, verify:

#### /search Results Page
- [ ] Page uses public-v2 layout
- [ ] Header displays correctly
- [ ] Footer displays correctly
- [ ] Search query displays in results
- [ ] Results cards display properly
- [ ] Empty state displays when no results
- [ ] Link to catalog works
- [ ] Page is responsive on mobile

#### /catalog/tests-cards
- [ ] Page uses public-v2 layout
- [ ] Header displays correctly
- [ ] Footer displays correctly
- [ ] Test cards display properly
- [ ] Filters work (if present)
- [ ] Page is responsive on mobile
- [ ] No admin links visible

#### /theory (Index)
- [ ] Page uses public-v2 layout
- [ ] Header displays correctly
- [ ] Footer displays correctly
- [ ] Category list displays
- [ ] Hero section displays
- [ ] All links work
- [ ] Page is responsive on mobile
- [ ] No admin links visible

#### /theory/category/page
- [ ] Page uses public-v2 layout
- [ ] Header displays correctly
- [ ] Footer displays correctly
- [ ] Breadcrumb displays
- [ ] Content displays properly
- [ ] Page is responsive on mobile
- [ ] No admin links visible

#### /words/test
- [ ] Page uses public-v2 layout
- [ ] Header displays correctly
- [ ] Footer displays correctly
- [ ] Test interface works
- [ ] Page is responsive on mobile
- [ ] No admin links visible

#### /verbs/test
- [ ] Page uses public-v2 layout
- [ ] Header displays correctly
- [ ] Footer displays correctly
- [ ] Test interface works
- [ ] Page is responsive on mobile
- [ ] No admin links visible

### Phase 7: Responsive Design

#### Mobile Portrait (320px - 479px)
- [ ] All pages render without horizontal scroll
- [ ] Text is readable (no tiny fonts)
- [ ] Buttons are tappable (min 44x44px)
- [ ] Images scale appropriately
- [ ] Navigation works
- [ ] Search works
- [ ] Language switcher works

#### Mobile Landscape (480px - 767px)
- [ ] All pages render correctly
- [ ] Layout adapts appropriately
- [ ] All interactions work

#### Tablet (768px - 1023px)
- [ ] All pages render correctly
- [ ] Layout uses tablet-optimized spacing
- [ ] Navigation is accessible
- [ ] All interactions work

#### Desktop (1024px+)
- [ ] All pages render correctly
- [ ] Full horizontal navigation visible
- [ ] Footer is 4-column layout
- [ ] Hover effects work
- [ ] All interactions work

### Phase 8: Dark Mode

#### Theme Toggle
- [ ] Toggle button visible in header
- [ ] Clicking toggles dark/light mode
- [ ] Preference is saved to localStorage
- [ ] Page doesn't flash on reload
- [ ] System preference is respected initially

#### Dark Mode Colors
- [ ] Background is dark
- [ ] Text is light and readable
- [ ] Primary colors are adjusted
- [ ] Borders are visible
- [ ] Cards have appropriate contrast
- [ ] Links are visible
- [ ] Hover states work
- [ ] Focus states work

#### All Components in Dark Mode
- [ ] Header looks good
- [ ] Footer looks good
- [ ] Search modal looks good
- [ ] Language switcher looks good
- [ ] All pages look good
- [ ] No readability issues

### Phase 9: Accessibility

#### Keyboard Navigation
- [ ] Tab key moves through interactive elements
- [ ] Focus indicators are visible
- [ ] Tab order is logical
- [ ] ESC closes modals
- [ ] Enter activates buttons/links
- [ ] No keyboard traps
- [ ] Skip to content link (if present)

#### Screen Reader
- [ ] ARIA labels are present on interactive elements
- [ ] Form inputs have labels
- [ ] Buttons have descriptive text or aria-label
- [ ] Modal has role="dialog"
- [ ] Focus is managed in modals
- [ ] Landmarks are used (header, nav, main, footer)
- [ ] Heading hierarchy is correct (h1, h2, h3...)

#### Visual
- [ ] Color contrast meets WCAG AA (4.5:1 for text)
- [ ] Focus indicators are clearly visible
- [ ] No information conveyed by color alone
- [ ] Text can be resized to 200%
- [ ] Content reflows without horizontal scroll

### Phase 10: Performance

#### Load Time
- [ ] Page loads in under 3 seconds
- [ ] Layout shift is minimal (CLS < 0.1)
- [ ] First contentful paint < 1.8s
- [ ] No render-blocking resources

#### Runtime Performance
- [ ] Animations are smooth (60fps)
- [ ] Search is responsive (feels instant)
- [ ] Debouncing prevents excessive API calls
- [ ] No memory leaks (check DevTools)
- [ ] Scrolling is smooth

#### Network
- [ ] Tailwind CDN loads successfully
- [ ] Alpine.js CDN loads successfully
- [ ] Google Fonts load successfully
- [ ] API calls complete successfully
- [ ] Failure states handle network errors

### Phase 11: Browser Compatibility

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Chrome Mobile (Android)
- [ ] Safari Mobile (iOS)

For each browser:
- [ ] Layout renders correctly
- [ ] All interactions work
- [ ] Animations are smooth
- [ ] No console errors
- [ ] Dark mode works

### Phase 12: Translation Verification

For each language (EN, UK, PL):
- [ ] All navigation labels translated
- [ ] All search placeholders translated
- [ ] All button labels translated
- [ ] All footer text translated
- [ ] All empty states translated
- [ ] All error messages translated
- [ ] No missing translation keys
- [ ] No hardcoded English text

## Success Criteria

The deployment is successful when:
- [ ] All checklist items are verified
- [ ] No critical bugs found
- [ ] Performance is acceptable
- [ ] Accessibility standards met
- [ ] User feedback is positive
- [ ] All documentation is complete

---

**Tested By:** _________________
**Date:** _________________
**Environment:** [ ] Development / [ ] Staging / [ ] Production
