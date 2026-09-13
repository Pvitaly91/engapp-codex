#!/usr/bin/env python3
"""Privately test actual XAMPP config with pinned DLLs and a local certificate.

Only the private Apache copy is started. Config paths/ports/logs are rebased;
Gramlyze's public path is rebased to a tiny PHP fixture so no application/DB is
executed. The real shared and NTS PHP runtime/ini are read-only inputs.
Candidate certificate/key remain private for a separately approved rotation.
This is a pre-rotation prerequisite: the original weak certificate is required
for the negative startup case; it is not a post-installation health check.
"""

import argparse
import hashlib
from http.client import HTTPConnection, HTTPSConnection
import json
from pathlib import Path
import re
import runpy
import shutil
import ssl
import subprocess
import time
import uuid
import zipfile


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--archive", type=Path, required=True)
    args = parser.parse_args()
    helpers = runpy.run_path(str(Path(__file__).with_name("test-isolated-openssl.py")))
    ensure, digest, safe = helpers["ensure"], helpers["digest"], helpers["no_reparse"]
    workspace = Path(__file__).resolve().parents[3]
    xampp = safe(Path("C:/Program Files/xampp"))
    private = safe(workspace / "storage/app/seo-m9-4-startup-local")
    run = private / (time.strftime("full-config-%Y%m%d-%H%M%S-") + uuid.uuid4().hex[:8])
    run.mkdir()
    ensure(digest(args.archive) == helpers["ARCHIVE_SHA"], "archive-hash-mismatch")
    candidates = dict(helpers["DLL_HASHES"], **{"libssh2.dll": helpers["LIBSSH2_SHA"]})
    result = {"schema": "gramlyze-full-config-startup-v1", "directory": str(run),
              "success": False, "candidate_hashes": candidates,
              "scope": "actual config clone; PHP runtime fixtures, no application or database execution"}
    process = None
    protected_paths = [xampp / "php/php.ini", xampp / "php-8.5.10-nts-gramlyze/php.ini"]
    protected_paths += list((xampp / "apache/conf").rglob("*.conf"))
    protected_paths += [xampp / "apache/conf/ssl.crt/server.crt", xampp / "apache/conf/ssl.key/server.key"]
    protected = {str(path): digest(path) for path in protected_paths}
    try:
        for folder in ("bin", "modules", "conf"):
            shutil.copytree(xampp / "apache" / folder, run / folder)
        for folder in ("logs", "shared-fixture", "gramlyze-fixture", "candidate"):
            (run / folder).mkdir()
        with zipfile.ZipFile(args.archive) as archive:
            for name, expected in candidates.items():
                data = archive.read(name)
                ensure(hashlib.sha256(data).hexdigest() == expected, "candidate-hash-mismatch:" + name)
                (run / "bin" / name).write_bytes(data)
        http, https = helpers["pick_ports"]()
        result["ports"] = {"http": http, "https": https}
        changes = []
        original_apache = (xampp / "apache").as_posix()
        gramlyze_public = (workspace / "public").as_posix()
        for path in (run / "conf").rglob("*.conf"):
            before = path.read_text(encoding="utf-8", errors="strict")
            after = before.replace(original_apache, run.as_posix()).replace(
                original_apache.replace("/", "\\"), str(run))
            after = after.replace(gramlyze_public, (run / "gramlyze-fixture").as_posix()).replace(
                gramlyze_public.replace("/", "\\"), str(run / "gramlyze-fixture"))
            after = re.sub(r"(?m)^(\s*Listen\s+)80\s*$", rf"\g<1>127.0.0.1:{http}", after)
            after = re.sub(r"(?m)^(\s*Listen\s+)443\s*$", rf"\g<1>127.0.0.1:{https}", after)
            after = re.sub(r"(?m)^(\s*<VirtualHost\s+)(?:\*|_default_):80(>)", rf"\g<1>127.0.0.1:{http}\2", after)
            after = re.sub(r"(?m)^(\s*<VirtualHost\s+)(?:\*|_default_):443(>)", rf"\g<1>127.0.0.1:{https}\2", after)
            after = re.sub(r"(?m)^(\s*Server(?:Name|Alias)\s+[^\r\n]*?):80(?=\s|$)", rf"\1:{http}", after)
            after = re.sub(r"(?m)^(\s*Server(?:Name|Alias)\s+[^\r\n]*?):443(?=\s|$)", rf"\1:{https}", after)
            if after != before:
                changes.append(path.relative_to(run).as_posix())
                path.write_text(after, encoding="utf-8")
        result["rebased_config_files"] = changes
        cert = run / "candidate/server.crt"
        key = run / "candidate/server.key"
        cert_config = run / "candidate/local-cert.cnf"
        cert_config.write_text("""[req]
distinguished_name = subject
x509_extensions = extensions
prompt = no
[subject]
CN = localhost
O = Gramlyze local development
[extensions]
basicConstraints = critical,CA:FALSE
keyUsage = critical,digitalSignature,keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @names
[names]
DNS.1 = localhost
DNS.2 = gramlyze.loc
DNS.3 = www.gramlyze.loc
IP.1 = 127.0.0.1
IP.2 = ::1
""", encoding="ascii")
        flags = subprocess.CREATE_DEFAULT_ERROR_MODE
        generation = subprocess.run([str(run / "bin/openssl.exe"), "req", "-x509", "-newkey", "rsa:3072", "-sha256",
                                     "-nodes", "-days", "365", "-config", str(cert_config), "-out", str(cert),
                                     "-keyout", str(key)], cwd=xampp, creationflags=flags, timeout=40)
        ensure(generation.returncode == 0, "certificate-generation-failed")
        result["certificate"] = {"path": str(cert), "key_path": str(key), "sha256": digest(cert),
                                 "key_sha256": digest(key), "algorithm": "RSA 3072 / SHA256", "days": 365,
                                 "san": ["localhost", "gramlyze.loc", "www.gramlyze.loc", "127.0.0.1", "::1"],
                                 "trust_store_changed": False}
        command = [str(run / "bin/httpd.exe"), "-d", str(run), "-f", str(run / "conf/httpd.conf")]
        syntax = subprocess.run([*command, "-t"], cwd=xampp, creationflags=flags, timeout=30)
        result["original_cert_syntax_exit"] = syntax.returncode
        ensure(syntax.returncode == 0, "full-original-config-syntax-failed")
        process = subprocess.Popen(command, cwd=xampp, creationflags=flags)
        result["old_certificate_parent_pid"] = process.pid
        try:
            result["old_certificate_start_exit"] = process.wait(timeout=25)
        except subprocess.TimeoutExpired:
            raise RuntimeError("original-certificate-unexpectedly-started")
        ensure(process.returncode != 0, "old-certificate-unexpected-success")
        old_logs = []
        for path in (run / "logs").glob("*"):
            if path.is_file():
                for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
                    if "ee key too small" in line or "AH02562" in line or "AH00016" in line:
                        old_logs.append({"log": path.name, "line": line})
        result["old_certificate_failure"] = old_logs
        ensure(any("ee key too small" in item["line"] for item in old_logs), "expected-old-certificate-failure-not-proven")
        # Replace certificate files in the private clone only. Actual paths and
        # configurations under XAMPP remain protected read-only inputs.
        shutil.copy2(cert, run / "conf/ssl.crt/server.crt")
        shutil.copy2(key, run / "conf/ssl.key/server.key")
        log_offsets = {str(path): path.stat().st_size for path in (run / "logs").glob("*") if path.is_file()}
        probe = """<?php
header('Content-Type: application/json');
echo json_encode(['pid'=>getmypid(),'sapi'=>PHP_SAPI,'version'=>PHP_VERSION,'ini'=>php_ini_loaded_file(),
 'openssl'=>OPENSSL_VERSION_TEXT,'curl'=>extension_loaded('curl'),'curl_ssl'=>curl_version()['ssl_version'],
 'tls'=>$_SERVER['SSL_PROTOCOL']??null], JSON_THROW_ON_ERROR);
"""
        for folder in ("shared-fixture", "gramlyze-fixture"):
            (run / folder / "index.php").write_text(probe, encoding="utf-8")
        alias = "/startup-isolation-" + uuid.uuid4().hex[:12]
        with (run / "conf/httpd.conf").open("a", encoding="utf-8") as config:
            config.write(f'''\n# Private integration-test shared PHP fixture only.
Alias {alias} "{(run / 'shared-fixture').as_posix()}"
<Directory "{(run / 'shared-fixture').as_posix()}">
    Require ip 127.0.0.1 ::1
    AllowOverride None
</Directory>
''')
        syntax = subprocess.run([*command, "-t"], cwd=xampp, creationflags=flags, timeout=30)
        result["candidate_syntax_exit"] = syntax.returncode
        ensure(syntax.returncode == 0, "candidate-syntax-failed")
        process = subprocess.Popen(command, cwd=xampp, creationflags=flags)
        result["candidate_parent_pid"] = process.pid

        def request(host, port, path, version=None):
            if version:
                context = ssl.create_default_context(cafile=str(cert))
                context.minimum_version = context.maximum_version = version
                connection = HTTPSConnection("127.0.0.1", port, context=context, timeout=15)
            else:
                connection = HTTPConnection("127.0.0.1", port, timeout=15)
            connection.request("GET", path, headers={"Host": f"{host}:{port}"})
            response = connection.getresponse()
            data = response.read()
            connection.close()
            ensure(response.status == 200, f"probe-http-status:{host}:{response.status}")
            return json.loads(data)

        ready = None
        for _ in range(100):
            ensure(process.poll() is None, "candidate-apache-exited")
            try:
                ready = request("localhost", http, alias + "/index.php")
                break
            except OSError:
                time.sleep(0.2)
        ensure(ready is not None, "candidate-readiness-timeout")
        result["shared_http"] = ready
        ensure(ready["sapi"] == "apache2handler" and ready["curl"], "shared-php-runtime-failed")
        result["tls"] = {}
        for version in (ssl.TLSVersion.TLSv1_2, ssl.TLSVersion.TLSv1_3):
            shared = request("localhost", https, alias + "/index.php", version)
            nts = request("gramlyze.loc", https, "/index.php", version)
            ensure(shared["sapi"] == "apache2handler" and shared["curl"], "https-shared-handler-failed")
            ensure(nts["sapi"] == "cgi-fcgi" and nts["curl"], "https-gramlyze-handler-failed")
            ensure(Path(nts["ini"]).resolve() == (xampp / "php-8.5.10-nts-gramlyze/php.ini").resolve(), "nts-ini-changed")
            result["tls"][version.name] = {"shared": shared, "gramlyze": nts}
        nts = request("gramlyze.loc", http, "/index.php")
        ensure(nts["sapi"] == "cgi-fcgi" and nts["curl"], "http-gramlyze-handler-failed")
        result["gramlyze_http"] = nts
        result["modules"] = {str(pid): helpers["process_modules"](pid) for pid in (process.pid, ready["pid"], nts["pid"])}
        for pid in (process.pid, ready["pid"]):
            modules = {Path(item["path"]).name.lower(): item for item in result["modules"][str(pid)]}
            for name, expected in candidates.items():
                ensure(modules[name]["sha256"] == expected, "loaded-candidate-hash-mismatch:" + name)
                ensure(Path(modules[name]["path"]).resolve().parent == (run / "bin").resolve(), "loaded-outside-private-copy")
        result["new_startup_errors"] = []
        for path in (run / "logs").glob("*"):
            if path.is_file():
                text = path.read_bytes()[log_offsets.get(str(path), 0):].decode("utf-8", errors="replace")
                for line in text.splitlines():
                    if any(mark in line for mark in ("PHP Startup", "Unable to load dynamic library", "ee key too small", "AH02562", "AH00016")):
                        result["new_startup_errors"].append({"log": path.name, "line": line})
        ensure(not result["new_startup_errors"], "candidate-startup-errors")
        result["success"] = True
    except Exception as exc:
        result["error"] = f"{type(exc).__name__}: {exc}"
    finally:
        if process:
            try:
                result["shutdown_exit_code"] = helpers["stop_own_apache"](process)
                if result["success"] and result["shutdown_exit_code"] != 0:
                    result["success"] = False
                    result["shutdown_error"] = "candidate-apache-did-not-exit-cleanly"
            except Exception as exc:
                result["success"] = False
                result["shutdown_error"] = str(exc)
        result["active_inputs_unchanged"] = all(digest(path) == value for path, value in protected.items())
        result["success"] = result["success"] and result["active_inputs_unchanged"]
        if process is None or process.poll() is not None:
            for folder in ("shared-fixture", "gramlyze-fixture"):
                path = safe(run / folder / "index.php")
                if path.exists():
                    path.unlink()
            result["temporary_endpoints_removed"] = True
            clone_key = safe(run / "conf/ssl.key/server.key")
            if clone_key.exists():
                clone_key.unlink()
            result["redundant_cloned_key_removed"] = True
        result_path = run / "result.json"
        result_path.write_text(json.dumps(result, indent=2), encoding="utf-8")
        if result["success"]:
            certificate = result["certificate"]
            evidence = {
                "schema": "gramlyze-local-certificate-candidate-v1",
                "candidate_cert": certificate["path"], "candidate_cert_sha256": certificate["sha256"],
                "candidate_key": certificate["key_path"], "candidate_key_sha256": certificate["key_sha256"],
                "original_cert_sha256": protected[str(xampp / "apache/conf/ssl.crt/server.crt")],
                "original_key_sha256": protected[str(xampp / "apache/conf/ssl.key/server.key")],
                "san": certificate["san"], "complete_config_success": True,
                "full_config_result_path": str(result_path), "full_config_result_sha256": digest(result_path),
                "trust_store_changed": False,
            }
            (run / "cert-evidence.json").write_text(json.dumps(evidence, indent=2), encoding="utf-8")
    print(json.dumps({"success": result["success"], "result": str(run / "result.json"), "error": result.get("error")}))
    return 0 if result["success"] else 1


if __name__ == "__main__":
    raise SystemExit(main())
