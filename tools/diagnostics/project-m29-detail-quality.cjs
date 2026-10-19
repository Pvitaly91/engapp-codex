'use strict';
// Explicit semantic author projection, not a word-count rule. No database or runtime writes.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');

const root = path.resolve(__dirname, '../..');
const file = 'm29-m13-sentence-structure';
const frozenSha = 'e28b75ee73f02fb7ed12bff25ef35423ee724a384927156eac877a3f79429d06';
// Each entry names one accepted point and its editorial decision. Counts are derived below.
const merges = {
    'm29-cleft-section-2': {
        1: 'Коротке пояснення, кого виправляє основний діалог.',
        2: 'Коротке визначення фокуса наведеного прикладу.',
        3: 'Короткий підсумок уже видимого порівняння.',
    },
    'm29-cleft-section-3': {
        1: 'Короткий контрастний приклад завершує основне зіставлення заперечень.',
        2: 'Коротке пояснення функції основного запитання.',
        3: 'Пряме продовження basic після крапки з комою; далі коротка ремарка про інтонацію та доречність.',
    },
    'm29-cleft-section-4': {
        1: 'Допустимий варіант was to remove і коротке застереження від узагальнення.',
        2: 'Коротке уточнення значення основного прикладу.',
        3: 'Коротке визначення фокуса основного прикладу.',
    },
    'm29-cleft-section-5': {
        1: 'Коротка інформація про допустимі who/that.',
        2: 'Застереження щодо варіативного узгодження та вправ.',
        3: 'Коротке пояснення часових форм видимого прикладу.',
    },
    'm29-noun-section-3': {
        1: 'Кроки 2–4 продовжують видимий крок 1: це повний основний розбір, не окрема глибша тема.',
        2: 'Кроки 2–4 завершують той самий основний розбір.',
        3: 'Кроки 2–3 завершують основний розбір; застереження про неоднозначне приєднання потрібне поруч із ним.',
    },
    'm29-noun-section-4': {
        1: 'Пояснення структури вже наведеного complement-прикладу.',
        2: 'Пояснення ролі that у вже наведеному relative-прикладі.',
        3: 'Коротке застереження проти ненадійного тесту видалення, без окремого розгорнутого аналізу.',
    },
    'm29-noun-section-5': {
        1: 'Застереження про спеціальні моделі узгодження; перше речення лише обмежує приклад, друге не розгортає нову модель.',
    },
    'm29-noun-section-6': {
        1: 'Основне пояснення apposition закінчується двокрапкою та відсилає до наступного basic-прикладу.',
        2: 'Коротке правило пунктуації цього прикладу.',
        4: 'Коротке пояснення належності місця.',
        5: 'Коротке пояснення належності місця у контрастному прикладі.',
    },
    'm29-noun-section-7': {
        1: 'Коротка контекстна ремарка про ясність терміна.',
        2: 'Коротка редакційна рекомендація завершує основний приклад спрощення.',
    },
    'm29-ellipsis-section-1': {
        1: 'Термінологічне застереження про різні класифікації, не окремий аналіз конструкції.',
    },
    'm29-ellipsis-section-2': {
        1: 'Коротке пояснення службових квадратних дужок.',
        2: 'Коротка ремарка про допустимість повної форми.',
        3: 'Коротке застереження проти довільного видалення підмета.',
        4: 'Тире-продовження містить саме відновлення Ready to leave?; без нього центральний basic-приклад нерозібраний. Далі основне порівняння регістру та граматичних моделей.',
    },
    'm29-ellipsis-section-3': {
        4: 'Коротка register note про I think not.',
        5: 'Коротке пояснення значення I am afraid.',
        6: 'Коротка практична рекомендація щодо незнайомого дієслова.',
    },
    'm29-ellipsis-section-4': {
        1: 'Коротке основне правило відповідності допоміжного дієслова часу й особі.',
        2: 'Пояснення, що саме замінює did so, і коротка register note: це аналіз самого basic-прикладу.',
        4: 'Коротка ремарка про допустиму альтернативу agreed to.',
    },
    'm29-ellipsis-section-6': {
        1: 'Альтернативне That із коротким застереженням проти механічного правила відстані.',
        2: 'Коротке пояснення анафоричного зв’язку.',
        3: 'Коротка редакційна рекомендація про довгі переліки.',
    },
    'm29-ellipsis-section-7': {
        5: 'Коротка рекомендація явнішого This test.',
    },
};

function metrics(html) {
    const text = html.replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ')
        .replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"').replace(/&#39;/g, "'");
    return {
        words: text.trim().split(/\s+/u).filter(word => /[\p{L}\p{N}]/u.test(word)).length,
        sentences: (text.match(/[.!?]+(?=\s|$)/g) || []).length,
    };
}

function project() {
    const frozenBytes = fs.readFileSync(path.join(root, 'database/content-patches', file + '.v1.json'));
    assert.equal(crypto.createHash('sha256').update(frozenBytes).digest('hex'), frozenSha);
    const old = JSON.parse(frozenBytes.toString('utf8'));
    const next = structuredClone(old);
    next.version = 2;
    const inventory = [], visited = new Set(), summary = [];
    for (const [targetIndex, target] of next.targets.entries()) {
        let before = 0, removed = 0, retained = 0;
        for (const plan of target.plans) for (const [index, point] of plan.points.entries()) {
            if (point.detail === '') continue;
            before++;
            const pointNumber = index + 1;
            const reason = merges[plan.key]?.[pointNumber];
            assert.equal(typeof reason, 'string', 'Unreviewed frozen point: ' + plan.key + '/' + pointNumber);
            const basic = metrics(point.basic), detail = metrics(point.detail);
            inventory.push({slug: target.slug, section: plan.key, point: pointNumber,
                basic_words: basic.words, detail_words: detail.words, detail_sentences: detail.sentences,
                decision: 'merge', reason});
            visited.add(plan.key + '/' + pointNumber);
            point.basic += '<br><br>' + point.detail;
            point.detail = '';
            removed++;
        }
        for (const plan of target.plans) retained += plan.points.filter(point => point.detail !== '').length;
        assert.deepEqual(target.after, old.targets[targetIndex].after, 'Educational, practice and DB payload must not change.');
        summary.push({slug: target.slug, before, removed, retained});
    }
    const expected = Object.entries(merges).flatMap(([section, points]) => Object.keys(points).map(point => section + '/' + point));
    assert.deepEqual([...visited].sort(), expected.sort(), 'Finite audit has stale or missing decisions.');
    const bytes = JSON.stringify(next, null, 4) + '\n';
    return {next, bytes, inventory, summary,
        sha256: crypto.createHash('sha256').update(bytes).digest('hex')};
}

if (require.main === module) {
    const result = project();
    if (process.argv.includes('--write')) {
        fs.writeFileSync(path.join(root, 'database/content-patches', file + '.v2.json'), result.bytes, {flag: 'wx'});
    }
    console.log(JSON.stringify({file, frozen_sha256: frozenSha, sha256: result.sha256,
        summary: result.summary, audit: result.inventory}, null, 2));
}
module.exports = {merges, metrics, project};
