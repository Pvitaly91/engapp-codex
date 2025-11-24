# Auto-Generated Tests UI Description

## Overview

This document describes the user interface changes made for the auto-generated tests feature.

## Location

The auto-generated tests section appears on:
1. **Theory Pages** (`/pages/{category}/{page}`)
2. **Category Pages** (`/pages/{category}`)

## Visual Layout

### Section Header
```
╔════════════════════════════════════════════════════════════╗
║  Згенеровані тести                          [Згорнути ▼]  ║
╚════════════════════════════════════════════════════════════╝
```

- Title: "Згенеровані тести" (Generated Tests)
- Collapsible section with expand/collapse button
- Default state: Expanded

### Test Cards Grid

Cards are displayed in a responsive grid:
- **Mobile (< 640px)**: 1 column
- **Tablet (640px - 1024px)**: 2 columns
- **Desktop (> 1024px)**: 3 columns

### Individual Test Card

```
╔═══════════════════════════════════════╗
║  Тест A1-A2                          ║
║                                       ║
║  Рівні: A1, A2                       ║
║  Питань: 15 / 45 доступно            ║
║                                       ║
║  [tag1] [tag2] [tag3] [+2]          ║
║                                       ║
║  ┌───────────────────────────────┐   ║
║  │      Пройти тест              │   ║
║  └───────────────────────────────┘   ║
╚═══════════════════════════════════════╝
```

**Card Components:**

1. **Header**: Test name showing level pair (e.g., "Тест A1-A2")
2. **Level Info**: Shows both levels in the pair
3. **Question Count**: Shows selected questions / total available
4. **Tags**: Shows up to 3 tags with overflow indicator
5. **Action Button**: "Пройти тест" (Take Test) button

### Example with 5 Tests

```
╔════════════════════════════════════════════════════════════════════════╗
║  Згенеровані тести                                    [Згорнути ▼]    ║
╠════════════════════════════════════════════════════════════════════════╣
║                                                                         ║
║  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                ║
║  │  Тест A1-A2  │  │  Тест A2-B1  │  │  Тест B1-B2  │                ║
║  │  Рівні: A1,A2│  │  Рівні: A2,B1│  │  Рівні: B1,B2│                ║
║  │  15 / 45     │  │  15 / 38     │  │  15 / 52     │                ║
║  │  [tag1][tag2]│  │  [tag2][tag3]│  │  [tag3][tag4]│                ║
║  │  [Пройти тест]│  │  [Пройти тест]│  │  [Пройти тест]│                ║
║  └──────────────┘  └──────────────┘  └──────────────┘                ║
║                                                                         ║
║  ┌──────────────┐  ┌──────────────┐                                   ║
║  │  Тест B2-C1  │  │  Тест C1-C2  │                                   ║
║  │  Рівні: B2,C1│  │  Рівні: C1,C2│                                   ║
║  │  15 / 29     │  │  15 / 21     │                                   ║
║  │  [tag4][tag5]│  │  [tag5][tag6]│                                   ║
║  │  [Пройти тест]│  │  [Пройти тест]│                                   ║
║  └──────────────┘  └──────────────┘                                   ║
║                                                                         ║
╚════════════════════════════════════════════════════════════════════════╝
```

## Test View

When a user clicks "Пройти тест", they are taken to the test page:

```
╔════════════════════════════════════════════════════════════╗
║  Авто-тест A1-A2 (tag1, tag2, tag3)          [← Назад]   ║
╠════════════════════════════════════════════════════════════╣
║  Рівні: A1-A2                                             ║
║  Кількість питань: 15                                      ║
║  Теги: tag1, tag2, tag3                                   ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  1. [Question text with input field]                      ║
║     Level: A1                                             ║
║     Tags: [tag1] [tag2]                                   ║
║                                                            ║
║  2. [Question text with input field]                      ║
║     Level: A2                                             ║
║     Tags: [tag2] [tag3]                                   ║
║                                                            ║
║  ... (13 more questions)                                  ║
║                                                            ║
║  ┌────────────────┐                                       ║
║  │   Перевірити   │                                       ║
║  └────────────────┘                                       ║
╚════════════════════════════════════════════════════════════╝
```

## Section Placement

The auto-generated tests section is placed:

1. **On Theory Pages**: After the page tags, before the "Пов'язані тести" (Related Tests) section
2. **On Category Pages**: After the category tags, before the "Пов'язані тести" (Related Tests) section

## Design Consistency

The auto-generated tests section follows the same design patterns as the "Пов'язані тести" section:

- **Same card style**: Border, shadow, rounded corners
- **Same grid layout**: Responsive 1-2-3 column layout
- **Same interaction**: Hover effects, button styles
- **Same expandable behavior**: Collapsible with Alpine.js

## No Tests Available

If no tests can be generated (not enough questions), the section is not displayed at all.

## Color Scheme

Uses the application's existing color scheme:
- **Primary**: Button colors, hover states
- **Secondary**: Tag colors
- **Muted**: Secondary text (counts, levels)
- **Border**: Card borders, dividers

## Responsive Behavior

### Mobile (< 640px)
- Single column layout
- Full-width cards
- Stacked vertically
- All text readable without horizontal scrolling

### Tablet (640px - 1024px)
- Two-column grid
- Cards adjust to available space
- Good balance between content and whitespace

### Desktop (> 1024px)
- Three-column grid
- Maximum visual efficiency
- Consistent spacing and alignment

## Accessibility

- **Semantic HTML**: Proper heading hierarchy
- **Keyboard navigation**: All interactive elements accessible via keyboard
- **ARIA labels**: Proper labeling for screen readers
- **Focus indicators**: Clear focus states for all interactive elements
- **Color contrast**: Sufficient contrast for all text

## Animation

- **Expand/Collapse**: Smooth transition using Alpine.js `x-collapse`
- **Hover effects**: Subtle color and shadow transitions on cards
- **Button interactions**: Visual feedback on click

## Loading State

Currently, tests are generated on page load. No loading state is shown as the generation is fast (metadata only).

## Error State

If tests cannot be generated due to an error, the section is simply not displayed. No error message is shown to the user.
