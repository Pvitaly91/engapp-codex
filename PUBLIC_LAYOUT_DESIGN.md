# Public Layout Design - Gramlyze Platform

## Overview
This document describes the new public layout design for the Gramlyze platform. The design applies to all public-facing pages (non-admin pages) and provides a modern, consistent user experience.

## Design Philosophy

### Core Principles
1. **Consistency**: Single layout for all public pages
2. **Accessibility**: ARIA labels, keyboard navigation, semantic HTML
3. **Performance**: CDN-based Tailwind, optimized animations
4. **Responsiveness**: Mobile-first design with adaptive components
5. **Modern UX**: Smooth animations, hover effects, dark mode support

## Layout Structure

### File: `resources/views/layouts/engram.blade.php`

The main public layout consists of three primary sections:

### 1. Header (Navigation)
**Location**: Sticky top navigation with backdrop blur

**Features**:
- **Logo**: Gramlyze logo with variants (full/compact for mobile)
- **Search Bar**: Integrated predictive search with autocomplete
- **Navigation Links**:
  - Каталог (Catalog)
  - Теорія (Theory)
  - Рецензії (Reviews)
  - AI Toolkit
  - Командам (For Teams)
  - Розпочати (Start) - Primary CTA button
- **Mobile Menu**: Hamburger menu with slide-out navigation
- **Accessibility**: Full keyboard navigation, ARIA labels

**Technical Details**:
```css
Position: sticky top-0
Z-index: 40
Background: backdrop-blur with bg-background/85
Border: bottom border with border-border/70
```

### 2. Main Content Area
**Location**: Between header and footer

**Features**:
- Maximum width container (80rem / 1280px)
- Centered with horizontal padding
- Vertical spacing (py-10)
- Supports nested content with proper spacing

**Technical Details**:
```css
Container: page-shell (max-width: 80rem)
Padding: px-4 py-10
Margin: mx-auto (centered)
```

### 3. Footer
**Location**: Bottom of page

**Features**:
- **Brand Section**:
  - Gramlyze logo and name
  - Platform description
  - Trust badges (Security, Support, Quick Start)
- **Navigation Section**:
  - Legal links (Політика, Умови, Підтримка)
  - Dark/Light theme toggle
  - Admin login link
- **Responsive Grid**: Two-column on desktop, single column on mobile

## Design System

### Color Palette

#### Light Theme
```css
/* Backgrounds */
--background: 210 40% 98%    /* Very light blue-gray */
--card: 0 0% 100%            /* Pure white */
--muted: 210 40% 96%         /* Light gray-blue */

/* Foregrounds */
--foreground: 215 31% 18%    /* Dark blue-gray */
--muted-foreground: 215 16% 40%  /* Medium gray */

/* Brand Colors */
--primary: 253 85% 63%       /* Vibrant purple */
--secondary: 188 82% 47%     /* Cyan blue */
--accent: 31 94% 55%         /* Orange */

/* Semantic */
--success: 142 76% 36%       /* Green */
--warning: 38 92% 50%        /* Amber */
--info: 217 91% 60%          /* Blue */
--destructive: 0 84% 60%     /* Red */

/* Borders */
--border: 214 32% 89%        /* Light border */
```

#### Dark Theme
```css
/* Backgrounds */
--background: 222 15% 10%    /* Very dark blue */
--card: 222 15% 13%          /* Dark card */
--muted: 222 15% 16%         /* Muted dark */

/* Foregrounds */
--foreground: 0 0% 98%       /* Near white */
--muted-foreground: 0 0% 80% /* Light gray */

/* Brand Colors (adjusted for dark) */
--primary: 253 85% 70%       /* Lighter purple */
--secondary: 188 85% 52%     /* Brighter cyan */
--accent: 31 94% 60%         /* Brighter orange */
```

### Typography

**Font Family**: Montserrat (Google Fonts)
- Weights: 400 (Regular), 500 (Medium), 600 (Semibold), 700 (Bold)
- Applied globally via Tailwind config

**Type Scale**:
- Heading 1: `text-4xl md:text-6xl` (36px/60px)
- Heading 2: `text-3xl md:text-4xl` (30px/36px)
- Heading 3: `text-2xl` (24px)
- Body: `text-base` (16px)
- Small: `text-sm` (14px)
- Extra Small: `text-xs` (12px)

### Spacing & Layout

**Border Radius**:
- Standard: `rounded-xl` (0.75rem)
- Large: `rounded-2xl` (1rem)
- Extra Large: `rounded-3xl` (1.5rem)

**Shadows**:
- Soft: `shadow-soft` (custom: 0 10px 30px -12px rgba(0,0,0,0.15))
- Standard: `shadow-md`, `shadow-lg`, `shadow-xl`

**Container**:
- Max Width: `max-w-[80rem]` (1280px)
- Padding: `px-4` (1rem)

## Interactive Features

### 1. Dark Mode Toggle
**Location**: Footer
**Functionality**:
- Persists preference in localStorage
- Respects system preference as default
- Smooth transition between themes
- Button in footer for easy access

**Implementation**:
```javascript
localStorage.getItem('theme')
document.documentElement.classList.toggle('dark')
```

### 2. Predictive Search
**Location**: Header (desktop) and expandable mobile panel
**Functionality**:
- Real-time search suggestions
- Debounced API calls
- Keyboard navigation
- Click-outside to close
- JSON response for autocomplete

**API Endpoint**: `/search?q={query}`

### 3. Mobile Navigation
**Location**: Hamburger menu in header
**Functionality**:
- Slide-out navigation
- Accessible toggle with ARIA
- Close on link click
- Full navigation structure

### 4. Scroll Animations
**Implementation**: IntersectionObserver
**Features**:
- Fade-in on scroll
- Staggered animations with delays
- Respects reduced motion preferences
- Automatic cleanup after animation

**Usage**:
```html
<div data-animate data-animate-delay="100">Content</div>
```

### 5. Slider/Carousel
**Features**:
- Touch-enabled
- Keyboard navigation (arrow keys)
- Dot indicators
- Smooth scroll behavior
- Auto-sync active state

## Page Templates

### Public Pages Using Engram Layout

1. **Home Page** (`/`)
   - File: `resources/views/home.blade.php`
   - Features: Hero section, stats, feature cards, CTA sections

2. **Theory Pages** (`/pages`, `/pages/{category}`, `/pages/{category}/{page}`)
   - Files: `resources/views/engram/pages/`
   - Features: Sidebar navigation, category filtering, page grid

3. **Test Catalog** (`/catalog-tests/cards`)
   - File: `resources/views/engram/catalog-tests-cards.blade.php`
   - Features: Filter sidebar, card grid, tag filtering

4. **Test Pages** (`/test/{slug}/js/*`)
   - Files: `resources/views/engram/saved-test-js*.blade.php`
   - Features: Test interface, progress tracking, results

5. **Search Results** (`/search`)
   - File: `resources/views/search/results.blade.php`
   - Features: Result cards, empty state, filtering

## Component Library

### Gramlyze Logo Component
**File**: `resources/views/components/gramlyze-logo.blade.php`

**Variants**:
1. `stacked` - Full logo with tagline (default)
2. `compact` - Icon only
3. `badge` - Badge style with "AI" tag

**Usage**:
```blade
<x-gramlyze-logo />
<x-gramlyze-logo variant="compact" size="h-9 w-9" />
<x-gramlyze-logo variant="badge" />
```

### Navigation Components
- Breadcrumbs
- Mobile menu toggle
- Search bar
- Filter sidebar

## Responsive Breakpoints

```css
sm: 640px   /* Small devices */
md: 768px   /* Medium devices */
lg: 1024px  /* Large devices */
xl: 1280px  /* Extra large */
```

**Key Responsive Patterns**:
- Mobile-first approach
- Hamburger menu on mobile
- Grid layouts collapse to single column
- Typography scales with viewport
- Touch-friendly targets (44px minimum)

## Accessibility Features

1. **Semantic HTML**: Proper heading hierarchy, landmarks
2. **ARIA Labels**: All interactive elements labeled
3. **Keyboard Navigation**: Full keyboard support
4. **Focus Management**: Visible focus indicators
5. **Color Contrast**: WCAG AA compliant
6. **Screen Reader Support**: Skip links, descriptive text
7. **Reduced Motion**: Respects prefers-reduced-motion

## Performance Optimizations

1. **CDN Resources**: Tailwind and Alpine.js from CDN
2. **Lazy Loading**: Images and heavy content
3. **Minimal JavaScript**: Essential interactions only
4. **CSS Variables**: Dynamic theming without rebuild
5. **Intersection Observer**: Efficient scroll animations
6. **Debounced Search**: Reduced API calls

## Browser Support

- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest 2 versions)
- **Mobile**: iOS Safari 12+, Chrome Android 80+
- **Progressive Enhancement**: Core functionality without JavaScript

## Migration Guide

### Converting a Page to Engram Layout

**Before**:
```blade
@extends('layouts.app')
```

**After**:
```blade
@extends('layouts.engram')
```

### Design Updates Checklist
- [ ] Update layout extends statement
- [ ] Apply design tokens (colors, spacing)
- [ ] Add data-animate attributes for animations
- [ ] Update card styles to match design system
- [ ] Test responsive breakpoints
- [ ] Verify dark mode appearance
- [ ] Check accessibility

## Future Enhancements

1. **Animation Library**: Expand animation patterns
2. **Component Documentation**: Storybook integration
3. **RTL Support**: Right-to-left language support
4. **Advanced Search**: Faceted search, filters
5. **Progressive Web App**: Offline support, installability
6. **Performance Monitoring**: Core Web Vitals tracking

## Maintenance Notes

### Adding New Pages
1. Extend `layouts.engram`
2. Follow design system colors and spacing
3. Use consistent component patterns
4. Add animations with data-animate
5. Test mobile and dark mode
6. Verify accessibility

### Updating Styles
1. Modify CSS variables in layout head
2. Update Tailwind config if needed
3. Test both light and dark themes
4. Check responsive breakpoints
5. Update this documentation

### Common Patterns
```blade
<!-- Card -->
<div class="rounded-2xl border border-border/70 bg-card p-6 shadow-soft">

<!-- Button Primary -->
<button class="rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-white">

<!-- Button Secondary -->
<button class="rounded-full border border-border px-6 py-2.5 text-sm font-semibold">

<!-- Section Spacing -->
<section class="space-y-8" data-animate>

<!-- Grid -->
<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
```

## Summary

The new public layout provides:
✅ Consistent, modern design across all public pages
✅ Dark mode support with persistence
✅ Responsive mobile-first approach
✅ Predictive search functionality
✅ Smooth animations and interactions
✅ Full accessibility compliance
✅ Performance optimized
✅ Easy to maintain and extend

This layout creates a professional, cohesive experience for all users visiting the public sections of the Gramlyze platform.
