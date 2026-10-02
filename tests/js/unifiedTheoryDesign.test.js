import { describe, expect, it } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import postcss from 'postcss';

const source = relative => fs.readFileSync(path.resolve(process.cwd(), relative), 'utf8');
const css = source('resources/css/theory-unified-design.css');
const entry = source('resources/js/catalog-public.js');
const cssAst = postcss.parse(css);

describe('scoped unified lesson design', () => {
    it('does not introduce global typography, hidden content or text truncation', () => {
        const rules = [...css.matchAll(/([^{}]+)\{/g)].map(match => match[1].replace(/\/\*[\s\S]*?\*\//g, '').trim());
        for (const selector of rules.filter(rule => !rule.startsWith('@'))) {
            expect(selector).toContain('.theory-design');
        }
        expect(css).not.toMatch(/line-clamp|text-overflow\s*:\s*ellipsis|content-visibility\s*:\s*hidden/);
        expect(css).not.toMatch(/past-simple|m11|m24|@import\s+url/);
    });
    it('supports narrow tables, focus, dark tokens, print and reduced motion', () => {
        for (const value of ['var(--text)', 'var(--surface-strong)', 'overflow-x: auto', 'focus-visible', '@media print', '::details-content', 'prefers-reduced-motion', '.theory-section-details[open]']) {
            expect(css).toContain(value);
        }
        expect(css).toContain('.theory-design [id] { scroll-margin-top: 8rem; }');
        expect(css).not.toContain('52vh');
        expect(css).not.toContain('--theory-sidebar-height');
        expect(css).toContain('.theory-native-block code.theory-example { display: block; }');
        expect(css).toContain('.dark .theory-design .theory-native-block :is(.text-emerald-700, .text-sky-700, .text-amber-700, .text-slate-700) { color: var(--text); }');
        expect(css).toContain('.theory-exercise > .border-b { background-color: var(--theory-soft); border-color: var(--line); }');
        expect(css).toContain('.theory-exercise .text-rose-700 { color: #fda4af; }');
    });
    it('keeps the existing sole Alpine owner and enhances only delivered DOM', () => {
        expect(entry).toContain("import { initTheorySections } from './theory-sections.js'");
        expect(entry).not.toMatch(/import .*Alpine|Alpine\.start|unpkg|cdn\.tailwindcss/);
        const module = source('resources/js/theory-sections.js');
        expect(module).not.toMatch(/fetch\(|setTimeout|innerHTML|createElement\('template'/);
        expect(module).toContain("window.addEventListener('popstate'");
    });
    it('does not override the restored legacy menu, icons, search or sidebar allocation', () => {
        expect(css).not.toMatch(/theory-sidebar|theory-nav-link|theory-nav-icon|theory-nav-label|theory-nav-count|data-theory-sidebar/);
        const view = source('resources/views/theory/show.blade.php');
        expect(view).toMatch(/class="[^"]*theory-design[^"]*"\s+data-theory-main/);
        expect(view).not.toContain('class="nd-page theory-design"');
        cssAst.walkRules(rule => {
            if (rule.selector.includes('[data-theory-aside]')) {
                expect(rule.parent.name).toBe('media');
                expect(rule.parent.params).toBe('print');
            }
        });
    });
    it('retains the original viewport-sized menu and full desktop tree search styling', () => {
        const catalog = postcss.parse(source('resources/css/catalog-public.css'));
        const allocations = [];
        catalog.walkRules(rule => {
            if (rule.selector === '[data-theory-aside] [data-theory-sidebar]') allocations.push(rule);
        });
        expect(allocations).toHaveLength(1);
        expect(allocations[0].parent.params).toBe('(min-width: 1024px)');
        expect(allocations[0].nodes.some(node => node.prop === 'block-size' && node.value === 'calc(100vh - 7rem)')).toBe(true);
        const tree = source('resources/views/theory/partials/tree-nav.blade.php');
        expect(tree).toContain('inline-size: 390px');
        expect(tree).toContain('inline-size: 410px');
        expect(tree).toContain('inline-size: 116px');
        const navigation = source('resources/views/theory/partials/mobile-navigation-content.blade.php');
        expect(navigation).toMatch(/class="[^"]*min-h-0[^"]*overflow-y-auto[^"]*"\s+data-theory-sidebar-scroll/);
        const search = source('resources/views/theory/partials/tree-nav-search.blade.php');
        expect(search).toContain('rounded-[24px] border p-3 shadow-sm surface-card');
        expect(search).toContain('rounded-[18px] border px-3 py-2.5 surface-card-strong');
    });
});
