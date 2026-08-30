# Nageeb Visual Direction

This document outlines the visual philosophy and design rules established for the Nageeb educational platform. It serves as the single source of truth for all frontend visual decisions to ensure consistency, elegance, and a premium user experience.

## 1. Visual Personality
**Premium, Modern, Educational, Trustworthy, Elegant, Confident, Human.**
Nageeb is a modern EdTech SaaS, not a standard generic dashboard. The interface is completely devoid of dark mode to enforce a clean, focused, and universally legible light environment. We avoid childish illustrations and excessive, competing colors.

## 2. Color Philosophy
- **Light Mode Only**: The interface relies on pristine white surfaces (`#FFFFFF`) layered over soft, cool-tinted slate backgrounds (`#F8FAFC`).
- **Confident Primary**: The primary color is a deep royal blue (`#1D4ED8`) which signals trust and professionalism, moving away from generic SaaS purples.
- **Controlled Accents**: Accent colors are warm (Amber) but used sparingly, only to highlight specific callouts or star ratings without becoming alarming.
- **Semantic Clarity**: Success (Emerald), Danger (Red), and Warning (Amber) have explicit background tints (e.g., `bg-success-muted`) to ensure they are visually distinct but not overly aggressive.

## 3. Typography Philosophy
- **Primary Font**: `IBM Plex Sans Arabic` handles all UI text, providing excellent legibility across varying sizes.
- **Scale and Spacing**: We employ a robust typographic scale ranging from Display (48px) down to Caption (12px). Line height (leading) is tight for headers and relaxed for body text to maximize Arabic readability. 
- **Monospace for Data**: `IBM Plex Mono` is exclusively used for numerical data (prices, dates, statistics) to ensure tabular alignment and immediate visual distinction from prose.

## 4. Component Philosophy
- **Utility over Custom CSS**: Components are built using Tailwind utilities directly. This ensures flexibility and prevents monolithic CSS bloat.
- **Interactive but Grounded**: Buttons and cards provide subtle feedback on hover (e.g., translating upwards by `1px` and increasing shadow slightly), making the platform feel alive without excessive motion.

## 5. Spacing Philosophy
- We use a strict 4px grid system derived from Tailwind's default spacing.
- **Whitespace is UI**: We rely on generous padding and margins rather than harsh borders to delineate sections. A crowded UI is a cheap UI.

## 6. Border & Radius Philosophy
- **Subtle Borders**: Borders are light (`#E2E8F0`) and used primarily to separate nested surfaces or define input fields.
- **Controlled Radius**: `8px` (`rounded-lg`) for interactive inputs/buttons, and `12px` (`rounded-xl`) for main content cards. We avoid making every element a "pill" shape, reserving fully rounded shapes (`rounded-full`) exclusively for badges and avatars.

## 7. Shadow Philosophy (Elevation)
- Shadows are soft, tinted slightly with the slate text color to blend naturally into the environment. 
- Deep, harsh shadows are avoided entirely. We use `shadow-xs` for baseline cards and inputs, rising to `shadow-md` for hover states and dropdowns.

## 8. Navigation Philosophy
- **Distinct Contexts**: Student, Teacher, and Admin spaces share a visual language but have tailored navigation structures (e.g., focused Topbars for students, Sidebar for teachers).
- Navigation elements must be lightweight, using muted text that illuminates to the Primary color upon interaction or active state.

## 9. Responsive Philosophy
- **Mobile-First Realities**: The design strictly supports devices down to 360px. Mobile layouts are intentionally designed—not just "squished" desktop layouts.
- Grids dynamically collapse into single columns, and complex tables shift into stacked card views on smaller screens.

## 10. Accessibility Principles
- **Contrast**: All text variables are tuned to pass WCAG AA contrast standards.
- **Focus States**: A highly visible focus ring (`focus:ring-2 focus:ring-primary/20`) is mandatory for all interactive elements to support keyboard navigation.
- **Aria Labels**: Proper aria-invalid, aria-busy, and aria-describedby attributes are implemented at the component level to ensure screen readers accurately interpret state changes.
