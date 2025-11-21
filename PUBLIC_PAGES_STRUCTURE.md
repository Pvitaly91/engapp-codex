# Public Pages Structure Analysis

## Overview
This document provides a detailed analysis of all public pages (non-admin) in the Gramlyze platform, showing their structure, features, and how they use the engram layout.

---

## 1. Home Page (`/`)

**Route**: `Route::get('/', [HomeController::class, 'index'])->name('home')`  
**View**: `resources/views/home.blade.php`  
**Layout**: `@extends('layouts.engram')`

### Structure
```
┌─────────────────────────────────────────┐
│ HEADER (Sticky Navigation)              │
│ - Logo, Search, Nav Links, CTA          │
├─────────────────────────────────────────┤
│ HERO SECTION                            │
│ ┌─────────────────────────────────────┐ │
│ │ • Title & Tagline                   │ │
│ │ • Platform Description              │ │
│ │ • Primary CTAs (2 buttons)          │ │
│ │ • Stats Cards (3 metrics)           │ │
│ │ • Feature Highlight Card            │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ PLATFORM MAP SECTION                    │
│ ┌──────────┬──────────┐                │
│ │ Каталог  │ Конструк │                │
│ │ тестів   │ тор      │                │
│ ├──────────┼──────────┤                │
│ │ Теорія   │ Рецензії │                │
│ └──────────┴──────────┘                │
│ (4 Feature Cards)                       │
├─────────────────────────────────────────┤
│ WORKFLOW SECTION                        │
│ ┌──────────────────────────────────────┐│
│ │ 3 Step Cards:                        ││
│ │ 1. Знайти ресурс                     ││
│ │ 2. Почати урок                       ││
│ │ 3. Поділитися                        ││
│ └──────────────────────────────────────┘│
├─────────────────────────────────────────┤
│ AI TOOLKIT SECTION                      │
│ ┌─────────────────┬─────────────────┐  │
│ │ AI Feature Card │ Highlight Panel │  │
│ │ - Explanation   │ - Quick facts   │  │
│ │ - Features list │ - Benefits      │  │
│ └─────────────────┴─────────────────┘  │
├─────────────────────────────────────────┤
│ FINAL CTA SECTION                       │
│ ┌─────────────────────────────────────┐ │
│ │ Call to Action with gradient bg     │ │
│ │ - Compelling message                │ │
│ │ - 2 CTA buttons                     │ │
│ │ - Feature highlight card            │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ FOOTER                                  │
│ - Brand info, Links, Theme toggle       │
└─────────────────────────────────────────┘
```

### Key Features
- **Hero with gradient background**: Vibrant colors with radial gradients
- **Stats showcase**: 120+ Categories, 2,400+ AI hints, 7,500+ Tags
- **4 Product modules**: Catalog, Constructor, Theory, Reviews
- **Scroll animations**: `data-animate` with staggered delays
- **Responsive grid**: 1 column mobile → 2-3 columns desktop

### Data Requirements
```php
Controller passes:
- latestTests (SavedGrammarTest collection)
- featuredCategories (PageCategory collection)
- recentPages (Page collection)
- stats (array with counts)
```

---

## 2. Theory Pages Index (`/pages`)

**Route**: `Route::get('/pages', [PageController::class, 'index'])`  
**View**: `resources/views/engram/pages/index.blade.php`  
**Layout**: `@extends('layouts.engram')`

### Structure
```
┌─────────────────────────────────────────┐
│ HEADER (Sticky Navigation)              │
├─────────────────────────────────────────┤
│ ┌──────────┬───────────────────────────┐│
│ │ SIDEBAR  │ MAIN CONTENT              ││
│ │ (260px)  │                           ││
│ │          │ ┌───────────────────────┐ ││
│ │ Category │ │ Category Header/Desc  │ ││
│ │ List     │ └───────────────────────┘ ││
│ │          │                           ││
│ │ - Cat 1  │ ┌────┬────┬────┐         ││
│ │ - Cat 2  │ │Page│Page│Page│         ││
│ │ - Cat 3  │ │Card│Card│Card│         ││
│ │ - Cat 4  │ ├────┼────┼────┤         ││
│ │   ...    │ │Page│Page│Page│         ││
│ │          │ └────┴────┴────┘         ││
│ │          │ (Responsive Grid)        ││
│ └──────────┴───────────────────────────┘│
├─────────────────────────────────────────┤
│ FOOTER                                  │
└─────────────────────────────────────────┘
```

### Key Features
- **Sidebar navigation**: Sticky, category list with page counts
- **Category description**: Rich text blocks with two-column layout
- **Page grid**: 2-3 column responsive grid
- **Mobile navigation**: Collapsible categories
- **Empty state**: Dashed border card for no content

### Data Requirements
```php
- categories (PageCategory collection)
- selectedCategory (PageCategory)
- categoryPages (Page collection)
- categoryDescription (array with blocks)
```

---

## 3. Theory Page Detail (`/pages/{category}/{page}`)

**Route**: `Route::get('/pages/{category:slug}/{pageSlug}')`  
**View**: `resources/views/engram/pages/show.blade.php`  
**Layout**: `@extends('layouts.engram')`

### Structure
```
┌─────────────────────────────────────────┐
│ HEADER (Sticky Navigation)              │
├─────────────────────────────────────────┤
│ ┌──────────┬───────────────────────────┐│
│ │ SIDEBAR  │ PAGE CONTENT              ││
│ │          │                           ││
│ │ Category │ ┌───────────────────────┐ ││
│ │ Nav      │ │ Page Title            │ ││
│ │          │ │ Subtitle (optional)   │ ││
│ │ Pages in │ └───────────────────────┘ ││
│ │ Category │                           ││
│ │          │ ┌──────────┬───────────┐ ││
│ │ - Page 1 │ │ LEFT COL │ RIGHT COL │ ││
│ │ - Page 2 │ │ Content  │ Content   │ ││
│ │ - Page 3 │ │ Blocks   │ Blocks    │ ││
│ │   ...    │ │          │           │ ││
│ │          │ └──────────┴───────────┘ ││
│ │          │ (Two-column text blocks) ││
│ │          │                           ││
│ │ Related  │ ┌───────────────────────┐ ││
│ │ Pages    │ │ Related Pages Grid    │ ││
│ │ (3 cards)│ └───────────────────────┘ ││
│ └──────────┴───────────────────────────┘│
├─────────────────────────────────────────┤
│ FOOTER                                  │
└─────────────────────────────────────────┘
```

### Key Features
- **Breadcrumbs**: Home → Теорія → Category → Page
- **Text blocks system**: Flexible content blocks with types and columns
- **Two-column layout**: Left and right content blocks
- **Sidebar**: Categories and pages navigation
- **Related pages**: 3 random pages from same category
- **Mobile navigation**: Collapsible sidebar sections

### Data Requirements
```php
- page (Page model with textBlocks)
- breadcrumbs (array)
- subtitleBlock (TextBlock or null)
- columns (array: left and right blocks)
- locale (string)
- categories (collection)
- selectedCategory (PageCategory)
- categoryPages (collection)
```

---

## 4. Test Catalog (`/catalog-tests/cards`)

**Route**: `Route::get('/catalog-tests/cards', [GrammarTestController::class, 'catalog'])`  
**View**: `resources/views/engram/catalog-tests-cards.blade.php`  
**Layout**: `@extends('layouts.engram')`

### Structure
```
┌─────────────────────────────────────────┐
│ HEADER (Sticky Navigation)              │
├─────────────────────────────────────────┤
│ ┌──────────┬───────────────────────────┐│
│ │ FILTER   │ TEST GRID                 ││
│ │ SIDEBAR  │                           ││
│ │ (248px)  │ ┌────┬────┬────┐         ││
│ │          │ │Test│Test│Test│         ││
│ │ Level    │ │Card│Card│Card│         ││
│ │ ☐ A1     │ ├────┼────┼────┤         ││
│ │ ☐ A2     │ │Test│Test│Test│         ││
│ │ ☐ B1     │ │Card│Card│Card│         ││
│ │ ☐ B2     │ ├────┼────┼────┤         ││
│ │          │ │Test│Test│Test│         ││
│ │ Category │ └────┴────┴────┘         ││
│ │ ☐ Tag 1  │ (3-column grid)          ││
│ │ ☐ Tag 2  │                           ││
│ │ ☐ Tag 3  │ Empty State:              ││
│ │   ...    │ "Ще немає тестів"         ││
│ │          │                           ││
│ │ [Reset]  │                           ││
│ └──────────┴───────────────────────────┘│
│ (Mobile: Filter button shows sidebar)   │
├─────────────────────────────────────────┤
│ FOOTER                                  │
└─────────────────────────────────────────┘
```

### Key Features
- **Filter sidebar**: Level and tag checkboxes, sticky on desktop
- **Auto-submit filters**: Checkbox changes trigger form submit
- **Test cards**: Name, date, question count, levels, tags, description
- **Tag display**: Pill-style tags with categories
- **CTA button**: "Пройти тест" leads to test interface
- **Mobile filter**: Toggle button shows/hides filter panel
- **"Others" category**: Collapsible tag section
- **Reset filter**: Link to clear all selections

### Test Card Contents
```
┌──────────────────────────────┐
│ Test Name (Bold)             │
│ Created: DD.MM.YYYY          │
│ Questions: N                 │
│ Levels: A1, B2, C1           │
│ ┌──┬──┬──┬──┐               │
│ │T1│T2│T3│T4│ (Tags)        │
│ └──┴──┴──┴──┘               │
│ Description preview...       │
│ [Пройти тест] (CTA Button)  │
└──────────────────────────────┘
```

### Data Requirements
```php
- tests (SavedGrammarTest collection)
- tags (array grouped by category)
- selectedTags (array)
- availableLevels (collection)
- selectedLevels (array)
```

---

## 5. Test Interface (`/test/{slug}/js`)

**Route**: `Route::get('/test/{slug}/js', [GrammarTestController::class, 'showSavedTestJs'])`  
**View**: `resources/views/engram/saved-test-js.blade.php`  
**Layout**: `@extends('layouts.engram')`

### Structure
```
┌─────────────────────────────────────────┐
│ HEADER (Sticky Navigation)              │
├─────────────────────────────────────────┤
│ TEST HEADER                             │
│ ┌─────────────────────────────────────┐ │
│ │ Test Name                           │ │
│ │ Instructions                        │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ MODE NAVIGATION                         │
│ [Normal] [Step] [Random] [Manual]...    │
├─────────────────────────────────────────┤
│ WORD SEARCH TOOL                        │
├─────────────────────────────────────────┤
│ PROGRESS BAR                            │
│ ████████░░░░░░░░ 45% (9/20)            │
├─────────────────────────────────────────┤
│ QUESTION CARDS (Stack)                  │
│ ┌─────────────────────────────────────┐ │
│ │ Question 1                          │ │
│ │ "Fill in the blank: ___"            │ │
│ │ ○ Option A                          │ │
│ │ ○ Option B                          │ │
│ │ ○ Option C                          │ │
│ │ ○ Option D                          │ │
│ │ [Check Answer] [Get Hint]           │ │
│ │ Status: ✓ Correct / ✗ Wrong         │ │
│ └─────────────────────────────────────┘ │
│ ... (more questions)                    │
├─────────────────────────────────────────┤
│ SUMMARY (Hidden until complete)         │
│ ┌─────────────────────────────────────┐ │
│ │ Final Score: 85% (17/20)            │ │
│ │ [Retry] [Show Wrong Answers]        │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ FOOTER                                  │
└─────────────────────────────────────────┘
```

### Key Features
- **Multiple test modes**: 
  - Normal (all questions visible)
  - Step (one at a time)
  - Random (shuffled order)
  - Manual (select options)
  - Input (type answers)
  - Drag & Drop
  - Match
  - Dialogue
- **Interactive questions**: Radio buttons, instant feedback
- **Progress tracking**: Visual progress bar, percentage
- **Answer checking**: Immediate validation with explanations
- **Hints system**: AI-powered hints via button
- **Word search**: Integrated dictionary lookup
- **Keyboard shortcuts**: Numbers 1-4 for options
- **State persistence**: Saves progress in session/localStorage
- **Restart button**: Clear progress and start over

### JavaScript Features
```javascript
- Question state management
- Answer validation
- Progress calculation
- Keyboard event handlers
- AJAX for hints/explanations
- LocalStorage for persistence
- Smooth scrolling to questions
```

### Data Requirements
```php
- test (SavedGrammarTest model)
- questionData (JSON array)
- jsStateMode (string: 'session' or 'localStorage')
- savedState (array or null)
```

---

## 6. Search Results (`/search`)

**Route**: `Route::get('/search', SiteSearchController::class)`  
**View**: `resources/views/search/results.blade.php`  
**Layout**: `@extends('layouts.engram')`

### Structure
```
┌─────────────────────────────────────────┐
│ HEADER (Sticky Navigation)              │
│ (Search box pre-filled with query)      │
├─────────────────────────────────────────┤
│ SEARCH RESULTS HEADER                   │
│ ┌─────────────────────────────────────┐ │
│ │ "Результати пошуку"                 │ │
│ │ Знайдено для: "query text"          │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ RESULTS (if found)                      │
│ ┌────┬────┬────┐                       │
│ │Res │Res │Res │                       │
│ │ult │ult │ult │                       │
│ │ 1  │ 2  │ 3  │                       │
│ ├────┼────┼────┤                       │
│ │Res │Res │Res │                       │
│ │ult │ult │ult │                       │
│ │ 4  │ 5  │ 6  │                       │
│ └────┴────┴────┘                       │
│ (3-column grid, responsive)             │
│                                         │
│ OR EMPTY STATE (if no results)         │
│ ┌─────────────────────────────────────┐ │
│ │        🔍 (Search icon)             │ │
│ │    "Нічого не знайдено"             │ │
│ │ "Спробуйте інший запит..."          │ │
│ │    [До каталогу] (CTA)              │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ FOOTER                                  │
└─────────────────────────────────────────┘
```

### Result Card Structure
```
┌──────────────────────────────┐
│ Result Title                 │
│ [Теорія] or [Тест] (Badge)  │
│ Перейти → (Link with arrow)  │
└──────────────────────────────┘
```

### Key Features
- **Unified search**: Pages and Tests in single results
- **Type badges**: Visual indicators for content type
- **Empty state**: Friendly message with CTA to catalog
- **Hover effects**: Card lift and color transitions
- **Animations**: Scroll-triggered fade-ins
- **Responsive grid**: 1→2→3 columns based on screen size

### Data Requirements
```php
- query (string: search term)
- results (collection of items with title, type, url)
```

### Search API
```php
Endpoint: GET /search?q={query}
Accept: application/json (for autocomplete)
Response: [
    {title: "...", type: "page|test", url: "..."},
    ...
]
```

---

## 7. Login Page (`/login`)

**Route**: `Route::get('/login', [AuthController::class, 'showLoginForm'])`  
**View**: `resources/views/auth/login.blade.php`  
**Layout**: Standalone (no layout)

### Structure
```
┌─────────────────────────────────────────┐
│ Centered Login Form (bg-gray-100)       │
│                                         │
│     ┌─────────────────────────┐         │
│     │ "Вхід до адмін-панелі"  │         │
│     │                         │         │
│     │ [Логін field]           │         │
│     │ [Пароль field]          │         │
│     │ ☐ Запам'ятати мене      │         │
│     │ [Увійти button]         │         │
│     └─────────────────────────┘         │
│                                         │
└─────────────────────────────────────────┘
```

### Key Features
- **Standalone page**: No header/footer
- **Centered card**: White card on gray background
- **Form validation**: Error messages below fields
- **Remember me**: Checkbox for persistent login
- **Responsive**: Mobile-friendly form
- **Minimal design**: Clean, focused on task

**Note**: This is an admin login page, so it intentionally does not use the public engram layout.

---

## Design Patterns Summary

### Common Components Across Pages

1. **Header Navigation**
   - Present on all pages using engram layout
   - Sticky positioning
   - Logo, search, navigation links
   - Mobile hamburger menu
   - Predictive search dropdown

2. **Footer**
   - Brand information
   - Legal links
   - Theme toggle (dark/light)
   - Admin login link
   - Trust badges

3. **Card Components**
   - Rounded corners (rounded-2xl)
   - Border with transparency (border-border/70)
   - Shadow (shadow-soft)
   - Hover effects (translate-y, shadow)
   - Consistent padding

4. **Buttons**
   - Primary: bg-primary, rounded-full, white text
   - Secondary: border, rounded-full, hover effects
   - Consistent sizing and padding

5. **Grid Layouts**
   - Responsive breakpoints (sm, md, lg)
   - Gap spacing (gap-4, gap-6)
   - 1→2→3 column progression

6. **Animations**
   - data-animate attribute
   - Fade-in on scroll
   - Staggered delays
   - Respects reduced motion

### Responsive Patterns

```
Mobile (< 640px):
- Single column layouts
- Hamburger menu
- Stacked cards
- Full-width buttons

Tablet (640-1024px):
- 2-column grids
- Visible navigation
- Sidebar visible
- Mixed layouts

Desktop (> 1024px):
- 3-column grids
- Sidebar + content
- Full navigation
- Spacious layouts
```

### Color Usage

**Primary Purple**: CTAs, links, highlights
**Secondary Cyan**: Accents, secondary features
**Accent Orange**: Special highlights, warnings
**Muted Gray**: Text, borders, backgrounds
**Success Green**: Confirmations, correct answers
**Destructive Red**: Errors, delete actions

---

## Accessibility Compliance

All public pages include:
- ✅ Semantic HTML (header, nav, main, footer)
- ✅ ARIA labels for interactive elements
- ✅ Keyboard navigation support
- ✅ Focus indicators
- ✅ Alt text for images
- ✅ Color contrast compliance
- ✅ Responsive text scaling
- ✅ Skip links (where applicable)
- ✅ Screen reader support

---

## Performance Considerations

1. **CDN Resources**: Tailwind, Alpine.js, Google Fonts
2. **Lazy Loading**: Images load on demand
3. **Minimal JS**: Only essential interactions
4. **CSS Variables**: Dynamic theming
5. **Debounced Search**: Reduced API calls
6. **IntersectionObserver**: Efficient animations

---

## File Structure

```
resources/views/
├── layouts/
│   ├── engram.blade.php          ← Main public layout
│   └── app.blade.php             ← Admin layout
├── home.blade.php                ← Homepage
├── search/
│   └── results.blade.php         ← Search results
├── auth/
│   └── login.blade.php           ← Login (standalone)
├── engram/
│   ├── catalog-tests-cards.blade.php
│   ├── catalog-tests-cards-aggregated.blade.php
│   ├── saved-test-js*.blade.php  ← Various test modes
│   └── pages/
│       ├── index.blade.php       ← Theory index
│       ├── show.blade.php        ← Theory detail
│       └── partials/
│           ├── sidebar.blade.php
│           ├── page-grid.blade.php
│           └── grammar-card.blade.php
└── components/
    ├── gramlyze-logo.blade.php
    ├── breadcrumbs.blade.php
    ├── test-mode-nav.blade.php
    ├── word-search.blade.php
    ├── saved-test-progress.blade.php
    └── saved-test-js-*.blade.php
```

---

## Conclusion

All public pages (non `/admin/` routes) now use the consistent engram layout, providing:
- Modern, cohesive design language
- Dark mode support
- Responsive mobile-first approach
- Accessibility compliance
- Smooth animations and interactions
- Predictive search functionality
- Maintainable component structure

This creates a professional, unified experience for all users visiting the public sections of the Gramlyze platform.
