'use strict';
// Pure file evidence only. No application bootstrap, environment read, request or DB connection.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const root = 'D:/DEV/htdocs/gramlyze.loc';
const source = path.resolve(__dirname, '../..');
const dir = path.join(root, 'storage/app/seo-m42-local');
const backup = path.join(dir, 'source-sync-before-v1');
const shared = [
 'resources/views/theory/partials/content-block.blade.php', 'resources/views/theory/show.blade.php',
 'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
 'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
 'resources/views/engram/theory/blocks-v3/forms-grid.blade.php',
 'resources/views/engram/theory/widgets/lesson-rule-cards.blade.php',
 'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
 'resources/views/engram/theory/blocks-v3/mistakes-grid.blade.php',
 'resources/views/theory/partials/point-detail-fragment.blade.php',
];
const created = ['app/Support/M42NativeDesignPackage.php',
 'database/content-patches/m42-native-design-registry.v1.json', 'database/content-patches/m42-native-design.v1.json',
 'resources/views/engram/theory/blocks-v3/m42-native-design-styles.blade.php'];
const sha = value => crypto.createHash('sha256').update(value).digest('hex');
const norm = value => value.toString('utf8').replaceAll('\r\n', '\n');
const rows = [...shared, ...created].map(file => {
 const live = fs.readFileSync(path.join(root, file));
 const versioned = fs.readFileSync(path.join(source, file));
 if (file.endsWith('/theory/show.blade.php')) {
   const before = fs.readFileSync(path.join(backup, file));
   const ownLine = "                            'm42StyleContext' => app()->getLocale() === 'uk',\n";
   assert.equal(norm(live).split(ownLine).length, 2, 'One finite theory-only opt-in line required');
   assert.equal(norm(live).replace(ownLine, ''), norm(before), 'Foreign served show changes must remain exact');
   return {file, sha256: sha(live), beforeSha256: sha(before), foreignTextPreserved: true, ownAddedLines: 1};
 }
 assert.deepEqual(live, versioned, 'Served file differs from reviewed worktree: ' + file);
 return {file, sha256: sha(live), beforeSha256: shared.includes(file)
   ? sha(fs.readFileSync(path.join(backup, file))) : null, sourceExact: true};
});
const result = {pass: true, sharedFiles: shared.length, newFiles: created.length,
 foreignServedShowChangesPreserved: true, sourceBackup: backup, files: rows};
const name = process.argv[2];
if (name) {
 assert.match(name, /^source-sync-proof-v[1-9][0-9]*\.json$/);
 fs.writeFileSync(path.join(dir, name), JSON.stringify(result, null, 2) + '\n', {flag: 'wx'});
}
console.log(JSON.stringify(result, null, 2));
