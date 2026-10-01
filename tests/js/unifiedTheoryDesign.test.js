import { describe, expect, it } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import postcss from 'postcss';

const source = relative => fs.readFileSync(path.resolve(process.cwd(), relative), 'utf8');
const css = source('resources/css/theory-unified-design.css');
const entry = source('resources/js/catalog-public.js');
const cssAst = postcss.parse(css);
const declarations = selector => {
    const properties = new Map();
    cssAst.walkRules(rule => {
        if (rule.selectors.includes(selector)) rule.walkDecls(node => properties.set(node.prop, node.value));
    });
    return properties;
};

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
        expect(css).toContain('block-size: var(--theory-sidebar-height, calc(100dvh - 8rem))');
        expect(css).toContain('.theory-sidebar-shell { position: sticky');
        expect(css).toContain('.theory-design.nd-page { overflow-x: clip; overflow-y: visible; }');
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
    it('keeps the sticky allocation neutral and paints two quiet sibling cards instead of tabs', () => {
        const shell = declarations('.theory-design .theory-sidebar-shell');
        expect(shell.get('padding')).toBe('0');
        expect(shell.get('border')).toBe('0');
        expect(shell.get('box-shadow')).toBe('none');
        const card = declarations('.theory-design .theory-sidebar-card');
        expect(card.get('padding')).toBe('1rem');
        expect(card.get('border')).toBe('1px solid var(--line)');
        expect(card.get('border-radius')).toBe('1.25rem');
        expect(card.get('box-shadow')).toBe('var(--theory-shadow)');
        expect(css).not.toContain('.theory-sidebar-tabs');
    });
    it('retains separate topic-tree and contents scroll roots inside bounded cards', () => {
        const contents = declarations('.theory-design .theory-sidebar-lesson-panel');
        expect(contents.get('min-height')).toBe('0');
        expect(contents.get('overflow-y')).toBe('auto');
        const topics = declarations('.theory-design .theory-sidebar-topics-panel');
        expect(topics.get('min-height')).toBe('0');
        expect(topics.get('overflow')).toBe('hidden');
        const navigation = source('resources/views/theory/partials/mobile-navigation-content.blade.php');
        expect(navigation).toMatch(/class="[^"]*min-h-0[^"]*overflow-y-auto[^"]*"\s+data-theory-sidebar-scroll/);
        const collapsedContents = declarations('.theory-design [data-collapsed="true"] .theory-sidebar-contents-card');
        expect(collapsedContents.get('display')).toBe('none');
    });
});
