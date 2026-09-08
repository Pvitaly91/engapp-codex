import fs from 'node:fs';

const contentClasses = JSON.parse(fs.readFileSync(new URL('./resources/css/catalog-public-classes.json', import.meta.url), 'utf8'));

// Match the public Play CDN 3.4.17 design; do not inherit legacy app.css's theme.
export default {
    darkMode: 'class',
    content: {
        relative: true,
        files: [
            './resources/views/layouts/catalog-public.blade.php',
            './resources/views/layouts/partials/**/*.blade.php',
            './resources/views/{home,catalog-tests-cards,test-show}.blade.php',
            './resources/views/{theory,courses,words,verbs,search,test-modes,components}/**/*.blade.php',
            './resources/views/engram/theory/**/*.blade.php',
            './resources/js/**/*.js',
            './app/Support/**/*.php',
            './database/seeders/{Page_V3,V3}/**/*.{php,json}',
        ],
    },
    safelist: contentClasses.utilities,
    theme: {
        extend: {
            colors: {
                brand: {50: '#eef2ff', 100: '#e0e7ff', 600: '#4350e6', 700: '#3730a3'},
                steel: '#5d7185', night: '#13233b', ocean: '#2f67b1',
                amber: '#f59b2f', mist: '#f5fbff', shell: '#fffefd', line: '#d8e2ee',
            },
            fontFamily: {display: ['Archivo', 'sans-serif'], body: ['Manrope', 'sans-serif']},
            boxShadow: {
                panel: '0 24px 60px rgba(17, 38, 63, 0.18)',
                card: '0 12px 28px rgba(17, 38, 63, 0.10)',
            },
        },
    },
    plugins: [],
};
