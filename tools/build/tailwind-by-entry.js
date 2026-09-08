import path from 'node:path';
import postcss from 'postcss';
import tailwind4 from '@tailwindcss/postcss';
import tailwindPublic from 'tailwindcss-public';
import publicConfig from '../../tailwind.public.config.js';

// One Vite/PostCSS pipeline, exactly one Tailwind compiler per stylesheet.
// Keep existing Tailwind 4 consumers unchanged; isolate the public v3 design.
export default function tailwindByEntry() {
    return {
        postcssPlugin: 'gramlyze-tailwind-by-entry',
        async Once(root, {result}) {
            const filename = result.opts.from || root.source?.input.file || '';
            const isPublic = path.basename(filename) === 'catalog-public.css';
            const compiler = isPublic ? tailwindPublic(publicConfig) : tailwind4();
            const processed = await postcss([compiler]).process(root, result.opts);
            result.messages.push(...processed.messages);
        },
    };
}
