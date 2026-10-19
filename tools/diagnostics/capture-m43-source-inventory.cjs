'use strict';

// Hash-only source inventory. Never boot Laravel, request HTTP, or read .env/DB files.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');

const ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const WT = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE = path.join(ROOT, 'storage/app/seo-m43-local');
const SCOPES = ['app', 'resources', 'routes', 'database', 'docs/content', 'public/js', 'public/css'];
const EXTENSIONS = new Set(['.php', '.json', '.md', '.js', '.cjs', '.mjs', '.css', '.scss', '.sass', '.less', '.ts', '.tsx', '.jsx', '.vue', '.html', '.htm', '.txt', '.yaml', '.yml', '.svg']);
const EXCLUDED = /(?:^|\/)(?:vendor|node_modules|storage|\.git|\.codex|\.agents|cache|caches|backup|backups|dump|dumps)(?:\/|$)|(?:^|\/)\.env(?:\.|$)|^public\/build(?:\/|$)/iu;
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const slash = value => value.replaceAll('\\', '/');

function relativeName(value) {
    assert.equal(typeof value, 'string');
    assert.ok(value && !value.startsWith('/') && !value.includes('\\') && !value.includes(':'));
    assert.ok(!value.split('/').some(part => part === '..' || part === '.' || part === ''));
    return value;
}
function wanted(value) {
    return SCOPES.some(scope => value.startsWith(scope + '/'))
        && !EXCLUDED.test(value) && EXTENSIONS.has(path.extname(value).toLowerCase());
}
function within(base, relative) {
    relativeName(relative);
    const resolved = path.resolve(base, relative);
    assert.ok(resolved.startsWith(path.resolve(base) + path.sep));
    return resolved;
}
function walk(base, relative, names) {
    if (EXCLUDED.test(relative)) return;
    const absolute = within(base, relative);
    if (!fs.existsSync(absolute)) return;
    const stat = fs.lstatSync(absolute);
    assert.ok(!stat.isSymbolicLink(), 'No source symlinks permitted: ' + relative);
    if (stat.isDirectory()) {
        for (const name of fs.readdirSync(absolute).sort()) walk(base, relative + '/' + name, names);
    } else if (stat.isFile() && wanted(relative)) names.add(relative);
}
function capture() {
    const tracked = execFileSync('git', ['-c', 'safe.directory=' + WT, '-c', 'core.bare=false', '-C', WT, 'ls-files', '-z'],
        {encoding: 'utf8', maxBuffer: 16 * 1024 * 1024, timeout: 30000, stdio: ['ignore', 'pipe', 'pipe']})
        .split('\0').filter(Boolean).map(slash).filter(wanted);
    const paths = new Set(tracked);
    // Served ROOT includes accepted untracked versions; the worktree walk also
    // catches task-authored additions for a later explicit allowlist comparison.
    for (const base of [ROOT, WT]) for (const scope of SCOPES) walk(base, scope, paths);
    const sorted = [...paths].sort();
    const sources = {};
    for (const [label, base] of [['root', ROOT], ['worktree', WT]]) {
        sources[label] = sorted.map(relative => {
            const absolute = within(base, relative);
            if (!fs.existsSync(absolute)) return {path: relative, exists: false, bytes: 0, sha256: null};
            const stat = fs.lstatSync(absolute);
            assert.ok(stat.isFile() && !stat.isSymbolicLink(), 'Expected ordinary source file: ' + relative);
            const bytes = fs.readFileSync(absolute);
            return {path: relative, exists: true, bytes: bytes.length, sha256: sha(bytes)};
        });
    }
    return {schema: 'm43-source-inventory-v1', captured_at: new Date().toISOString(),
        scopes: SCOPES, roots: {root: ROOT, worktree: WT}, sources};
}
function privateFile(name) {
    assert.match(name, /^(?:source-before|source-after|source-compare|source-allowlist)-v[1-9][0-9]*\.json$/u);
    return within(PRIVATE, name);
}
function writeExclusive(name, payload) {
    const filename = privateFile(name);
    fs.mkdirSync(PRIVATE, {recursive: true});
    fs.writeFileSync(filename, JSON.stringify(payload, null, 2) + '\n', {flag: 'wx'});
    return {file: filename, sha256: sha(fs.readFileSync(filename))};
}
function compare(beforeName, afterName, allowName, outputName) {
    const before = JSON.parse(fs.readFileSync(privateFile(beforeName), 'utf8'));
    const after = JSON.parse(fs.readFileSync(privateFile(afterName), 'utf8'));
    const allowed = JSON.parse(fs.readFileSync(privateFile(allowName), 'utf8'));
    for (const value of [before, after]) {
        assert.equal(value.schema, 'm43-source-inventory-v1');
        assert.deepEqual(value.scopes, SCOPES);
        assert.deepEqual(value.roots, {root: ROOT, worktree: WT});
    }
    assert.deepEqual(Object.keys(allowed).sort(), ['root', 'worktree']);
    const changes = [], unapproved = [], used = {root: new Set(), worktree: new Set()};
    for (const label of ['root', 'worktree']) {
        assert.ok(Array.isArray(allowed[label]));
        const allow = new Set(allowed[label].map(relativeName));
        assert.equal(allow.size, allowed[label].length, 'Duplicate allowlist entry');
        for (const name of allow) assert.ok(wanted(name), 'Allowlist outside source scope: ' + name);
        const old = new Map(before.sources[label].map(row => [row.path, row]));
        const current = new Map(after.sources[label].map(row => [row.path, row]));
        for (const name of [...new Set([...old.keys(), ...current.keys()])].sort()) {
            const left = old.get(name) ?? {path: name, exists: false, bytes: 0, sha256: null};
            const right = current.get(name) ?? {path: name, exists: false, bytes: 0, sha256: null};
            if (left.exists === right.exists && left.bytes === right.bytes && left.sha256 === right.sha256) continue;
            const row = {root: label, path: name, before: left, after: right, allowed: allow.has(name)};
            changes.push(row);
            if (!row.allowed) unapproved.push(row); else used[label].add(name);
        }
    }
    const result = {schema: 'm43-source-comparison-v1', compared_at: new Date().toISOString(),
        before_file: beforeName, after_file: afterName, allowlist_file: allowName,
        changes, unapproved, unchanged_outside_allowlist: unapproved.length === 0,
        unused_allowlist: Object.fromEntries(['root', 'worktree'].map(label => [label, allowed[label].filter(name => !used[label].has(name))]))};
    const artifact = writeExclusive(outputName, result);
    console.log(JSON.stringify({...artifact, changes: changes.length, unapproved: unapproved.length, pass: unapproved.length === 0}));
    if (unapproved.length) process.exitCode = 1;
}

const [mode, ...args] = process.argv.slice(2);
if (mode === 'capture') {
    assert.equal(args.length, 1, 'capture <source-before-vN.json|source-after-vN.json>');
    assert.match(args[0], /^source-(?:before|after)-v[1-9][0-9]*\.json$/u);
    const payload = capture();
    console.log(JSON.stringify({...writeExclusive(args[0], payload),
        paths: payload.sources.root.length,
        root_files: payload.sources.root.filter(row => row.exists).length,
        worktree_files: payload.sources.worktree.filter(row => row.exists).length,
        db_access: false, http_access: false, source_writes: false}));
} else if (mode === 'compare') {
    assert.equal(args.length, 4, 'compare <before> <after> <allowlist> <output>');
    compare(...args);
} else {
    throw new Error('Mode must be capture or compare');
}
