'use strict';

// Stages only an isolated Apache runtime. Immutable releases are still built,
// verified and sealed by the PERF-29 tools, not by this module.
const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const { createHash, randomUUID } = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const APACHE = 'C:/Program Files/xampp/apache';
const APACHE_PIN = '7bd994ae100f079c7c2e36414ec40c059b38d440f0c48e89c0946896c8b0aeea';
const PS = 'C:/Program Files/PowerShell/7/pwsh.exe';
const ROOT = /^C:\\Gramlyze\.PERF30\.[a-f0-9]{16,64}$/;
const ROLE = /^S-1-5-21-(?:[1-9][0-9]{0,9}-){3}[1-9][0-9]{0,9}$/;
const MODULES = Object.freeze(['alias', 'authn_core', 'authz_core', 'authz_host', 'env', 'headers',
    'log_config', 'mime', 'proxy', 'proxy_http', 'rewrite', 'setenvif']);
const sha = bytes => createHash('sha256').update(bytes).digest('hex');
const px = file => file.replaceAll('\\', '/');
const q = file => '"' + px(file) + '"';

function exactRole(sid) {
    assert.match(sid, ROLE);
    assert(sid.split('-').slice(4).every(v => Number(v) <= 0xffffffff));
    return sid;
}

function exclusiveRoot(value) {
    assert.equal(process.platform, 'win32');
    const root = path.resolve(value);
    assert.match(root, ROOT, 'expected unique direct private PERF-30 path on C:');
    assert.equal(fs.realpathSync.native('C:\\'), 'C:\\');
    fs.mkdirSync(root);
    assert.equal(fs.realpathSync.native(root), root);
    return root;
}

function physical(file) {
    const stat = fs.lstatSync(file);
    assert(stat.isFile() && !stat.isSymbolicLink() && stat.nlink === 1);
    assert.equal(px(fs.realpathSync.native(file)).toLowerCase(), px(path.resolve(file)).toLowerCase());
    return fs.readFileSync(file);
}

// Read the PE import directory (including delayed imports) strictly from the
// pinned installed binaries. Only local DLL siblings are eligible copies;
// Windows libraries are left to the Windows loader (no ACL changes there).
function imports(bytes) {
    const end = bytes.length;
    const u16 = n => bytes.readUInt16LE(n);
    const u32 = n => bytes.readUInt32LE(n);
    assert.equal(u16(0), 0x5a4d);
    const header = u32(0x3c);
    assert.equal(u32(header), 0x4550);
    const opt = header + 24, table = opt + u16(header + 20), pe64 = u16(opt) === 0x20b;
    assert(pe64 || u16(opt) === 0x10b);
    const dirs = opt + (pe64 ? 112 : 96);
    const sections = Array.from({length:u16(header + 6)}, (_, i) => {
        const s = table + i * 40;
        return {virtual:u32(s + 12), bytes:u32(s + 16), raw:u32(s + 20)};
    });
    function offset(rva) {
        const s = sections.find(s => rva >= s.virtual && rva < s.virtual + s.bytes);
        assert(s, 'invalid PE RVA');
        return s.raw + rva - s.virtual;
    }
    const result = new Set();
    for (const [idx, stride] of [[1,20], [13,32]]) {
        const rva = u32(dirs + idx * 8), size = u32(dirs + idx * 8 + 4);
        if (!rva || !size) continue;
        const start = offset(rva);
        assert(start + size <= end);
        for (let p = start; p + stride <= start + size; p += stride) {
            const ptr = u32(p + (idx === 1 ? 12 : 4));
            if (!ptr) break;
            if (idx === 13) assert(u32(p) & 1, 'unexpected VA-based delayed import');
            const s = offset(ptr), e = bytes.indexOf(0, s);
            assert(e > s && e - s <= 200);
            const name = bytes.toString('ascii', s, e).toLowerCase();
            assert.match(name, /^[a-z\d_.-]+\.dll$/);
            result.add(name);
        }
    }
    return result;
}

function binaries(root, modules) {
    assert(Array.isArray(modules) && modules.length > 0 && new Set(modules).size === modules.length);
    assert(modules.every(n => MODULES.includes(n)));
    const parts = ['bin/httpd.exe', 'conf/mime.types', ...modules.map(n => 'modules/mod_' + n + '.so')];
    const pins = {}, sys = new Set();
    for (let j = 0; j < parts.length; ++j) {
        const rel = parts[j], source = path.join(APACHE, rel);
        const bytes = physical(source);
        const dest = path.join(root, 'server', rel);
        fs.writeFileSync(dest, bytes, {flag:'wx'});
        pins[rel] = sha(bytes);
        if (!/\.(?:exe|dll|so)$/.test(rel)) continue;
        for (const name of imports(bytes)) {
            const dependency = 'bin/' + name;
            if (fs.existsSync(path.join(APACHE, dependency))) {
                if (!parts.includes(dependency)) parts.push(dependency);
            } else sys.add(name);
        }
    }
    assert.equal(pins['bin/httpd.exe'], APACHE_PIN);
    return {pins, systemImports:[...sys].sort()};
}

function stageRuntime({runtimeRoot = 'C:\\Gramlyze.PERF30.' + randomUUID().replaceAll('-', ''),
    workerRoleSid, modules, configure} = {}) {
    exactRole(workerRoleSid);
    assert.equal(typeof configure, 'function');
    const root = exclusiveRoot(runtimeRoot);
    for (const child of ['server/bin', 'server/modules', 'server/conf', 'www', 'run/tmp', 'control', 'negative'])
        fs.mkdirSync(path.join(root, child), {recursive:true});
    const dirs = Object.fromEntries(['server','www','run','control','negative'].map(dir => [dir, path.join(root, dir)]));
    const binary = binaries(root, modules);
    const configPath = path.join(root, 'server.conf');
    const includePath = path.join(root, 'immutable.conf');
    const {config, immutable = null} = configure({root, ...dirs, configPath, includePath});
    assert.equal(typeof config, 'string');
    fs.writeFileSync(configPath, config, {flag:'wx'});
    if (immutable !== null) fs.writeFileSync(includePath, immutable, {flag:'wx'});
    for (const [name, value] of Object.entries({'body.css':'/* PERF-30 identity fixture */\n',
        'body.css.gz':'only a sacrificial sidecar fixture\n','donor.dat':'link attempt fixture\n'}))
        fs.writeFileSync(path.join(dirs.negative, name), value, {flag:'wx'});
    // A confidentiality canary is not a real credential or a live-site file.
    fs.writeFileSync(path.join(dirs.control, 'publisher-only.txt'), 'PERF-30 synthetic publisher fixture\n', {flag:'wx'});
    const receipt = {root, ...dirs, configPath, configSha256:sha(config), includePath,
        immutableSha256:immutable === null ? null : sha(immutable), workerRoleSid, ...binary};
    fs.writeFileSync(path.join(dirs.control, 'private-runtime.json'), JSON.stringify(receipt, null, 2) + '\n', {flag:'wx'});
    const applied = spawnSync(PS, ['-NoLogo','-NoProfile','-NonInteractive','-File',
        path.join(__dirname, 'restricted-runtime-acl.ps1'), '-RuntimeRoot', root, '-WorkerRoleSid', workerRoleSid],
    {windowsHide:true, encoding:'utf8', timeout:60000});
    assert.equal(applied.status, 0, String(applied.error || applied.stderr || 'private runtime DACL setup failed'));
    const acl = JSON.parse(applied.stdout);
    assert.equal(acl.root, root);
    assert.equal(acl.roleSid, workerRoleSid);
    return {...receipt, acl};
}

module.exports = {stageRuntime, imports, exactRole, MODULES, APACHE_PIN, PS, px, q, sha};
