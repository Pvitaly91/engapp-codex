#!/usr/bin/env node
'use strict';

// Read-only audit of immutable application inputs and optionally a built RC.
// This does not load Laravel, .env, server caches, database or session state.
const { createHash } = require('node:crypto');
const { lstatSync, readFileSync, realpathSync } = require('node:fs');
const { isAbsolute, join, resolve, sep } = require('node:path');

const BASE = '05573a4734361f6946bf6514422cd062fe8d5d43';
const HISTORICAL = 'tests/Fixtures/perf-release-sha256.v1.json';
const HISTORICAL_SHA = 'bda6fd1aaa5d45396629ab7129de6228b4e3c19c54210d9ed644c37b85e446e0';
const ENGINE = 'app/Support/TheoryNativeContentRelease.php';
const EARLIER_ENGINE = '84ce0e11b6007cfc5777c0b663e998d815cb255970704c1c7fbd5f424b03fcd4';
const CURRENT_ENGINE = 'a88cdf061fa79279fb378c8f2765a6866fa40513abae8ef9646e8701d2833e53';
const PINNED = {
  [ENGINE]: CURRENT_ENGINE,
  'app/Support/ValidatedNativeContentSources.php': '042be99b914e6705f30af5ef5b7b5bf6c79259ba841dbcd2f7dbfd1e9364df2c',
  'resources/views/test-modes/step-easy.blade.php': 'c0e85097db19f5fe40ffa8aa5ed7163d46a28e261b324c88e143e8d0d9943a36',
  'app/Http/Controllers/WordSearchController.php': 'b179ef3077fb42e58050205bb01cd51cdbf8a5c0fc86f4db7487e8c5627f6e7d',
  'app/Http/Controllers/TestJsV2Controller.php': '6c7f4cc417b4b153169df418baaa5dea99f9ee8698beb113e7a96d4c23341e3f',
  'app/Http/Controllers/NewDesignTestController.php': '818ec9fc4fee90843967f3c0b18cb77be3f41b52c4e729e904cc174d304b938c',
  'app/Services/SavedTestResolver.php': 'a463f62e244436e64c3fc72285b2e05ec522357eb70ea30fdb8cafc516313643',
  'app/Providers/AppServiceProvider.php': '6149899d233b382c9c4c61ed0be93d759d6bab1bee7796f59c57975e86037d87',
  'routes/web.php': 'c049e564ed611619645b338848b875b84d2d31995ed8bc40e4a83f0ec8e8298c',
  'resources/css/app.css': '3722d677e02b542e5a3314a14f7dacf13e4417182b304a2297800843b781e80f',
  'resources/js/app.js': 'cf6dab196c721f6911ff99cc4f910897f978599a2d289971a316a5fc33091f94',
  'resources/css/catalog-public.css': '02a5e7d154ac4bde7755601b04c4a81a666e2c975c41effc8938d574d935c8c6',
  'resources/js/catalog-public.js': 'e62bea3ed0fa66fe0d28e6c38fb6ca376043c815efe7805d981e5db901f324d7',
  'composer.json': '8892147771015310b1cd7170e8aac6d5846d49f70e6d081bb3c0ccc7d0b13b27',
  'composer.lock': '6897cff07cbf36201f8eb7dc9f987905f698f770c1cbcc26a3f22da89cf59595',
  'package.json': 'fed97ed919f76319a1fbbd94e1046a43d3b2fb5c2844ffd581d4ae19c6b760ca',
  'package-lock.json': '1446a49eb26e56b26af3cf13ca50a77262cac9bfbfdd5ab3f6db50364683fd0c',
  'vite.config.js': 'b8c03c1c23d0ce702d3791f2d1a2d25b34db95e843725032177c63dcd0dd50f9',
  'postcss.config.js': '76de3730b40996f579367a0c7f8a4f664e20e9a540b85c3b9a623213db45408a',
  'tailwind.config.js': 'f5da65ea73f2d61bbcf952f8db7a661882790d526aa742e02c6ac9d92f475cab',
};
const VITE = [
  'resources/css/app.css', 'resources/js/app.js',
  'resources/css/catalog-public.css', 'resources/js/catalog-public.js',
];
const sha = bytes => createHash('sha256').update(bytes).digest('hex');

function safeRead(root, relative) {
  if (!/^[A-Za-z0-9._ /()+'!-]+$/.test(relative)
    || relative.split('/').some(part => !part || part === '.' || part === '..')) {
    throw new Error('unsafe relative audit path');
  }
  const filename = join(root, ...relative.split('/'));
  const path = realpathSync.native(filename);
  if (!path.startsWith(root.endsWith(sep) ? root : root + sep) || path !== filename) {
    throw new Error('not a physical file path in the release');
  }
  const stat = lstatSync(filename);
  if (!stat.isFile() || stat.nlink !== 1 || stat.size > 64 * 1024 * 1024) {
    throw new Error('not a regular, bounded, singly-linked file');
  }
  return readFileSync(filename);
}

function auditBytes(root, reference) {
  const historicalBytes = safeRead(reference, HISTORICAL);
  if (sha(historicalBytes) !== HISTORICAL_SHA) throw new Error('trusted historical manifest SHA mismatch');
  const history = JSON.parse(historicalBytes);
  if (history.schema !== 1 || history.algorithm !== 'sha256'
    || Object.keys(history.files).length !== 523 || history.files[ENGINE] !== EARLIER_ENGINE) {
    throw new Error('unexpected historical manifest shape or engine pin');
  }
  for (const [name, hash] of Object.entries(PINNED)) {
    if (name !== ENGINE && Object.hasOwn(history.files, name) && history.files[name] !== hash) {
      throw new Error('unapproved historical pin override');
    }
  }
  const expected = { ...history.files, ...PINNED, [HISTORICAL]: HISTORICAL_SHA };
  const mismatches = [];
  for (const [relative, wanted] of Object.entries(expected)) {
    try {
      const actual = sha(safeRead(root, relative));
      if (actual !== wanted) mismatches.push({ path: relative, expected: wanted, actual });
    } catch (error) {
      mismatches.push({ path: relative, status: error.code ?? error.message });
    }
  }
  return {
    historicalPins: Object.keys(history.files).length, approvedHistoricalOverlay: ENGINE,
    exact: Object.keys(expected).length - mismatches.length, expected: Object.keys(expected).length,
    mismatches,
  };
}

function auditVite(root) {
  try {
    lstatSync(join(root, 'public', 'hot'));
    throw new Error('public/hot development marker must not exist');
  } catch (error) {
    if (error.code !== 'ENOENT') throw error;
  }
  const bytes = safeRead(root, 'public/build/manifest.json');
  const manifest = JSON.parse(bytes);
  if (!manifest || typeof manifest !== 'object' || Array.isArray(manifest)) {
    throw new Error('invalid Vite manifest');
  }
  for (const key of VITE) {
    if (!Object.hasOwn(manifest, key) || manifest[key].isEntry !== true) {
      throw new Error('missing Vite entry: ' + key);
    }
    const extension = key.endsWith('.css') ? 'css' : 'js';
    if (typeof manifest[key].file !== 'string'
      || !new RegExp('^assets/[A-Za-z0-9_.-]+-[A-Za-z0-9_-]{8,}\\.' + extension + '$').test(manifest[key].file)) {
      throw new Error('non-fingerprinted Vite entry: ' + key);
    }
  }

  const files = new Map();
  for (const row of Object.values(manifest)) {
    if (!row || typeof row !== 'object' || typeof row.file !== 'string'
      || [row.css, row.assets, row.imports, row.dynamicImports].some(value => value !== undefined && !Array.isArray(value))) {
      throw new Error('invalid Vite manifest record');
    }
    for (const name of [...(row.imports ?? []), ...(row.dynamicImports ?? [])]) {
      if (typeof name !== 'string' || !Object.hasOwn(manifest, name)) throw new Error('unresolved Vite import');
    }
    for (const name of [row.file, ...(row.css ?? []), ...(row.assets ?? [])]) {
      if (typeof name !== 'string' || !/^assets\/[A-Za-z0-9_.-]+$/.test(name) || name.endsWith('.map')) {
        throw new Error('unexpected Vite output path');
      }
      if (!files.has(name)) {
        const asset = safeRead(root, 'public/build/' + name);
        if (asset.length === 0) throw new Error('empty Vite output');
        files.set(name, { bytes: asset.length, sha256: sha(asset) });
      }
    }
  }
  return { manifestSha256: sha(bytes), entries: VITE.map(entry => ({ entry, file: manifest[entry].file })),
    files: Object.fromEntries([...files.entries()].sort(([a], [b]) => a.localeCompare(b, 'en'))) };
}

function auditVendor(root, mode) {
  const lock = JSON.parse(safeRead(root, 'composer.lock'));
  const installed = JSON.parse(safeRead(root, 'vendor/composer/installed.json'));
  const includesDev = mode === 'dev';
  const expected = [...lock.packages, ...(includesDev ? lock['packages-dev'] : [])];
  if (!Array.isArray(installed.packages) || installed.dev !== includesDev) {
    throw new Error('installed Composer metadata is not marked ' + mode);
  }
  const byName = new Map(installed.packages.map(pkg => [pkg.name, pkg]));
  if (installed.packages.length !== expected.length || byName.size !== expected.length) {
    throw new Error('installed Composer package set differs from lock');
  }
  for (const pkg of expected) {
    const actual = byName.get(pkg.name);
    if (!actual || actual.version !== pkg.version
      || (actual.source?.reference ?? null) !== (pkg.source?.reference ?? null)
      || (actual.dist?.reference ?? null) !== (pkg.dist?.reference ?? null)) {
      throw new Error('Composer version or source reference differs from lock: ' + pkg.name);
    }
  }
  return { mode, packages: expected.length, phpPlatform: lock.platform.php };
}

function main(args) {
  let root = null, build = false, vendor = null;
  for (let i = 0; i < args.length; i++) {
    if (args[i] === '--root' && root === null && args[i + 1] && isAbsolute(args[i + 1])) root = args[++i];
    else if (args[i] === '--build' && !build) build = true;
    else if (args[i] === '--vendor' && vendor === null && ['production', 'dev'].includes(args[i + 1])) vendor = args[++i];
    else throw new Error('usage: ubuntu-app-preflight.cjs --root /absolute/release [--build] [--vendor production|dev]');
  }
  if (root === null) throw new Error('a release root is required (--root /absolute/release)');
  root = realpathSync.native(resolve(root));
  if (!lstatSync(root).isDirectory()) throw new Error('release root is not a directory');
  // Run the helper from the trusted PERF-31 checkout, not the copy being audited.
  const reference = realpathSync.native(resolve(__dirname, '..', '..'));
  const source = auditBytes(root, reference);
  const result = { baseGit: BASE, scope: 'file-byte and build metadata audit only', source };
  if (source.mismatches.length === 0) {
    if (build) {
      try { result.vite = auditVite(root); }
      catch (error) { result.vite = { error: error.message }; }
    }
    if (vendor) {
      try { result.composer = auditVendor(root, vendor); }
      catch (error) { result.composer = { error: error.message }; }
    }
  }
  console.log(JSON.stringify(result, null, 2));
  if (source.mismatches.length !== 0 || result.vite?.error || result.composer?.error) process.exitCode = 1;
}

try { main(process.argv.slice(2)); }
catch (error) { console.error(error.message); process.exitCode = 1; }
