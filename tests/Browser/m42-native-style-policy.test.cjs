const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');
const read = p => fs.readFileSync(path.join(root, p), 'utf8');
const registry = JSON.parse(read('database/content-patches/m42-native-design-registry.v1.json'));
const projection = JSON.parse(read('database/content-patches/m42-native-design.v1.json'));
const styles = read('resources/views/engram/theory/blocks-v3/m42-native-design-styles.blade.php');
const dispatcher = read('resources/views/theory/partials/content-block.blade.php');

for (const owner of registry.targets) {
  test(`${owner.slug}: finite native kind/color hooks, no content/DOM replacement`, () => {
    const plan = projection.targets.find(x => x.identity === owner.identity);
    assert.ok(plan); assert.equal(plan.locale, 'uk');
    assert.equal(plan.blocks.length, owner.blocks.length);
    for (const [i, block] of plan.blocks.entries()) {
      assert.equal(block.component, owner.blocks[i].type);
      assert.ok([null, 'blue', 'emerald', 'sky', 'amber', 'rose'].includes(block.color));
      if (block.color) assert.ok(styles.includes(`[data-m42-color=${block.color}]`));
      for (const forbidden of ['data', 'body', 'points', 'view', 'text', 'title', 'answer'])
        assert.equal(Object.hasOwn(block, forbidden), false);
      assert.equal(block.source_index, i);
    }
    assert.match(dispatcher, /\$m42Design = \$m27\['m42_native_design'\] \?\? null/);
    assert.match(dispatcher, /'m42Design' => \$m42Design/);
    assert.match(dispatcher, /\$m42StyleContext \?\? false/);
  });
}

test('only UK theory caller opts in; shared course/category/test callers do not', () => {
  assert.match(read('resources/views/theory/show.blade.php'), /'m42StyleContext' => app\(\)->getLocale\(\) === 'uk'/);
  const views = path.join(root, 'resources/views');
  function walk(dir) {
    return fs.readdirSync(dir, {withFileTypes: true}).flatMap(item => item.isDirectory()
      ? walk(path.join(dir, item.name)) : [path.join(dir, item.name)]);
  }
  const opts = walk(views).filter(p => p.endsWith('.blade.php') && fs.readFileSync(p, 'utf8').includes("'m42StyleContext' =>"));
  assert.deepEqual(opts.map(p => path.relative(views, p).replaceAll('\\', '/')), ['theory/show.blade.php']);
});

test('all palette selectors are source-hook-scoped; no global layout/clipping', () => {
  const css = styles.match(/<style>([\s\S]*?)<\/style>/)[1].replace(/\/\*[\s\S]*?\*\//g, '');
  for (const line of css.split('\n').map(x => x.trim()).filter(Boolean)) {
    assert.ok(line.startsWith('.theory-design .m42-native-design') || line.startsWith('.dark .theory-design .m42-native-design')
      || line.startsWith('@media print{.theory-design .m42-native-design'), line);
  }
  assert.doesNotMatch(css, /overflow\s*:\s*hidden|text-overflow\s*:\s*ellipsis|text-transform\s*:\s*uppercase|display\s*:\s*none/);
  assert.doesNotMatch(css, /\.theory-sidebar|\.theory-hero|\.m41-existing-design|[;{]\s*(?:min-|max-)?height\s*:/);
  assert.match(css, /data-m42-native-kind=comparison-table[^\n]+min-width:9rem;overflow-wrap:break-word/);
  assert.match(css, /\[data-m42-native-design\]\[data-m42-native-kind=forms-grid\][^\n]+background:linear-gradient/);
  assert.doesNotMatch(css, /var\(--surface-2\)/, 'Optional M41 palette variable must have a defined local fallback');
  assert.match(read('resources/css/catalog-public.css'), /--surface:\s*/);
  assert.match(css, /:not\(\[data-m42-native-kind=practice-set\]\)/);
});

test('natural answer casing and all four existing practice group palettes are preserved', () => {
  assert.match(styles, /button:is\([^\n]+text-transform:none/);
  for (const color of ['blue', 'amber', 'emerald', 'purple']) {
    assert.ok(styles.includes(`:is(.border-${color}-100,.border-${color}-200)`));
  }
  assert.match(styles, /em\[data-m42-em-language=en\][^\n]+font-family:ui-monospace/);
  assert.match(styles, /em\[data-m42-em-language=uk-template\][^\n]+font-family:inherit/);
});
