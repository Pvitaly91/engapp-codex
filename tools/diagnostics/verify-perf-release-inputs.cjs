#!/usr/bin/env node
'use strict';

// Verify the files a clean checkout puts on disk, not a transformed text read.
const { createHash } = require('node:crypto');
const { lstatSync, readFileSync, realpathSync } = require('node:fs');
const { join, resolve } = require('node:path');

const root = realpathSync(resolve(__dirname, '..', '..'));
const source = join(root, 'tests', 'Fixtures', 'perf-release-sha256.v1.json');
const manifest = JSON.parse(readFileSync(source, 'utf8'));
if (manifest.schema !== 1 || manifest.algorithm !== 'sha256' || !manifest.files ||
    Array.isArray(manifest.files) || Object.keys(manifest.files).length === 0) {
  throw new Error('Unrecognized or empty PERF release manifest');
}
let count = 0;
let mismatches = 0;
for (const [relative, expected] of Object.entries(manifest.files)) {
  if (!/^[A-Za-z0-9._ /()+'!-]+$/.test(relative) || relative.startsWith('/') ||
      relative.split('/').some(segment => segment === '..' || segment === '.' || segment === '') ||
      !/^[a-f0-9]{64}$/.test(expected)) {
    throw new Error('Invalid PERF release manifest entry');
  }
  const filename = join(root, relative);
  try {
    if (!lstatSync(filename).isFile() || realpathSync(filename) !== filename) {
      throw new Error('must be a regular file in the checkout');
    }
    const actual = createHash('sha256').update(readFileSync(filename)).digest('hex');
    if (actual !== expected) throw new Error('SHA-256 does not match');
    count++;
  } catch (error) {
    mismatches++;
    console.error(`${relative}: ${error.code || error.message}`);
  }
}
console.log(`PERF release inputs: ${count} matched; ${mismatches} failed.`);
if (mismatches) process.exitCode = 1;
