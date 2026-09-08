// @vitest-environment node
import {describe, expect, it} from 'vitest';
import fs from 'node:fs';
import vm from 'node:vm';
import postcss from 'postcss';

const read = file => fs.readFileSync(file, 'utf8');
const css = postcss.parse(read('resources/css/catalog-public.css'));
const desktop = read('resources/views/theory/partials/desktop-navigation-loader.blade.php');
const mobile = read('resources/views/theory/partials/mobile-navigation.blade.php');

function rules(selector) {
    const found = [];
    css.walkRules(rule => { if (rule.selector === selector) found.push(rule); });
    return found;
}

describe('lazy theory navigation geometry', () => {
    it('reserves the existing viewport-bounded desktop footprint, only at desktop widths', () => {
        const found = rules('[data-theory-aside] [data-theory-sidebar]');
        expect(found).toHaveLength(1);
        expect(found[0].parent.name).toBe('media');
        expect(found[0].parent.params).toBe('(min-width: 1024px)');
        expect(found[0].nodes.some(node => node.prop === 'block-size' && node.value === 'calc(100vh - 7rem)')).toBe(true);
    });

    it('reserves mobile panel allocation without reserving a closed menu', () => {
        const found = rules('[data-theory-mobile-nav-panel]');
        expect(found).toHaveLength(1);
        expect(found[0].nodes.some(node => node.prop === 'block-size' && node.value === 'calc(100vh - 8rem)')).toBe(true);
        expect(mobile).toMatch(/<div\s+x-show="open"[^>]*x-cloak[^>]*data-theory-mobile-nav-panel/s);
        expect(mobile).toContain('x-transition');
    });

    it('hides initially false error/content states and preserves the loading indicator', () => {
        for (const template of [desktop, mobile]) {
            expect(template).toMatch(/x-show="error"\s+x-cloak/);
            expect(template).toMatch(/x-ref="content"\s+x-show="[^\"]*!loading[^\"]*!error[^\"]*"\s+x-cloak/);
            expect(template).toContain('x-show="loading"');
            expect(template).toContain('window.Alpine?.initTree(this.$refs.content)');
        }
    });
});

describe('pre-paint saved sidebar state', () => {
    const headScript = read('resources/views/layouts/catalog-public.blade.php').match(/<script>([\s\S]*?)<\/script>/)[1];
    const run = value => {
        const root = {dataset: {}, classList: {toggle() {}}, style: {setProperty() {}}};
        vm.runInNewContext(headScript, {
            document: {documentElement: root},
            window: {matchMedia: () => ({matches: false})},
            localStorage: {getItem(key) { if (value instanceof Error) throw value; return key === 'theorySidebarCollapsed' ? value : null; }},
        });
        return root;
    };

    it('restores only the explicit collapsed preference before Alpine starts', () => {
        expect(run('true').dataset.theorySidebarCollapsed).toBe('true');
        for (const value of [null, 'false', 'invalid']) {
            expect(run(value).dataset.theorySidebarCollapsed).toBe('false');
        }
    });

    it('does not break first paint when browser storage is unavailable', () => {
        expect(() => run(new Error('Storage denied'))).not.toThrow();
    });

    it('limits the early CSS fallback to the period before Alpine owns data-collapsed', () => {
        const navigation = read('resources/views/theory/partials/tree-nav.blade.php');
        expect(navigation).toContain('html[data-theory-sidebar-collapsed="true"] [data-theory-layout]:not([data-collapsed]) [data-theory-aside]');
        expect(navigation).toContain('[data-theory-layout][data-collapsed="true"] [data-theory-aside]');
    });
});
