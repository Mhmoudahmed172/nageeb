# Nageeb Design System

## 1. Brand Direction
**Vibe**: Premium, Modern, Educational, Trustworthy, Human, Clean, Confident.
**Theme**: **Light Mode Only** (No dark mode).

## 2. Color System
A sophisticated light color system avoiding generic SaaS purples and childish aesthetics. The primary color is a deep, confident indigo/blue that feels premium, paired with soft, tinted surfaces.

- **Primary Brand** (`#1D4ED8` - Royal Blue): Confident, trustworthy, premium.
- **Primary Hover** (`#1E40AF`): Darker for interactions.
- **Primary Soft** (`#EFF6FF`): For backgrounds of active states or primary-tinted cards.
- **Accent** (`#F59E0B` - Warm Amber): For callouts, stars, and attention without being alarming.
- **Background** (`#F8FAFC` - Slate 50): Very light, slightly cool gray to provide contrast against white surfaces.
- **Surface** (`#FFFFFF`): Pure white for cards, dropdowns, and main content areas.
- **Surface Secondary** (`#F1F5F9` - Slate 100): For nested surfaces, disabled inputs, or subtle separation.
- **Text Primary** (`#0F172A` - Slate 900): Near black for highest readability on light backgrounds.
- **Text Secondary** (`#334155` - Slate 700): For body copy, ensuring strong contrast but softer than headings.
- **Text Muted** (`#64748B` - Slate 500): For metadata, placeholders, and less important text.
- **Border** (`#E2E8F0` - Slate 200): Subtle, not distracting.
- **Success** (`#059669` - Emerald 600): For correct answers, successful payments.
- **Warning** (`#D97706` - Amber 600): For alerts, expiring subscriptions.
- **Danger** (`#DC2626` - Red 600): For errors, deletion actions.
- **Info** (`#2563EB` - Blue 600): For informational banners.

## 3. Typography
**Font Family**: `IBM Plex Sans Arabic` (or `Cairo` / `Tajawal` if preferred) for all text to ensure excellent Arabic readability.
**Direction**: RTL.

- **Display**: 48px (3rem), font-bold, tight leading.
- **H1**: 36px (2.25rem), font-bold, tight leading.
- **H2**: 30px (1.875rem), font-semibold, tight leading.
- **H3**: 24px (1.5rem), font-semibold, normal leading.
- **H4**: 20px (1.25rem), font-medium, normal leading.
- **Body Large**: 18px (1.125rem), font-normal, relaxed leading.
- **Body**: 16px (1rem), font-normal, relaxed leading.
- **Body Small**: 14px (0.875rem), font-normal, normal leading.
- **Caption**: 12px (0.75rem), font-medium, normal leading.

## 4. Spacing Scale
Based on Tailwind's default 4px grid.
- `xs`: 4px (space-1)
- `sm`: 8px (space-2)
- `md`: 16px (space-4)
- `lg`: 24px (space-6)
- `xl`: 32px (space-8)
- `2xl`: 48px (space-12)
- `3xl`: 64px (space-16)

## 5. Borders & Radius
**Borders**: Subtle (`#E2E8F0`). Used only to delineate surfaces that lack shadows, or for inputs. Do not outline everything; rely on spacing first.
**Radius**: Modern and controlled.
- **Inputs, Buttons, Small Cards**: `8px` (`rounded-lg`).
- **Large Cards, Modals, Featured Media**: `12px` (`rounded-xl`).
- **Pills/Badges**: `9999px` (`rounded-full`).

## 6. Shadows (Elevation)
Subtle and soft, avoiding harsh contrast.
- **xs**: `0 1px 2px rgba(15, 23, 42, 0.05)` - Inputs, standard buttons.
- **sm**: `0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05)` - Hover states on buttons, small cards.
- **md**: `0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.05)` - Dropdowns, standard modals.
- **lg**: `0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05)` - Large modals, prominent overlays.

## 7. Component Guidelines
- **Buttons**: Primary (filled), Secondary (soft/tinted background), Outline (border only), Ghost (no background). Include loading states.
- **Inputs**: Floating labels or clean top labels. Focus states must have a subtle `ring` of primary color.
- **Cards**: Surface color, subtle border OR `sm` shadow, `rounded-xl`.
- **Badges**: Soft backgrounds with slightly darker text of the same hue (e.g., Success badge: green-50 bg, green-700 text).

## 8. Micro-Interactions
- **Hover**: Buttons subtly lift (shadow-sm) or change background color.
- **Focus**: `ring-2 ring-primary/20` offset by 2px on all interactive elements.
- **Transitions**: `150ms ease-in-out` for color/opacity changes, `200ms ease-out` for transforms (modals opening, dropdowns).

## 9. Accessibility
- All text colors must pass WCAG AA contrast on their respective backgrounds.
- All interactive elements must be keyboard focusable (`tabindex="0"` or native) and display a clear focus ring.
- State changes (loading, error, success) must use aria-live regions where appropriate.

## 10. Responsive Breakpoints
- `sm`: 640px (Mobile landscape)
- `md`: 768px (Tablets)
- `lg`: 1024px (Small laptops, sidebars become visible)
- `xl`: 1280px (Standard desktops)
- `2xl`: 1536px+ (Large monitors)
*Note: Mobile-first approach. Modals become bottom-sheets on mobile.*
