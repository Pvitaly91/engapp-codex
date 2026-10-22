<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Display-only regression: actual Blade expression + actual bundled Alpine function evaluator, no app/DB/DOM/network. */
class M76TokenLabelDisplayTest extends TestCase
{
    public function test_bank_labels_use_only_own_strings_and_never_invoke_inherited_functions_or_write_reactive_scope(): void
    {
        $root = dirname(__DIR__, 2);
        // A source-only WT intentionally has no vendor tree; use the actually loaded Composer installation.
        $dependencyRoot = dirname((new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName(), 3);
        $javascript = <<<'JS'
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm');
const assert = require('node:assert/strict'), crypto = require('node:crypto');
const root = process.argv[2], dependencyRoot = process.argv[3], tracked = new Map();
assert.ok(path.isAbsolute(root)); assert.ok(path.isAbsolute(dependencyRoot));
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
function read(relative, directory = root) { const full = path.join(directory, relative), bytes = fs.readFileSync(full); tracked.set(full, sha(bytes)); return bytes.toString('utf8'); }
const component = read('resources/views/components/text-block-practice-questions.blade.php');
const bank = component.match(/<template x-for="token in tokenBank"[\s\S]*?<\/template>/g);
assert.equal(bank.length, 1, 'Read the real bank button, not the answer-chip label.');
const expression = bank[0].match(/x-text="([^"]+)"/)[1];
assert.equal(expression, "Object.prototype.hasOwnProperty.call(currentQuestion?.token_labels ?? {}, token.value) && typeof currentQuestion.token_labels[token.value] === 'string' ? currentQuestion.token_labels[token.value] : token.value");
const selected = component.match(/<template x-for="token in selectedTokens"[\s\S]*?<\/template>/g);
assert.equal(selected.length, 1); assert.match(selected[0], /x-text="token\.value"/);
assert.doesNotMatch(selected[0], /token_labels/, 'Selected answer text is not changed by the bank-only label fix.');

// Execute the function-auto-evaluation implementation shipped with the actual Livewire/Alpine bundle.
// Unlike a copied fallback function, this reproduces Alpine invoking a function-valued x-text result.
const bundled = read('vendor/livewire/livewire/dist/livewire.js', dependencyRoot);
const start = bundled.indexOf('function runIfTypeOfFunction('); assert.notEqual(start, -1);
let end = bundled.indexOf('{', start), depth = 1;
for (end++; depth; end++) { assert.ok(end < bundled.length); if (bundled[end] === '{') depth++; else if (bundled[end] === '}') depth--; }
const originalEvaluator = bundled.slice(start, end);
assert.match(originalEvaluator, /value\.apply\(scope2, params\)/);
const run = vm.runInNewContext('let shouldAutoEvaluateFunctions = true; ' + originalEvaluator + '; runIfTypeOfFunction', {
  handleError(error) { throw error; },
});
const names = ['every', 'shift', 'constructor', 'length', 'map', 'some', 'filter', 'toString', 'hasOwnProperty', 'ordinary'];
let functionCalls = 0, prototypeGets = 0, reactiveWrites = 0;
const forbiddenFunction = () => { functionCalls++; throw Error('A label function must never be invoked.'); };
const inherited = Object.create({ every: 'inherited label must not leak', shift: forbiddenFunction, constructor: 'inherited constructor' });
const poison = Object.create(null);
for (const token of names) Object.defineProperty(poison, token, { get() { prototypeGets++; throw Error('Inherited getter must never be read: ' + token); } });
const poisonObject = Object.create(poison);
const ownArray = Object.assign([], { every: 'кожен', shift: 'зміна', constructor: 'конструктор' });
const ownObject = { every: 'кожен', shift: 'зміна', constructor: 'конструктор', length: 'довжина', ordinary: 'звичайний' };
const nonStrings = { every: forbiddenFunction, shift: false, constructor: forbiddenFunction, length: 12, map: null, some: undefined };
const fixtures = [
  { name: 'empty-array', question: { token_labels: [] }, labels: [] },
  { name: 'empty-object', question: { token_labels: {} }, labels: {} },
  { name: 'own-string-array', question: { token_labels: ownArray }, labels: ownArray },
  { name: 'own-string-object', question: { token_labels: ownObject }, labels: ownObject },
  { name: 'inherited-labels-object', question: { token_labels: inherited }, labels: inherited },
  { name: 'poison-prototype-object', question: { token_labels: poisonObject }, labels: poisonObject },
  { name: 'own-non-string-labels', question: { token_labels: nonStrings }, labels: nonStrings },
  { name: 'missing-map', question: {}, labels: undefined },
  { name: 'null-map', question: { token_labels: null }, labels: null },
  { name: 'null-question', question: null, labels: null },
  { name: 'missing-question', question: undefined, labels: undefined },
];
let cases = 0, ownLabels = 0, literalFallbacks = 0;
const results = [];
for (const fixture of fixtures) {
  const entries = [];
  for (const value of names) {
    const token = Object.freeze({ id: 'token-' + value, value });
    const question = fixture.question, beforeKeys = question && Object.keys(question);
    const labelsKeys = fixture.labels && Object.keys(fixture.labels);
    const data = { currentQuestion: question, token };
    const scope = new Proxy(data, { set(target, key, value) { reactiveWrites++; return Reflect.set(target, key, value); },
      defineProperty(target, key, descriptor) { reactiveWrites++; return Reflect.defineProperty(target, key, descriptor); },
      deleteProperty(target, key) { reactiveWrites++; return Reflect.deleteProperty(target, key); } });
    const actual = vm.runInNewContext(expression, scope);
    assert.equal(typeof actual, 'string', fixture.name + '/' + value + ' cannot return a callable, number or undefined.');
    const mapped = fixture.labels != null && Object.prototype.hasOwnProperty.call(fixture.labels, value)
      && typeof Object.getOwnPropertyDescriptor(fixture.labels, value).value === 'string';
    const expected = mapped ? Object.getOwnPropertyDescriptor(fixture.labels, value).value : value;
    assert.equal(actual, expected); mapped ? ownLabels++ : literalFallbacks++;
    let receiverCalls = 0, text;
    run(result => { receiverCalls++; assert.equal(typeof result, 'string'); text = result; }, actual, scope, [], null);
    assert.equal(receiverCalls, 1); assert.equal(text, expected);
    assert.deepEqual(Object.keys(data), ['currentQuestion', 'token']);
    if (question) assert.deepEqual(Object.keys(question), beforeKeys);
    if (fixture.labels) assert.deepEqual(Object.keys(fixture.labels), labelsKeys);
    assert.equal(token.value, value); assert.equal(token.id, 'token-' + value);
    entries.push({ token: value, text, own_label: mapped }); cases++;
  }
  results.push({ fixture: fixture.name, entries });
}
assert.equal(cases, 110); assert.equal(ownLabels, 8); assert.equal(literalFallbacks, 102);
assert.equal(functionCalls, 0); assert.equal(prototypeGets, 0); assert.equal(reactiveWrites, 0);
for (const [full, before] of tracked) assert.equal(sha(fs.readFileSync(full)), before, 'No source is written by this probe.');
console.log(JSON.stringify({ kind: 'actual bank Blade x-text and actual bundled Alpine function evaluator; not live UI acceptance',
  cases, fixture_count: fixtures.length, token_names: names, own_string_labels_preserved: ownLabels, literal_fallbacks: literalFallbacks,
  forbidden_function_calls: functionCalls, inherited_getter_reads: prototypeGets, reactive_scope_writes: reactiveWrites,
  selected_answer_chip_unchanged: true, sources_unchanged: true, source_files: tracked.size,
  db_queries: 0, http_requests: 0, file_writes: 0, results }));
JS;
        $process = new Process([getenv('NODE_BINARY') ?: 'node', '-', $root, $dependencyRoot], $root, timeout: 30);
        $process->setInput($javascript); $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(110, $result['cases']); self::assertSame(11, $result['fixture_count']);
        self::assertSame(8, $result['own_string_labels_preserved']); self::assertSame(102, $result['literal_fallbacks']);
        foreach (['every', 'shift', 'constructor', 'length'] as $token) { self::assertContains($token, $result['token_names']); }
        foreach (['forbidden_function_calls', 'inherited_getter_reads', 'reactive_scope_writes', 'db_queries', 'http_requests', 'file_writes'] as $field) {
            self::assertSame(0, $result[$field], $field);
        }
        self::assertTrue($result['selected_answer_chip_unchanged']); self::assertTrue($result['sources_unchanged']);
        self::assertSame(2, $result['source_files']);
    }
}
