(function () {
    'use strict';

    const normalize = value => String(value || '').toLocaleLowerCase().trim().replace(/\s+/gu, ' ');

    const rank = (title, search) => {
        const text = normalize(title);
        const query = normalize(search);
        if (!text || !query) return 0;
        if (text === query) return 5;

        // Match quality takes precedence over title length. Shorter titles win
        // only within the same kind of match; unrelated titles always score zero.
        const coverage = Math.min(query.length / text.length, 1);
        if (text.startsWith(query)) return 4 + coverage;
        const position = text.indexOf(query);
        if (position >= 0) {
            return (/[^\p{L}\p{N}]/u.test(text[position - 1]) ? 3 : 2) + coverage;
        }
        const terms = [...new Set(query.split(' '))];
        return terms.length > 1 && terms.every(term => text.includes(term)) ? 1 + coverage : 0;
    };

    // Preserve the original order, even after several searches. Move siblings
    // in the DOM so the keyboard and screen-reader order follows the ranking.
    const createSorter = elements => {
        const groups = new Map();
        elements.forEach((element, index) => {
            const siblings = groups.get(element.parentElement) || [];
            siblings.push({element, index});
            groups.set(element.parentElement, siblings);
        });
        return score => {
            const reorder = () => groups.forEach((siblings, parent) => {
                const ordered = siblings.map(item => ({...item, score: score(item.element)}))
                    .sort((a, b) => b.score - a.score || a.index - b.index);
                const members = new Set(siblings.map(item => item.element));
                const current = Array.from(parent.children).filter(element => members.has(element));
                if (ordered.some((item, index) => item.element !== current[index])) {
                    ordered.forEach(item => parent.appendChild(item.element));
                }
            });
            if (window.Alpine?.mutateDom) window.Alpine.mutateDom(reorder);
            else reorder();
        };
    };

    window.GramlyzeTheorySearch = {normalize, rank, createSorter};
})();
