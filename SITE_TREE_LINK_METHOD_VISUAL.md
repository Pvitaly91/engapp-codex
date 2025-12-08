# Visual Guide: Site Tree Link Method Display

## How It Looks

On the `/admin/site-tree` page, each linked tree item now displays badges showing how the connection was established:

### Before (Original View)
```
☑ 1. Present Simple — Теперішній простий час [A1] ✓
```

### After (With Link Method Tracking)
```
☑ 1. Present Simple — Теперішній простий час [A1] ✓ [точна назва]
```

## Badge Examples

### Example 1: Exact Title Match (Green)
```
☑ Question forms — як ставити запитання [A1] ✓ [точна назва]
                                                   ↑ Green badge
```
- **Color**: Light green background with dark green text
- **Tooltip**: "Зв'язано автоматично: точна відповідність назви"

### Example 2: Seeder Name Match (Purple)
```
☑ Advanced word order and emphasis [B1-B2] ✓ [сидер]
                                               ↑ Purple badge
```
- **Color**: Light purple background with dark purple text
- **Tooltip**: "Зв'язано автоматично: відповідність імені сидера"

### Example 3: Slug Match (Yellow)
```
☑ Basic word order in statements [A1] ✓ [slug]
                                         ↑ Yellow badge
```
- **Color**: Light yellow background with dark yellow text
- **Tooltip**: "Зв'язано автоматично: відповідність slug"

### Example 4: Manual Link (Blue)
```
☑ Custom Topic [B1] ✓ [вручну]
                      ↑ Blue badge
```
- **Color**: Light blue background with dark blue text
- **Tooltip**: "Зв'язано вручну користувачем"

### Example 5: Not Linked (No Badge)
```
☑ 5. Дієслова та володіння [A1-C2]
```
- No checkmark, no badge

## Badge Placement

Badges appear in this order for each tree item:
1. **Checkbox** (for enabling/disabling)
2. **Category number** (e.g., "1." or "2.3")
3. **Title** (green background if linked)
4. **Level badge** (e.g., [A1], [B1-B2])
5. **Link indicator** (green checkmark ✓ with hover tooltip showing method)
6. **Link method badge** (colored badge showing how it was linked)
7. **Action buttons** (visible when item is selected)

## Responsive Behavior

- On **desktop**: All badges visible with full labels
- On **mobile**: Badges remain visible but may wrap to next line
- On **hover**: Full tooltip explains the linking method

## User Interactions

### Hovering Over Checkmark (✓)
Shows: "Відкрити на сайті | Метод: точна назва"

### Hovering Over Method Badge
Shows detailed explanation:
- "Зв'язано автоматично: точна відповідність назви"
- "Зв'язано автоматично: відповідність імені сидера"
- "Зв'язано автоматично: відповідність slug"
- "Зв'язано вручну користувачем"

### Clicking Checkmark (✓)
Opens the theory page in a new tab

## Color Coding Summary

| Method | Badge Text | Color | Use Case |
|--------|-----------|-------|----------|
| Exact Title | точна назва | 🟢 Green | Title matches exactly |
| Seeder Name | сидер | 🟣 Purple | Seeder class name matches |
| Slug Match | slug | 🟡 Yellow | Slug pattern matches |
| Manual | вручну | 🔵 Blue | Manually linked by admin |

## Notes

- **Green** indicates the most reliable automatic matching
- **Purple** and **Yellow** indicate less strict automatic matching
- **Blue** indicates human intervention (most flexible but requires manual effort)
- No badge means the item is not linked to any page
