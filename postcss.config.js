import tailwindByEntry from './tools/build/tailwind-by-entry.js';
import autoprefixer from 'autoprefixer';

export default {
  plugins: [tailwindByEntry(), autoprefixer()],
};
