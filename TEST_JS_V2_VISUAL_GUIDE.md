# Test JS V2 - Visual Design Guide

## 🎨 Color Palette

### Primary Colors
- **Blue-50**: `#eff6ff` - Light blue background
- **Blue-100**: `#dbeafe` - Badge backgrounds
- **Blue-400**: `#60a5fa` - Hover borders
- **Blue-500**: `#3b82f6` - Focus rings
- **Blue-700**: `#1d4ed8` - Badge text

### Success Colors
- **Emerald-50**: `#ecfdf5` - Success feedback background
- **Emerald-500**: `#10b981` - Success accents
- **Emerald-600**: `#059669` - Trophy icon
- **Emerald-700**: `#047857` - Success text
- **Teal-500**: `#14b8a6` - Gradient accent

### Error Colors
- **Rose-50**: `#fff1f2` - Error feedback background
- **Rose-400**: `#fb7185` - Error borders
- **Rose-500**: `#f43f5e` - Error accents
- **Rose-700**: `#be123c` - Error text

### Neutral Colors
- **Slate-50**: `#f8fafc` - Light backgrounds
- **Slate-100**: `#f1f5f9` - Very light backgrounds
- **Slate-200**: `#e2e8f0` - Borders
- **Slate-400**: `#94a3b8` - Secondary text
- **Slate-600**: `#475569` - Body text
- **Slate-700**: `#334155` - Dark text
- **Slate-900**: `#0f172a` - Headings, buttons

### Accent Colors
- **Amber-100**: `#fef3c7` - Filled answer highlights
- **Amber-200**: `#fde68a` - Active answer highlights
- **Amber-900**: `#78350f` - Answer text

## 📐 Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│  WHITE HEADER SECTION (with shadow)                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Test Name (3xl/4xl, bold, slate-900)                │  │
│  │  Description (base, slate-600)                        │  │
│  │                                         [Restart] [🔍]│  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│  GRADIENT BACKGROUND (slate-50 → blue-50 → indigo-50)       │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  Test Mode Navigation                                  │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  Progress Bar                                          │ │
│  │  [████████░░░░░] 8/10  Accuracy: 80%                 │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  ╔══════════════════════════════════════════════════╗ │ │
│  │  ║ QUESTION CARD (white, 2px border, shadow)       ║ │ │
│  │  ║ ┌──────────────────────────────────────────────┐ ║ │ │
│  │  ║ │ Gradient Header (slate-50 → blue-50)         │ ║ │ │
│  │  ║ │ [A1] Present Simple      Question 1 of 10    │ ║ │ │
│  │  ║ └──────────────────────────────────────────────┘ ║ │ │
│  │  ║                                                  ║ │ │
│  │  ║  She ___ to school every day.                   ║ │ │
│  │  ║                                                  ║ │ │
│  │  ║  ┌────────────────────┬────────────────────┐    ║ │ │
│  │  ║  │ [1] goes           │ [2] go             │    ║ │ │
│  │  ║  ├────────────────────┼────────────────────┤    ║ │ │
│  │  ║  │ [3] going          │ [4] went           │    ║ │ │
│  │  ║  └────────────────────┴────────────────────┘    ║ │ │
│  │  ║                                                  ║ │ │
│  │  ║  ✓ Correct!                                     ║ │ │
│  │  ╚══════════════════════════════════════════════════╝ │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  [More question cards...]                                   │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  ╔══════════════════════════════════════════════════╗ │ │
│  │  ║ SUMMARY CARD                                     ║ │ │
│  │  ║ ┌──────────────────────────────────────────────┐ ║ │ │
│  │  ║ │ Gradient Header (emerald-500 → teal-500)     │ ║ │ │
│  │  ║ │ ✓ Test Complete!                      🏆     │ ║ │ │
│  │  ║ └──────────────────────────────────────────────┘ ║ │ │
│  │  ║                                                  ║ │ │
│  │  ║  Your Score                            [Trophy] ║ │ │
│  │  ║  8 out of 10 correct (80%)                      ║ │ │
│  │  ║                                                  ║ │ │
│  │  ║  [Try Again]    [Review Mistakes]               ║ │ │
│  │  ╚══════════════════════════════════════════════════╝ │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 🎯 Key Design Elements

### 1. Header Section
```
┌──────────────────────────────────────────────────┐
│ Background: white                                 │
│ Border-bottom: 1px slate-200                     │
│ Shadow: sm                                        │
│ Padding: 6 (1.5rem) vertical, 8 (2rem) sides    │
│ Max-width: 5xl (1024px)                          │
│                                                   │
│ Title: 3xl-4xl, font-bold, slate-900, tracking-tight │
│ Description: base, slate-600, leading-relaxed    │
└──────────────────────────────────────────────────┘
```

### 2. Question Card
```
┌──────────────────────────────────────────────────┐
│ Border: 2px slate-200                            │
│ Shadow: md (hover: xl)                           │
│ Rounded: 2xl (1rem)                              │
│ Background: white                                │
│ Transition: all 300ms                            │
│                                                   │
│ ┌────────────────────────────────────────────┐  │
│ │ GRADIENT HEADER                            │  │
│ │ from-slate-50 to-blue-50                   │  │
│ │ Padding: 4 (1rem) vertical                 │  │
│ │ Border-bottom: 1px slate-200               │  │
│ │                                             │  │
│ │ [A1] (blue-100 bg, blue-700 text, pill)    │  │
│ │ Present Simple (sm, medium, slate-600)     │  │
│ │                 Question 1 of 10 (badge)   │  │
│ └────────────────────────────────────────────┘  │
│                                                   │
│ CONTENT AREA                                     │
│ Padding: 5 (1.25rem) vertical                   │
│                                                   │
│ Question text: lg, leading-relaxed, slate-900   │
│ Blanks: amber-100 (filled), blue-100 (active)   │
│                                                   │
│ OPTIONS GRID (2 columns on desktop, 1 on mobile)│
│ Gap: 3 (0.75rem)                                 │
│                                                   │
│ FEEDBACK AREA                                    │
│ Border-left: 4px (colored by feedback type)     │
│ Background: *-50 tint                            │
│ Padding: 4 (1rem)                                │
└──────────────────────────────────────────────────┘
```

### 3. Answer Button
```
┌──────────────────────────────────────┐
│ Border: 2px slate-200                │
│ Rounded: xl (0.75rem)                │
│ Padding: 4 horizontal, 3.5 vertical  │
│ Font: medium weight                  │
│ Transition: all 200ms                │
│                                       │
│ ┌─────┐                              │
│ │ [1] │ Option text goes here       │
│ └─────┘                              │
│   │                                   │
│   └─ Badge: 7x7, rounded-lg,         │
│      border-2 slate-400,             │
│      text-sm bold, shadow-sm         │
│                                       │
│ HOVER STATE:                         │
│ - border-blue-400                    │
│ - bg-blue-50                         │
│ - shadow-md                          │
│                                       │
│ ACTIVE STATE:                        │
│ - scale-[0.98]                       │
└──────────────────────────────────────┘
```

### 4. Summary Card
```
┌──────────────────────────────────────────────────┐
│ Border: 2px slate-200                            │
│ Rounded: 3xl (1.5rem)                            │
│ Shadow: lg                                        │
│ Background: white                                │
│                                                   │
│ ┌────────────────────────────────────────────┐  │
│ │ GRADIENT HEADER                            │  │
│ │ from-emerald-500 to-teal-500               │  │
│ │ Padding: 6 (1.5rem) vertical               │  │
│ │                                             │  │
│ │ [✓] Test Complete! (2xl, bold, white)     │  │
│ │     Great job... (emerald-50, sm)          │  │
│ └────────────────────────────────────────────┘  │
│                                                   │
│ CONTENT AREA                                     │
│ Padding: 6 (1.5rem) vertical                    │
│                                                   │
│ YOUR SCORE (uppercase, tracking-wide, sm)       │
│ 8 out of 10 correct (80%)                       │
│ (4xl, bold, slate-900)                          │
│                            ┌──────────┐          │
│                            │   🏆    │          │
│                            │  Trophy │          │
│                            └──────────┘          │
│                                                   │
│ BUTTONS (full width on mobile)                  │
│ [Try Again] - Gradient slate-900 to slate-700  │
│ [Review Mistakes] - Border 2px slate-300        │
└──────────────────────────────────────────────────┘
```

## 🎭 Interactive States

### Question Card States
1. **Default**: border-slate-200, shadow-md
2. **Hover**: shadow-xl (smooth transition)
3. **Focus**: ring-4 blue-500/20
4. **Active**: (card stays stable, buttons respond)

### Answer Button States
1. **Default**: border-slate-200, bg-white
2. **Hover**: border-blue-400, bg-blue-50, shadow-md
3. **Active**: scale-[0.98] (pressed effect)
4. **Disabled**: border-slate-200, bg-slate-50, text-slate-500
5. **Wrong**: border-rose-400, bg-rose-50, text-rose-700

### Feedback States
1. **Correct**: 
   - Text: emerald-700 with checkmark icon
   - Explanation: bg-emerald-50, border-l-4 emerald-500

2. **Incorrect**:
   - Text: rose-700 with X icon
   - Explanation: bg-rose-50, border-l-4 rose-500

3. **Info**:
   - Explanation: bg-slate-50, border-l-4 slate-400

## 📱 Responsive Breakpoints

### Mobile (< 640px)
- Single column for answer options
- Smaller font sizes
- Reduced padding
- Full-width buttons
- Stack header elements

### Tablet (640px - 1024px)
- 2 columns for answer options
- Medium font sizes
- Standard padding
- Flex buttons

### Desktop (> 1024px)
- 2 columns for answer options
- Large font sizes
- Generous padding
- Flex buttons with hover effects

## 🌈 Gradient Definitions

### Page Background
```css
background: linear-gradient(to bottom right, #f8fafc, #eff6ff, #eef2ff)
/* slate-50 → blue-50 → indigo-50 */
```

### Question Card Header
```css
background: linear-gradient(to right, #f8fafc, #eff6ff)
/* slate-50 → blue-50 */
```

### Summary Card Header
```css
background: linear-gradient(to right, #10b981, #14b8a6)
/* emerald-500 → teal-500 */
```

### Button Gradients
```css
/* Primary button */
background: linear-gradient(to right, #0f172a, #334155)
/* slate-900 → slate-700 */
```

## 🎪 Animation Details

### Hover Animations
- **Duration**: 200-300ms
- **Easing**: ease-in-out (default Tailwind)
- **Properties**: shadow, border-color, background-color, transform

### Focus Animations
- **Ring**: appears smoothly
- **Color**: blue-500 at 20% opacity
- **Width**: 4px (ring-4)

### Active Blank Animation
- **Pulse**: animate-pulse (Tailwind built-in)
- **Effect**: Gently fades in and out
- **Purpose**: Draw attention to the current blank to fill

### Button Press Animation
- **Transform**: scale(0.98)
- **Duration**: 200ms
- **Effect**: Slight shrink on click for tactile feedback

## 📊 Typography Scale

```
Headings:
- Page title: text-3xl (1.875rem) → md:text-4xl (2.25rem)
- Card title: text-2xl (1.5rem)
- Section title: text-lg (1.125rem)

Body:
- Main text: text-base (1rem)
- Secondary: text-sm (0.875rem)
- Caption: text-xs (0.75rem)

Scores:
- Large score: text-4xl (2.25rem)
```

## 🎯 Spacing System

```
Vertical spacing:
- Between sections: space-y-6 (1.5rem)
- Between cards: space-y-6 (1.5rem)
- Within cards: space-y-4 to space-y-5

Horizontal spacing:
- Container padding: px-6 (1.5rem)
- Button gaps: gap-3 (0.75rem)
- Icon-text gap: gap-2 to gap-3

Padding:
- Small: p-4 (1rem)
- Medium: p-5 (1.25rem)
- Large: p-6 to p-8 (1.5rem to 2rem)
```

## 🎨 Shadow System

```
Card shadows:
- Default: shadow-md
- Hover: shadow-xl
- Summary: shadow-lg

Button shadows:
- Default: shadow-lg
- Hover: shadow-xl

Badge shadows:
- Small elements: shadow-sm
```
