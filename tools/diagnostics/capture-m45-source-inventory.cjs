'use strict';
// Private hash-only managed-source/config evidence. No Laravel, HTTP, DB or source writes.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const WT = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE = path.join(ROOT, 'storage/app/seo-m45-local');
const BASE = 'e5b3ee339410afe3bd6b6bad33eb62fced4d6671';
const SCOPES = ['app', 'bootstrap', 'config', 'database', 'docs', 'public', 'resources', 'routes', 'tests', 'tools'];
const EXCLUDED = /(?:^|\/)(?:vendor|node_modules|storage|\.git|\.codex|\.agents|cache|caches|backup|backups|dump|dumps)(?:\/|$)|(?:^|\/)\.env(?:\.|$)|^public\/build(?:\/|$)/iu;
const SHARED = [
    'resources/views/theory/show.blade.php',
    'resources/views/theory/partials/content-block.blade.php',
    'resources/views/courses/partials/theory-page-content.blade.php',
    'resources/views/engram/theory/blocks-v3/authored-practice-ui.blade.php',
];
const DEFINITIONS = [
    'FutureFormsFuturePerfectVsFutureContinuousTheorySeeder',
    'FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder',
    'FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder',
].map(name => 'database/seeders/Page_V3/FutureForms/' + name + '/definition.json');
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const slash = value => value.replaceAll('\\', '/');
function relativeName(value) {
    assert.equal(typeof value, 'string');
    assert.ok(value && !value.startsWith('/') && !value.includes('\\') && !value.includes(':'));
    assert.ok(!value.split('/').some(part => ['', '.', '..'].includes(part)));
    return value;
}
function wanted(value) { return !EXCLUDED.test(value) && (!value.includes('/') || SCOPES.some(scope => value.startsWith(scope + '/'))); }
function within(base, relative) {
    relativeName(relative);
    const absolute = path.resolve(base, relative), root = path.resolve(base);
    assert.ok(absolute.toLowerCase().startsWith((root + path.sep).toLowerCase()));
    let cursor = absolute;
    while (cursor.toLowerCase() !== root.toLowerCase()) {
        if (fs.existsSync(cursor)) assert.ok(!fs.lstatSync(cursor).isSymbolicLink(), 'Linked path refused: ' + relative);
        const parent = path.dirname(cursor); assert.notEqual(parent, cursor); cursor = parent;
    }
    return absolute;
}
function walk(base, relative, names) {
    if (EXCLUDED.test(relative)) return;
    const absolute = within(base, relative);
    if (!fs.existsSync(absolute)) return;
    const stat = fs.lstatSync(absolute); assert.ok(!stat.isSymbolicLink(), 'No managed-source links: ' + relative);
    if (stat.isDirectory()) for (const name of fs.readdirSync(absolute).sort()) walk(base, relative + '/' + name, names);
    else if (stat.isFile() && wanted(relative)) names.add(relative);
}
function row(base, relative) {
    const absolute = within(base, relative);
    if (!fs.existsSync(absolute)) return { path: relative, exists: false, bytes: 0, sha256: null };
    assert.ok(fs.lstatSync(absolute).isFile());
    const bytes = fs.readFileSync(absolute);
    return { path: relative, exists: true, bytes: bytes.length, sha256: sha(bytes) };
}
function protectedNames(base) {
    const names = ['.env', '.env.testing', '.env.test', 'phpunit.xml', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json'];
    const cache = within(base, 'bootstrap/cache');
    if (fs.existsSync(cache)) for (const name of fs.readdirSync(cache).sort()) {
        if (name.endsWith('.php')) names.push('bootstrap/cache/' + name);
    }
    return names;
}
function capture() {
    const tracked = execFileSync('git', ['-c', 'safe.directory=' + WT, '-c', 'core.bare=false', '-C', WT, 'ls-files', '-z'],
        { encoding: 'utf8', maxBuffer: 32 * 1024 * 1024, timeout: 30000, stdio: ['ignore', 'pipe', 'pipe'] })
        .split('\0').filter(Boolean).map(slash).filter(wanted);
    const names = new Set(tracked);
    for (const base of [ROOT, WT]) {
        for (const scope of SCOPES) walk(base, scope, names);
        for (const name of fs.readdirSync(base).sort()) {
            if (wanted(name) && fs.lstatSync(path.join(base, name)).isFile()) names.add(name);
        }
    }
    const sorted = [...names].sort(), sources = {}, protectedConfig = {};
    const protectedSet = [...new Set([...protectedNames(ROOT), ...protectedNames(WT)])].sort();
    for (const [label, base] of [['root', ROOT], ['worktree', WT]]) {
        sources[label] = sorted.map(name => row(base, name));
        // Digests only; .env/config contents are never included in evidence/output.
        protectedConfig[label] = protectedSet.map(name => row(base, name));
    }
    return { schema: 'm45-source-inventory-v1', captured_at: new Date().toISOString(), base_sha: BASE,
        scopes: SCOPES, roots: { root: ROOT, worktree: WT }, sources, protected_config: protectedConfig };
}
function privateFile(name) {
    assert.match(name, /^(?:source-before|source-after|source-compare|source-allowlist)-v[1-9][0-9]*\.json$/u);
    return within(PRIVATE, name);
}
function writeExclusive(filename, bytes) {
    fs.mkdirSync(path.dirname(filename), { recursive: true });
    fs.writeFileSync(filename, bytes, { flag: 'wx' });
    assert.equal(sha(fs.readFileSync(filename)), sha(bytes));
}
function beforeBackups(payload) {
    const directory = within(PRIVATE, 'source-sync-before-v1');
    assert.equal(fs.existsSync(directory), false, 'Exclusive initial ROOT backups');
    const pending = [], records = [];
    for (const [index, name] of SHARED.entries()) {
        const bytes = execFileSync('git', ['-c', 'safe.directory=' + WT, '-c', 'core.bare=false', '-C', WT, 'show', BASE + ':' + name],
            { maxBuffer: 16 * 1024 * 1024, timeout: 30000, stdio: ['ignore', 'pipe', 'pipe'] });
        pending.push([within(PRIVATE, 'm45-shared-base-v1-' + index + '.bin'), bytes]);
    }
    for (const name of [...SHARED, ...DEFINITIONS]) {
        const bytes = fs.readFileSync(within(ROOT, name));
        const initial = payload.sources.root.find(record => record.path === name);
        assert.equal(initial?.sha256, sha(bytes), 'ROOT changed during BEFORE capture: ' + name);
        const record = { path: name, root_before_sha256: sha(bytes), bytes: bytes.length,
            backup_basename: 'source-sync-before-v1/' + name };
        const index = SHARED.indexOf(name);
        if (index >= 0) {
            record.base_basename = 'm45-shared-base-v1-' + index + '.bin';
            record.base_sha256 = sha(pending[index][1]);
        }
        records.push(record); pending.push([within(PRIVATE, record.backup_basename), bytes]);
    }
    for (const [filename] of pending) assert.equal(fs.existsSync(filename), false, 'Exclusive shared/source evidence');
    for (const [filename, bytes] of pending) writeExclusive(filename, bytes);
    const manifest = { schema: 'm45-source-backup-v1', base_sha: BASE, roots: payload.roots,
        captured_at: payload.captured_at, files: records };
    writeExclusive(within(PRIVATE, 'source-sync-before-v1/manifest.json'), JSON.stringify(manifest, null, 2) + '\n');
    return records;
}
function compare(beforeName, afterName, allowName, outputName) {
    const before = JSON.parse(fs.readFileSync(privateFile(beforeName), 'utf8'));
    const after = JSON.parse(fs.readFileSync(privateFile(afterName), 'utf8'));
    const allow = JSON.parse(fs.readFileSync(privateFile(allowName), 'utf8'));
    for (const value of [before, after]) {
        assert.equal(value.schema, 'm45-source-inventory-v1'); assert.equal(value.base_sha, BASE);
        assert.deepEqual(value.scopes, SCOPES); assert.deepEqual(value.roots, { root: ROOT, worktree: WT });
    }
    assert.deepEqual(Object.keys(allow).sort(), ['root', 'worktree']);
    const changes = [], unapproved = [], protectedChanges = [];
    for (const label of ['root', 'worktree']) {
        assert.ok(Array.isArray(allow[label]));
        const allowed = new Set(allow[label].map(relativeName));
        assert.equal(allowed.size, allow[label].length);
        for (const name of allowed) assert.ok(wanted(name), 'Allowlist stays within managed sources');
        for (const [kind, destination] of [['sources', changes], ['protected_config', protectedChanges]]) {
            const old = new Map(before[kind][label].map(record => [record.path, record]));
            const current = new Map(after[kind][label].map(record => [record.path, record]));
            for (const name of [...new Set([...old.keys(), ...current.keys()])].sort()) {
                const left = old.get(name) ?? { path: name, exists: false, bytes: 0, sha256: null };
                const right = current.get(name) ?? { path: name, exists: false, bytes: 0, sha256: null };
                if (left.exists === right.exists && left.bytes === right.bytes && left.sha256 === right.sha256) continue;
                const record = { root: label, path: name, before: left, after: right, allowed: kind === 'sources' && allowed.has(name) };
                destination.push(record);
                if (kind === 'sources' && !record.allowed) unapproved.push(record);
            }
        }
    }
    const result = { schema: 'm45-source-comparison-v1', compared_at: new Date().toISOString(), base_sha: BASE,
        before_file: beforeName, after_file: afterName, allowlist_file: allowName, changes, unapproved,
        protected_config_changes: protectedChanges, unchanged_outside_allowlist: unapproved.length === 0,
        protected_config_unchanged: protectedChanges.length === 0 };
    const filename = privateFile(outputName); writeExclusive(filename, JSON.stringify(result, null, 2) + '\n');
    console.log(JSON.stringify({ file: filename, sha256: sha(fs.readFileSync(filename)), changes: changes.length,
        unapproved: unapproved.length, protected_config_changes: protectedChanges.length, pass: !unapproved.length && !protectedChanges.length }));
    if (unapproved.length || protectedChanges.length) process.exitCode = 1;
}
if (require.main === module) {
    const [mode, ...args] = process.argv.slice(2);
    if (mode === 'capture') {
        assert.equal(args.length, 1); assert.match(args[0], /^source-(?:before|after)-v[1-9][0-9]*\.json$/u);
        const filename = privateFile(args[0]); assert.equal(fs.existsSync(filename), false, 'Exclusive inventory');
        const payload = capture(); const backups = args[0] === 'source-before-v1.json' ? beforeBackups(payload) : [];
        writeExclusive(filename, JSON.stringify(payload, null, 2) + '\n');
        console.log(JSON.stringify({ file: filename, sha256: sha(fs.readFileSync(filename)), paths: payload.sources.root.length,
            root_files: payload.sources.root.filter(record => record.exists).length,
            worktree_files: payload.sources.worktree.filter(record => record.exists).length,
            protected_config_paths: payload.protected_config.root.length, original_backups: backups.length,
            base_sha: BASE, source_writes: false, db_access: false, http_access: false }));
    } else if (mode === 'compare') { assert.equal(args.length, 4); compare(...args); }
    else throw new Error('Use capture <source-before/after-vN.json> or compare <before> <after> <allowlist> <output>');
}
module.exports = { ROOT, WT, PRIVATE, BASE, SHARED, DEFINITIONS, capture, compare, within, wanted };
