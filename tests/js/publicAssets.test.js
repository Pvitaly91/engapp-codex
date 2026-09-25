// @vitest-environment node
import {beforeAll, describe, expect, it} from 'vitest';
import fs from 'node:fs';
import path from 'node:path';
import postcss from 'postcss';
import tailwindByEntry from '../../tools/build/tailwind-by-entry.js';
import config from '../../tailwind.public.config.js';

const read = file => fs.readFileSync(path.resolve(file), 'utf8');
const layout = read('resources/views/layouts/catalog-public.blade.php');
let css;
beforeAll(async () => {
    css = (await postcss([tailwindByEntry()]).process(read('resources/css/catalog-public.css'), {
        from: path.resolve('resources/css/catalog-public.css'),
    })).css;
}, 60000);

describe('isolated public assets', () => {
    it('uses manifest entries, not CDN, HMR or the unrelated global app.css', () => {
        expect(layout).toContain("@vite(['resources/css/catalog-public.css', 'resources/js/catalog-public.js'])");
        expect(layout).not.toMatch(/cdn\.tailwindcss\.com|unpkg\.com\/alpinejs|tailwind\.config|resources\/css\/app\.css|@vite\/client/);
    });
    it('keeps Livewire as the only Alpine owner and avoids a second root init', () => {
        expect(layout.match(/@livewireScripts/g)).toHaveLength(1);
        expect(layout).not.toContain('x-init="init()"');
        expect(read('resources/js/catalog-public.js')).not.toMatch(/Alpine\.start|from ['"]alpinejs/);
        for (const name of ['themeController', 'searchBox', 'languageSwitcher']) expect(layout).toContain('function ' + name + '(');
        for (const name of ['buildShellRandomShapes', 'randomizeAppBackgroundIcons']) expect(read('resources/js/catalog-public.js')).toContain('window.' + name + ' = ' + name);
    });
    it('pins the compatible public compiler without downgrading existing v4 consumers', () => {
        const lock = JSON.parse(read('package-lock.json')).packages;
        expect(lock['node_modules/tailwindcss-public'].version).toBe('3.4.17');
        expect(lock['node_modules/tailwindcss'].version).toBe('4.1.16');
        expect(config.darkMode).toBe('class');
        expect(css).toContain('tailwindcss v3.4.17');
        expect(css).not.toContain('tailwindcss v4');
    });
    it('preserves public design tokens and content/dynamic state utilities', () => {
        for (const selector of ['.font-body', '.font-display', '.bg-ocean', '.shadow-card', '.lg\\:shadow-panel',
            '.line-through', '.font-mono', '.text-slate-500', '.text-xs', '.border-emerald-200',
            '.bg-red-50', '.text-green-700', '.opacity-50', '.grid-cols-2', '.rounded-2xl']) {
            expect(css.includes(selector), selector).toBe(true);
        }
        expect(css).toContain('Manrope');
        expect(css).toContain('Archivo');
        expect(css).toContain('47 103 177');
        expect(css).toContain('dark\\:');
        expect(css).toContain('sm\\:');
        expect(css).toContain('disabled\\:');
        expect(css).not.toMatch(/line-height:\s*2\.2rem\s*!important/);
    });
    it('uses source-only globs and a small reviewed class manifest', () => {
        expect(JSON.stringify(config.content)).not.toMatch(/storage|backup|dump|https?:/);
        expect(config.content.files).toContain('./database/seeders/{Page_V3,V3}/**/*.{php,json}');
        expect(config.safelist).toEqual(['font-mono', 'font-semibold', 'line-through', 'text-slate-500', 'text-xs']);
    });
    it('still compiles legacy app.css with its original v4 line-height override', async () => {
        const legacy = (await postcss([tailwindByEntry()]).process(read('resources/css/app.css'), {
            from: path.resolve('resources/css/app.css'),
        })).css;
        expect(legacy).toContain('tailwindcss v4.1.16');
        expect(legacy).toMatch(/line-height:\s*2\.2rem\s*!important/);
        expect(legacy).not.toContain('--app-background-blue');
    }, 60000);
});
