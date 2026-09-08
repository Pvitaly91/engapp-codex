// Reproduce the build from source-only inputs: no .env, vendor, DB or runtime data.
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const {spawnSync} = require('node:child_process');
const repo = path.resolve(__dirname, '../..');
const out = path.join(repo, 'storage/app/seo-m3-local');
const label = process.argv[2] || 'clean-build';
if (!/^[a-z0-9_-]+$/i.test(label)) throw new Error('Supply a simple evidence label');
const evidence = path.join(out, label + '.json');
if (fs.existsSync(evidence)) throw new Error('Evidence exists; supply a new label');
const clean = path.join(out, 'clean-build-' + crypto.randomUUID());
fs.mkdirSync(clean, {recursive: true});
const inputs = ['package.json', 'package-lock.json', 'vite.config.js', 'postcss.config.js',
    'tailwind.config.js', 'tailwind.public.config.js', 'tools/build', 'resources',
    'app/Support', 'database/seeders/Page_V3', 'database/seeders/V3'];
for (const input of inputs) {
    const target = path.join(clean, input);
    fs.mkdirSync(path.dirname(target), {recursive: true});
    fs.cpSync(path.join(repo, input), target, {recursive: true});
}
const record = {started: new Date().toISOString(), node: process.version, inputs, sourceOnly: true, commands: []};
for (const args of [['ci', '--no-audit', '--no-fund'], ['run', 'build']]) {
    // Fixed npm commands, no user-controlled shell input. Hide Windows helpers.
    const run = spawnSync(process.platform === 'win32' ? 'npm.cmd' : 'npm', args,
        {cwd: clean, encoding: 'utf8', shell: process.platform === 'win32', windowsHide: true});
    record.commands.push({args, exit: run.status, stdout: run.stdout, stderr: run.stderr});
    console.log(run.stdout || ''); console.log(run.stderr || '');
    if (run.status !== 0) {
        fs.writeFileSync(evidence, JSON.stringify(record, null, 2));
        process.exit(run.status || 1);
    }
}
const manifest = JSON.parse(fs.readFileSync(path.join(clean, 'public/build/manifest.json')));
record.assets = Object.fromEntries(Object.entries(manifest).map(([entry, value]) => {
    const data = fs.readFileSync(path.join(clean, 'public/build', value.file));
    return [entry, {file: value.file, bytes: data.length, sha256: crypto.createHash('sha256').update(data).digest('hex')}];
}));
record.finished = new Date().toISOString();
fs.writeFileSync(evidence, JSON.stringify(record, null, 2));
console.log(JSON.stringify(record.assets, null, 2));
