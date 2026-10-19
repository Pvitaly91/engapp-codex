'use strict';
// Fresh private hash-only evidence. No framework, HTTP, DB, configuration or source writes.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto'), assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const ROOT = 'D:/DEV/htdocs/gramlyze.loc';
const WT = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
const PRIVATE = path.join(ROOT, 'storage/app/theory-template-local');
const BASE = 'e58986a9dd5c5d0289389d41550d90ef28e8fba4';
const SCOPES = ['app', 'bootstrap', 'config', 'database', 'docs', 'public', 'resources', 'routes', 'tests', 'tools'];
const EXCLUDED = /(?:^|\/)(?:vendor|node_modules|storage|\.git|\.codex|\.agents|cache|caches|backup|backups|dump|dumps)(?:\/|$)|(?:^|\/)\.env(?:\.|$)|^public\/build(?:\/|$)/iu;
const hash = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const wanted = name => !EXCLUDED.test(name) && (!name.includes('/') || SCOPES.some(scope => name.startsWith(scope + '/')));
function valid(name) {
    assert.equal(typeof name, 'string');
    assert.ok(name && !name.startsWith('/') && !name.includes('\\') && !name.includes(':'));
    assert.ok(!name.split('/').some(part => ['', '.', '..'].includes(part)));
    return name;
}
function walk(base, relative, names) {
    if (EXCLUDED.test(relative)) return;
    valid(relative);
    const absolute = path.join(base, relative);
    if (!fs.existsSync(absolute)) return;
    const stat = fs.lstatSync(absolute);
    assert.ok(!stat.isSymbolicLink(), 'Linked source refused: ' + relative);
    if (stat.isDirectory()) for (const name of fs.readdirSync(absolute).sort()) walk(base, relative + '/' + name, names);
    else if (stat.isFile() && wanted(relative)) names.add(relative);
}
function row(base, name) {
    valid(name); const absolute = path.join(base, name);
    if (!fs.existsSync(absolute)) return { path: name, exists: false, bytes: 0, sha256: null };
    const stat = fs.lstatSync(absolute); assert.ok(stat.isFile() && !stat.isSymbolicLink());
    const bytes = fs.readFileSync(absolute);
    return { path: name, exists: true, bytes: bytes.length, sha256: hash(bytes) };
}
function protectedNames(base) {
    const names = ['.env', '.env.testing', '.env.test', 'phpunit.xml', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json'];
    const directory = path.join(base, 'bootstrap/cache');
    if (fs.existsSync(directory)) for (const name of fs.readdirSync(directory).sort()) {
        const stat = fs.lstatSync(path.join(directory, name));
        assert.ok(!stat.isSymbolicLink());
        if (stat.isFile()) names.push('bootstrap/cache/' + name);
    }
    return names;
}
function capture() {
    const tracked = execFileSync('git', ['-c', 'safe.directory=' + WT, '-c', 'core.bare=false', '-C', WT, 'ls-files', '-z'],
        { encoding: 'utf8', maxBuffer: 32 * 1024 * 1024, timeout: 30000, stdio: ['ignore', 'pipe', 'pipe'] })
        .split('\0').filter(Boolean).map(name => name.replaceAll('\\', '/')).filter(wanted);
    const names = new Set(tracked);
    for (const base of [ROOT, WT]) {
        assert.ok(fs.statSync(base).isDirectory());
        for (const scope of SCOPES) walk(base, scope, names);
        for (const name of fs.readdirSync(base).sort()) {
            const stat = fs.lstatSync(path.join(base, name));
            if (wanted(name) && stat.isFile()) names.add(name);
        }
    }
    const sorted = [...names].sort(), sources = {}, protectedConfig = {};
    const protectedSet = [...new Set([...protectedNames(ROOT), ...protectedNames(WT)])].sort();
    for (const [label, base] of [['root', ROOT], ['worktree', WT]]) {
        sources[label] = sorted.map(name => row(base, name));
        protectedConfig[label] = protectedSet.map(name => row(base, name));
    }
    return { schema: 'theory-template-source-inventory-v1', captured_at: new Date().toISOString(), base_sha: BASE,
        scopes: SCOPES, roots: { root: ROOT, worktree: WT }, sources, protected_config: protectedConfig,
        immutable_paths: sorted.filter(name => name.startsWith('database/') || /^docs\/content\//u.test(name)) };
}
function evidence(name) {
    assert.match(name, /^source-(?:before|after|compare|allowlist)-v[1-9][0-9]*\.json$/u);
    return path.join(PRIVATE, name);
}
function write(name, payload) {
    const filename = evidence(name); fs.mkdirSync(PRIVATE, { recursive: true });
    fs.writeFileSync(filename, JSON.stringify(payload, null, 2) + '\n', { flag: 'wx' });
    return filename;
}
function compare(beforeName, afterName, allowName, resultName) {
    const before = JSON.parse(fs.readFileSync(evidence(beforeName), 'utf8'));
    const after = JSON.parse(fs.readFileSync(evidence(afterName), 'utf8'));
    const allow = JSON.parse(fs.readFileSync(evidence(allowName), 'utf8'));
    for (const payload of [before, after]) {
        assert.equal(payload.schema, 'theory-template-source-inventory-v1'); assert.equal(payload.base_sha, BASE);
        assert.deepEqual(payload.scopes, SCOPES); assert.deepEqual(payload.roots, { root: ROOT, worktree: WT });
    }
    assert.deepEqual(Object.keys(allow).sort(), ['root', 'worktree']);
    const changes = [], unapproved = [], protectedChanges = [], immutableChanges = [], newContractDocuments = [];
    for (const label of ['root', 'worktree']) {
        const allowed = new Set(allow[label].map(valid)); assert.equal(allowed.size, allow[label].length);
        for (const name of allowed) assert.ok(wanted(name));
        for (const kind of ['sources', 'protected_config']) {
            const left = new Map(before[kind][label].map(record => [record.path, record]));
            const right = new Map(after[kind][label].map(record => [record.path, record]));
            for (const name of [...new Set([...left.keys(), ...right.keys()])].sort()) {
                const previous = left.get(name) ?? { path: name, exists: false, bytes: 0, sha256: null };
                const current = right.get(name) ?? { path: name, exists: false, bytes: 0, sha256: null };
                if (previous.exists === current.exists && previous.bytes === current.bytes && previous.sha256 === current.sha256) continue;
                const record = { root: label, path: name, before: previous, after: current, allowed: kind === 'sources' && allowed.has(name) };
                if (kind === 'protected_config') protectedChanges.push(record);
                else {
                    changes.push(record); if (!record.allowed) unapproved.push(record);
                    // The task explicitly asks for this new architectural contract. It is
                    // not a frozen author source; never exempt edits to an existing file.
                    const requestedNewContract = name === 'docs/content/theory-template.md'
                        && !previous.exists && current.exists && record.allowed;
                    if (requestedNewContract) newContractDocuments.push(record);
                    else if (name.startsWith('database/') || name.startsWith('docs/content/')) immutableChanges.push(record);
                }
            }
        }
    }
    const payload = { schema: 'theory-template-source-comparison-v1', compared_at: new Date().toISOString(),
        base_sha: BASE, before_file: beforeName, after_file: afterName, allowlist_file: allowName,
        changes, unapproved, protected_config_changes: protectedChanges, immutable_changes: immutableChanges,
        new_contract_documents: newContractDocuments,
        pass: !unapproved.length && !protectedChanges.length && !immutableChanges.length };
    const filename = write(resultName, payload);
    console.log(JSON.stringify({ file: filename, sha256: hash(fs.readFileSync(filename)), changes: changes.length,
        unapproved: unapproved.length, protected_config_changes: protectedChanges.length, immutable_changes: immutableChanges.length, pass: payload.pass }));
    if (!payload.pass) process.exitCode = 1;
}
if (require.main === module) {
    const [mode, ...args] = process.argv.slice(2);
    if (mode === 'capture') {
        assert.equal(args.length, 1); assert.match(args[0], /^source-(?:before|after)-v[1-9][0-9]*\.json$/u);
        assert.equal(fs.existsSync(evidence(args[0])), false, 'Exclusive fresh inventory');
        const payload = capture(), filename = write(args[0], payload);
        console.log(JSON.stringify({ file: filename, sha256: hash(fs.readFileSync(filename)), paths: payload.sources.root.length,
            root_files: payload.sources.root.filter(row => row.exists).length, worktree_files: payload.sources.worktree.filter(row => row.exists).length,
            immutable_paths: payload.immutable_paths.length, protected_config_paths: payload.protected_config.root.length,
            source_writes: false, db_access: false, http_access: false }));
    } else if (mode === 'compare') { assert.equal(args.length, 4); compare(...args); }
    else throw new Error('Use capture <source-before/after-vN.json> or compare <before> <after> <allowlist> <result>');
}
module.exports = { ROOT, WT, PRIVATE, BASE, capture, compare };
