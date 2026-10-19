'use strict';
// Read-only effective .loc vhost, DNS and local processes; no HTTP, routes or server changes.
const fs = require('node:fs'), path = require('node:path'), crypto = require('node:crypto');
const assert = require('node:assert/strict');
const {execFileSync} = require('node:child_process');
const PRIVATE = 'D:/DEV/htdocs/gramlyze.loc/storage/app/theory-template-local';
const [label] = process.argv.slice(2);
assert.match(label, /^(?:before|after)-v[1-9][0-9]*$/u); assert.equal(process.platform, 'win32');
const sha = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const files = ['C:/Program Files/xampp/apache/conf/httpd.conf', 'C:/Program Files/xampp/apache/conf/extra/httpd-vhosts.conf',
    'C:/Program Files/xampp/apache/conf/extra/gramlyze-fastcgi.conf', 'D:/DEV/htdocs/gramlyze.loc/public/index.php'];
const hashes = Object.fromEntries(files.map(file => [file, sha(fs.readFileSync(file))]));
const vhosts = fs.readFileSync(files[1], 'utf8');
const matches = [...vhosts.matchAll(/<VirtualHost\s+\*:80>([\s\S]*?)<\/VirtualHost>/gu)]
    .map(match => match[1]).filter(body => /^\s*ServerName\s+gramlyze\.loc\s*$/mu.test(body));
assert.equal(matches.length, 1, 'One exact local HTTP vhost required');
const documentRoot = matches[0].match(/^\s*DocumentRoot\s+"([^"]+)"/mu)?.[1]?.replaceAll('\\', '/');
assert.equal(documentRoot, 'D:/DEV/htdocs/gramlyze.loc/public');
assert.match(fs.readFileSync(files[0], 'utf8'), /^\s*Include\s+conf\/extra\/httpd-vhosts\.conf\s*$/mu);
let effective = '';
try { effective = execFileSync('C:/Program Files/xampp/apache/bin/httpd.exe', ['-S'], {encoding: 'utf8', timeout: 15000, stdio: ['ignore', 'pipe', 'pipe']}); }
catch (error) { throw new Error('Apache read-only effective vhost check failed: ' + (error.status ?? error.code)); }
assert.match(effective, /port 80 namevhost gramlyze\.loc /u);
const script = '$ErrorActionPreference="Stop"; $ports=@(Get-NetTCPConnection -LocalPort 80,3306 -State Listen | Select-Object LocalAddress,LocalPort,OwningProcess); '
    + '$procs=@(Get-CimInstance Win32_Process -Filter "Name = \'httpd.exe\' OR Name = \'mysqld.exe\'" | Select-Object Name,ProcessId,ExecutablePath); '
    + '$dns=@(Resolve-DnsName gramlyze.loc -Type A | Select-Object Name,IPAddress); '
    + '@{ports=$ports;processes=$procs;dns=$dns}|ConvertTo-Json -Depth 5 -Compress';
const runtime = JSON.parse(execFileSync('powershell.exe', ['-NoProfile', '-NonInteractive', '-Command', script],
    {encoding: 'utf8', timeout: 20000, stdio: ['ignore', 'pipe', 'pipe']}));
assert.ok(runtime.dns.some(row => row.Name === 'gramlyze.loc' && row.IPAddress === '127.0.0.1'));
for (const [number, name] of [[80, 'httpd.exe'], [3306, 'mysqld.exe']]) {
    assert.ok(runtime.ports.some(port => port.LocalPort === number && runtime.processes.some(proc => proc.Name === name && proc.ProcessId === port.OwningProcess)), 'Expected local listening process');
}
const record = {schema: 'theory-template-physical-v1', at: new Date().toISOString(), localHost: 'gramlyze.loc', scheme: 'http', documentRoot,
    vhostSource: files[1], runtime, hashes, effectiveVhostSha256: sha(effective),
    commands: ['httpd.exe -S', 'Get-NetTCPConnection -LocalPort 80,3306 -State Listen', 'Get-CimInstance Win32_Process httpd.exe/mysqld.exe', 'Resolve-DnsName gramlyze.loc -Type A'],
    configWrites: false, routeAdded: false, dbWrites: false};
assert.ok(fs.statSync(PRIVATE).isDirectory() && !fs.lstatSync(PRIVATE).isSymbolicLink());
const file = path.join(PRIVATE, 'physical-target-' + label + '.json');
fs.writeFileSync(file, JSON.stringify(record, null, 2) + '\n', {flag: 'wx'});
console.log(JSON.stringify({pass: true, file, sha256: sha(fs.readFileSync(file)), documentRoot, configWrites: false}));
