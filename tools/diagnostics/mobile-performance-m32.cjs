// Bounded M3.2 orchestration, using the existing M3.1 observer and CLS contract.
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {execFileSync} = require('node:child_process');
const {measureColdNavigation, PAGES} = require('./public-layout-shifts.cjs');
const hash = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const PROBES = ['tools/diagnostics/public-layout-shifts.cjs', 'tools/diagnostics/cls-session-window.cjs',
    'tools/diagnostics/mobile-performance-m32.cjs'];
function schedule() {
    return Array.from({length: 10}, (_, i) => PAGES.slice(0, 2).map(([name, url]) => ({
        pairId: `pair-${i + 1}-${name}`, name, path: url, order: (i + 1) % 2 ? 'AB' : 'BA',
    }))).flat();
}
function plan() {
    return {frozenAt: new Date().toISOString(), pairsPerPage: 10, cache: 'cold',
        viewport: {width: 390, height: 844, mobile: true}, windowMs: 12000, settledMs: 2000, schedule: schedule(),
        reportPlanSha256: hash('docs/reports/seo-m3-2-mobile-performance.md')};
}
async function main() {
    const [mode, label, metadataFile] = process.argv.slice(2);
    assert.ok(['plan', 'current', 'alternate-browser', 'paired', 'finalize'].includes(mode), 'Mode: plan|current|alternate-browser|paired|finalize');
    assert.match(label || '', /^[a-z0-9][a-z0-9_-]*$/i, 'Unique label required');
    const root = path.resolve('storage/app/seo-m3-2-local'); fs.mkdirSync(root, {recursive: true});
    const output = path.join(root, `${label}.json`);
    assert.ok(!fs.existsSync(output), 'Refusing to overwrite evidence');
    if (mode === 'plan') {
        fs.writeFileSync(output, JSON.stringify(plan(), null, 2)); console.log(output); return;
    }
    if (mode === 'finalize') {
        assert.ok(process.argv.includes('--exclusive-window-confirmed'), 'Coordinator must confirm no concurrent heavy work');
        const rawFile = path.resolve(metadataFile || '');
        assert.equal(path.dirname(rawFile), root, 'Raw evidence must belong to local M3.2 directory');
        const report = JSON.parse(fs.readFileSync(rawFile, 'utf8'));
        assert.equal(report.schema, 'gramlyze-isolated-paired-m32-v1');
        const standFolder = path.dirname(report.standManifest);
        assert.equal(path.dirname(standFolder), root, 'Stand proof must belong to local M3.2 directory');
        assert.match(path.basename(standFolder), /^stands-[a-f0-9]{32}$/);
        const proofFile = path.join(standFolder, 'after-serve-safety.json');
        const proof = JSON.parse(fs.readFileSync(proofFile, 'utf8'));
        assert.ok(Date.parse(proof.checkedAt) >= Date.parse(report.finishedAt), 'Proof must follow measurement and server shutdown');
        assert.ok(['A', 'B'].every(v => proof.dataHashes[v] === report.variants[v].dataSha256), 'Frozen data hash mismatch');
        report.safety = {...proof, noParallelWork: true};
        report.finalization = {at: new Date().toISOString(), sourceRawSha256: hash(rawFile),
            safetyProofSha256: hash(proofFile), coordinator: 'exclusive measurement window explicitly confirmed; not a machine-wide activity monitor'};
        // A separate finalized envelope preserves every original row and the
        // original raw file; never relabel preparation hashes as post-run proof.
        fs.writeFileSync(output, JSON.stringify(report, null, 2));
        const result = require('./verify-layout-comparison.cjs').verifyPairedComparison(report);
        const verificationFile = path.join(root, `${label}-verification.json`);
        assert.ok(!fs.existsSync(verificationFile), 'Refusing to overwrite verification evidence');
        fs.writeFileSync(verificationFile, JSON.stringify(result, null, 2));
        console.log(JSON.stringify({pass: result.pass, verifiedRecords: result.verifiedRecords, errors: result.errors, output, verificationFile}));
        if (!result.pass) process.exitCode = 1;
        return;
    }
    const modulePath = process.env.PLAYWRIGHT_MODULE || 'playwright';
    const {chromium} = require(modulePath);
    const browser = await chromium.launch({headless: true, ...(process.env.CHROMIUM_EXECUTABLE ? {executablePath: process.env.CHROMIUM_EXECUTABLE} : {})});
    let playwright;
    try { playwright = require(path.join(path.dirname(require.resolve(modulePath)), 'package.json')).version; } catch { playwright = null; }
    const probeSha256 = Object.fromEntries(PROBES.map(file => [file, hash(file)]));
    const report = {schema: mode === 'paired' ? 'gramlyze-isolated-paired-m32-v1' : 'gramlyze-current-trace-m32-v1',
        mode, label, startedAt: new Date().toISOString(), browser: browser.version(), playwright, node: process.version,
        head: execFileSync('git', ['rev-parse', 'HEAD'], {encoding: 'utf8'}).trim(), probeSha256,
        fontMode: 'natural-network', records: []};
    const browserSession = await browser.newBrowserCDPSession();
    report.browserBuild = await browserSession.send('Browser.getVersion');
    await browserSession.detach();
    const save = () => fs.writeFileSync(output, JSON.stringify(report, null, 2));
    try {
        if (mode === 'paired') {
            assert.ok(metadataFile, 'Paired run requires verified isolated stand metadata');
            const metadata = JSON.parse(fs.readFileSync(metadataFile, 'utf8'));
            report.standManifest = path.resolve(metadataFile);
            assert.ok(metadata.planFile, 'Stand metadata must reference the pre-run frozen plan');
            report.plan = JSON.parse(fs.readFileSync(metadata.planFile, 'utf8'));
            assert.deepEqual(report.plan.schedule, schedule(), 'Plan must have fixed balanced 20 pairs');
            report.variants = metadata.variants; report.safety = metadata.safety;
            for (const v of ['A', 'B']) report.variants[v].probeSha256 = probeSha256;
            save();
            for (const [index, pair] of report.plan.schedule.entries()) for (const variant of pair.order) {
                const row = await measureColdNavigation(browser, {base: report.variants[variant].base,
                    name: pair.name, url: pair.path, pairId: pair.pairId, variant, run: Math.floor(index / 2) + 1});
                report.records.push(row); save();
                console.log(JSON.stringify({pairId: pair.pairId, variant, lcp: row.metric.lcp,
                    cls: row.metric.clsSessionWindow, status: row.metric.status, reasons: row.metric.reasons}));
            }
        } else {
            const cases = PAGES.map(([name, url]) => ({name, url, viewport: 'mobile'}));
            if (mode === 'current') cases.push({name: PAGES[0][0], url: PAGES[0][1], viewport: 'desktop'});
            save();
            for (const item of cases) {
                const row = await measureColdNavigation(browser, {...item, run: 1});
                report.records.push(row); save();
                console.log(JSON.stringify({name: row.name, viewport: row.viewport, ...row.metric, error: row.error}));
            }
        }
    } finally { await browser.close(); report.finishedAt = new Date().toISOString(); save(); }
    console.log(`Evidence: ${output}`);
    if (report.records.some(r => r.metric.status !== 'complete')) process.exitCode = 1;
}
module.exports = {schedule, plan};
if (require.main === module) main().catch(error => { console.error(error.message); process.exitCode = 1; });
