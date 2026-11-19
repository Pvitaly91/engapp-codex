'use strict';
const assert = require('node:assert/strict');

// Load the same complete dependency group as the actual card/step browser scripts.
const wordPolicyHelpers = ['presentPerfectPassiveWordPolicy', 'presentPerfectPassiveWordPaths',
  'presentPerfectPassiveNextWords', 'syncPresentPerfectPassiveWordPath', 'presentPerfectPassiveWordChoices',
  'restorePresentPerfectPassiveWordProgress'];

function assertWordSentencePath(c, raw, sentence, variants) {
  const q = JSON.parse(JSON.stringify(raw)); q.chosen = [];
  assert.ok(c.presentPerfectPassiveWordPolicy(q), raw.uuid+' must use the current finite policy');
  const path = c.presentPerfectPassiveWordPaths(q).find(words => variants.normalize(words.join(' ')) === variants.normalize(sentence));
  assert.ok(path, raw.uuid+'/'+sentence+' must have a reviewed full-sentence path');
  for (const [slot, word] of path.entries()) {
    assert.equal(c.testAnswerMatches(q, slot, word), true, raw.uuid+'/'+sentence+'/'+slot);
    assert.equal(c.testAnswerMatches(q, slot, word+' extra'), false, 'A word slot must not accept a multiword phrase');
    q.chosen[slot] = word;
    assert.equal(c.syncPresentPerfectPassiveWordPath(q), true, raw.uuid+' must resize only to a reviewed prefix path');
    assert.equal(q.chosen.every(Boolean), slot === path.length - 1, 'Every word remains required');
  }
  assert.equal(q.answers.length, path.length);
  assert.equal(variants.normalize(q.chosen.join(' ')), variants.normalize(sentence));
}

function assertWordChoiceCount(c, q, slot, choices, variants) {
  const allowed = c.presentPerfectPassiveNextWords(q, slot);
  assert.ok(allowed, q.uuid+' must expose a prefix-bound word policy');
  // Four buttons normally; never hide a reviewed next word when a source has more than four alternatives.
  assert.equal(choices.length, Math.max(4, new Set(allowed.map(value=>variants.normalize(value))).size), q.uuid+'/'+slot);
  for (const choice of choices) {
    assert.doesNotMatch(choice, /\s/u, q.uuid+' one real word per choice');
    assert.equal(choice, variants.withoutSentencePunctuation(choice), q.uuid+' no sentence punctuation in buttons');
  }
}

module.exports = {wordPolicyHelpers, assertWordSentencePath, assertWordChoiceCount};
