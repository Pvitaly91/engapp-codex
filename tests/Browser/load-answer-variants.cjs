const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const context = vm.createContext({ GRAMLYZE_CONTRACTION_RULES: require('../../public/data/english-contractions.json') });
vm.runInContext(fs.readFileSync(path.join(__dirname, '../../public/js/english-answer-variants.js'), 'utf8'), context);
module.exports = context.EnglishAnswerVariants;
