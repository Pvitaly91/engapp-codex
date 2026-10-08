'use strict';
// Mechanical adaptation of the existing guarded source-sync script, not a shell writer.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict');
const WT = 'C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc';
let code = fs.readFileSync(path.join(WT, 'tools/diagnostics/sync-m43-working-sources.php'), 'utf8');
code = code.replaceAll('M43AuthoredTenseUsagePackage', 'M44AuthoredFutureFormsPackage').replaceAll('M43', 'M44').replaceAll('m43', 'm44');
code = code.replace("$baseline['schema'] !== 'm44-source-inventory-v1'", "$baseline['schema'] !== 'm43-source-inventory-v1'");
code = code.replace('3f122ab8d26923d53f4ede05fe094435797f7439950733fd74a0eae5ea9eacfd', 'ad86a11c5c0fda76ad054f23f7566cd1e508cae0324a94ae736750d2692eadb5');
const original = "if ($old !== null) { throw new RuntimeException('M44 first sync refuses any pre-existing new M44 source: '.$path); }";
assert.ok(code.includes(original));
code = code.replace(original, "if ($old !== null && (!str_starts_with($path, 'tools/diagnostics/') || $old !== $new)) { throw new RuntimeException('M44 first sync refuses a pre-existing new application source or differing diagnostic: '.$path); }");
const inventoryCheck = "if ($old !== null) {\n        $row = $initial[$path] ?? null;";
assert.ok(code.includes(inventoryCheck));
code = code.replace(inventoryCheck, "if ($old !== null && !(str_starts_with($path, 'tools/diagnostics/') && str_contains(strtolower($path), 'm44') && $old === $new)) {\n        $row = $initial[$path] ?? null;");
const file = path.join(WT, 'tools/diagnostics/sync-m44-working-sources.php');
assert.equal(fs.existsSync(file), false);
fs.writeFileSync(file, code.replace(/\r\n/gu, '\n').trimEnd() + '\n', {flag: 'wx'});
console.log(JSON.stringify({created: file, from: 'versioned guarded M43 sync', ownDiagnosticRule: 'pre-existing task diagnostics must be byte-identical; application sources never replaced under this exception'}));
