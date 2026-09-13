#!/usr/bin/env python3
"""Exercise the approved OpenSSL pair in a private loopback-only Apache copy.

Never writes to XAMPP or stops an existing Apache. The shared PHP module/ini
remain read-only inputs. This prerequisite is separate from apache_start.bat
cold-start acceptance. Generated keys, binaries and evidence remain private.
"""

import argparse
import ctypes
from ctypes import wintypes
import hashlib
import json
import os
from pathlib import Path
import shutil
import socket
import ssl
import subprocess
import time
import urllib.request
import uuid
import zipfile


ARCHIVE_SHA = "a6bc8b2f3d7bfb397ccb973db2f959e61e530e0986c9cea262dd4a317ec599d8"
DLL_HASHES = {
    "libssl-3-x64.dll": "dd76bebb8a13731a1bd047232c77299e7fa1fe9c8ef6d1563ca059473088630a",
    "libcrypto-3-x64.dll": "4978b06f18c1d092e4f7c8c864cc814db2ff4535baa2de54937348ffe14aae5e",
}
LIBSSH2_SHA = "a521fa2c78f5cb15136459ad6d5e6372112a49d920a859efab16f32fc625f4f3"
MODULES = ("authz_core", "authz_host", "mime", "dir", "env", "log_config",
           "socache_shmcb", "ssl")


def digest(path):
    return hashlib.sha256(Path(path).read_bytes()).hexdigest()


def ensure(condition, message):
    if not condition:
        raise RuntimeError(message)


def no_reparse(path):
    path = Path(path).absolute()
    for parent in (path, *path.parents):
        if parent.exists():
            ensure(not (parent.lstat().st_file_attributes & 0x400),
                   f"reparse-point-refused:{parent}")
    return path


def pick_ports():
    sockets = []
    try:
        for _ in range(2):
            sock = socket.socket()
            sock.bind(("127.0.0.1", 0))
            sockets.append(sock)
        return [s.getsockname()[1] for s in sockets]
    finally:
        for sock in sockets:
            sock.close()


def process_modules(pid):
    kernel = ctypes.WinDLL("kernel32", use_last_error=True)
    psapi = ctypes.WinDLL("psapi", use_last_error=True)
    kernel.OpenProcess.argtypes = (wintypes.DWORD, wintypes.BOOL, wintypes.DWORD)
    kernel.OpenProcess.restype = wintypes.HANDLE
    kernel.CloseHandle.argtypes = (wintypes.HANDLE,)
    psapi.EnumProcessModulesEx.argtypes = (wintypes.HANDLE, ctypes.POINTER(wintypes.HMODULE),
                                          wintypes.DWORD, ctypes.POINTER(wintypes.DWORD), wintypes.DWORD)
    psapi.GetModuleFileNameExW.argtypes = (wintypes.HANDLE, wintypes.HMODULE, wintypes.LPWSTR, wintypes.DWORD)
    handle = kernel.OpenProcess(0x410, False, pid)
    ensure(handle, f"cannot-open-own-test-process:{pid}:{ctypes.get_last_error()}")
    try:
        array = (wintypes.HMODULE * 2048)()
        needed = wintypes.DWORD()
        ensure(psapi.EnumProcessModulesEx(handle, array, ctypes.sizeof(array), ctypes.byref(needed), 3),
               f"cannot-enumerate-test-modules:{pid}")
        ensure(needed.value <= ctypes.sizeof(array), "module-buffer-too-small")
        result = []
        for module in array[:needed.value // ctypes.sizeof(wintypes.HMODULE)]:
            buffer = ctypes.create_unicode_buffer(32768)
            ensure(psapi.GetModuleFileNameExW(handle, module, buffer, len(buffer)), "cannot-read-module-path")
            path = Path(buffer.value)
            if any(part in path.name.lower() for part in ("php", "ssl", "crypto", "curl", "libpq", "ssh", "http2")):
                result.append({"path": str(path), "sha256": digest(path)})
        return result
    finally:
        kernel.CloseHandle(handle)


def stop_own_apache(process):
    # Signal only the process we created and still hold a handle to. Apache's
    # documented WinNT MPM event: https://github.com/apache/httpd/blob/2.4.58/server/mpm/winnt/mpm_winnt.c
    if process.poll() is not None:
        return process.returncode
    kernel = ctypes.WinDLL("kernel32", use_last_error=True)
    kernel.OpenEventW.argtypes = (wintypes.DWORD, wintypes.BOOL, wintypes.LPCWSTR)
    kernel.OpenEventW.restype = wintypes.HANDLE
    kernel.SetEvent.argtypes = (wintypes.HANDLE,)
    kernel.CloseHandle.argtypes = (wintypes.HANDLE,)
    # A failure immediately after Popen can precede the MPM's event creation.
    # Keep the owned process handle and wait briefly for that same instance.
    handle = None
    for _ in range(100):
        if process.poll() is not None:
            return process.returncode
        handle = kernel.OpenEventW(2, False, f"ap{process.pid}_shutdown")
        if handle:
            break
        time.sleep(0.1)
    ensure(handle, f"cannot-open-owned-apache-shutdown-event:{process.pid}")
    try:
        ensure(process.poll() is None, "owned-apache-exited-before-shutdown")
        ensure(kernel.SetEvent(handle), "owned-apache-shutdown-signal-failed")
    finally:
        kernel.CloseHandle(handle)
    return process.wait(timeout=30)


def fetch(url, context=None):
    handlers = [urllib.request.ProxyHandler({})]
    if context:
        handlers.append(urllib.request.HTTPSHandler(context=context))
    with urllib.request.build_opener(*handlers).open(url, timeout=30) as response:
        return json.load(response)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--xampp", type=Path, default=Path("C:/Program Files/xampp"))
    parser.add_argument("--archive", type=Path, required=True)
    parser.add_argument("--with-php-libssh2", action="store_true",
                        help="Explore the third dependency in the isolated copy only; never installs it")
    args = parser.parse_args()
    ensure(os.name == "nt", "Windows required")
    workspace = Path(__file__).resolve().parents[3]
    private = no_reparse(workspace / "storage/app/seo-m9-4-startup-local")
    private.mkdir(exist_ok=True)
    run = private / (time.strftime("isolated-%Y%m%d-%H%M%S-") + uuid.uuid4().hex[:8])
    run.mkdir()
    xampp = no_reparse(args.xampp)
    archive = no_reparse(args.archive)
    ensure(digest(archive) == ARCHIVE_SHA, "archive-hash-mismatch")
    candidates = dict(DLL_HASHES)
    if args.with_php_libssh2:
        candidates["libssh2.dll"] = LIBSSH2_SHA
    protected = {str(p): digest(p) for p in [xampp / "php/php.ini", xampp / "apache/conf/httpd.conf",
                 *(xampp / "apache/bin" / name for name in DLL_HASHES)]}
    result = {"schema": "gramlyze-isolated-openssl-v1", "directory": str(run), "success": False,
              "archive_sha256": ARCHIVE_SHA, "candidate_hashes": candidates,
              "shared_ini_sha256": digest(xampp / "php/php.ini")}
    process = None
    try:
        for name in ("bin", "modules", "logs", "htdocs", "conf"):
            (run / name).mkdir()
        for path in (xampp / "apache/bin").iterdir():
            if path.is_file() and path.suffix.lower() in (".dll", ".exe"):
                shutil.copy2(path, run / "bin" / path.name)
        for name in MODULES:
            shutil.copy2(xampp / f"apache/modules/mod_{name}.so", run / f"modules/mod_{name}.so")
        with zipfile.ZipFile(archive) as source:
            for name, expected in candidates.items():
                data = source.read(name)
                ensure(hashlib.sha256(data).hexdigest() == expected, f"candidate-hash-mismatch:{name}")
                (run / "bin" / name).write_bytes(data)
        cert = run / "conf/fixture.crt"
        key = run / "conf/fixture.key"
        cert_config = run / "conf/fixture.cnf"
        cert_config.write_text("[req]\ndistinguished_name=dn\nx509_extensions=extensions\nprompt=no\n"
                               "[dn]\nCN=Gramlyze isolated loopback fixture\n[extensions]\n"
                               "basicConstraints=critical,CA:FALSE\nsubjectAltName=IP:127.0.0.1\n", encoding="ascii")
        # No ErrorMode changes, window hiding or stream redirection. This server
        # is a prerequisite fixture, never evidence for the user's bat launch.
        flags = subprocess.CREATE_DEFAULT_ERROR_MODE
        generated = subprocess.run([str(run / "bin/openssl.exe"), "req", "-x509", "-newkey", "rsa:2048",
                                    "-nodes", "-days", "1", "-config", str(cert_config),
                                    "-keyout", str(key), "-out", str(cert)], cwd=xampp,
                                   creationflags=flags, timeout=30)
        ensure(generated.returncode == 0, "fixture-certificate-generation-failed")
        http, https = pick_ports()
        result["ports"] = {"http": http, "https": https}
        config = f'''ServerRoot "{run.as_posix()}"
Listen 127.0.0.1:{http}
Listen 127.0.0.1:{https}
ServerName 127.0.0.1
PidFile "logs/httpd.pid"
ErrorLog "logs/error.log"
LogLevel warn
ThreadsPerChild 32
MaxConnectionsPerChild 0
'''
        config += "".join(f'LoadModule {name}_module "modules/mod_{name}.so"\n' for name in MODULES)
        config += f'''LoadFile "{xampp.as_posix()}/php/php8ts.dll"
LoadFile "{xampp.as_posix()}/php/libpq.dll"
LoadFile "{xampp.as_posix()}/php/libsqlite3.dll"
LoadModule php_module "{xampp.as_posix()}/php/php8apache2_4.dll"
PHPIniDir "{xampp.as_posix()}/php"
TypesConfig "{xampp.as_posix()}/apache/conf/mime.types"
DocumentRoot "{run.as_posix()}/htdocs"
<Directory "{run.as_posix()}/htdocs">
    Require ip 127.0.0.1
    AllowOverride None
    Options None
</Directory>
<FilesMatch "\\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>
SSLCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
SSLHonorCipherOrder on
SSLProtocol all -SSLv3
SSLSessionCache "shmcb:{run.as_posix()}/logs/ssl_scache(512000)"
SSLSessionCacheTimeout 300
<VirtualHost 127.0.0.1:{https}>
    ServerName 127.0.0.1
    SSLEngine on
    SSLCertificateFile "{cert.as_posix()}"
    SSLCertificateKeyFile "{key.as_posix()}"
    SSLOptions +StdEnvVars
</VirtualHost>
'''
        (run / "conf/httpd.conf").write_text(config, encoding="utf-8")
        (run / "htdocs/tls.txt").write_text("gramlyze-isolated-tls-fixture\n", encoding="ascii")
        external_ca = xampp / "php-8.5.10-nts-gramlyze/extras/ssl/cacert.pem"
        php = '''<?php
header('Content-Type: application/json');
$result = ['pid' => getmypid(), 'sapi' => PHP_SAPI, 'version' => PHP_VERSION,
 'ini' => php_ini_loaded_file(), 'openssl' => OPENSSL_VERSION_TEXT,
 'extensions' => get_loaded_extensions(), 'tls' => $_SERVER['SSL_PROTOCOL'] ?? null];
if (isset($_GET['curl'])) {
 function probe($url, $ca = null) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
   CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROXY => '', CURLOPT_NOBODY => true]);
  if ($ca !== null) curl_setopt($ch, CURLOPT_CAINFO, $ca);
  $body = curl_exec($ch);
  return ['ok' => $body !== false, 'errno' => curl_errno($ch),
   'http' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE), 'verify' => curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT)];
 }
 $result['curl_ssl'] = curl_version()['ssl_version'];
 $result['trusted'] = probe('https://127.0.0.1:HTTPSPORT/tls.txt', FIXTURECA);
 $result['untrusted'] = probe('https://127.0.0.1:HTTPSPORT/tls.txt');
 $result['wrong_name'] = probe('https://localhost:HTTPSPORT/tls.txt', FIXTURECA);
 $result['external'] = probe('https://getcomposer.org/versions', EXTERNALCA);
 $db = new PDO('sqlite::memory:');
 $result['sqlite'] = (int)$db->query('SELECT 17')->fetchColumn();
}
echo json_encode($result, JSON_THROW_ON_ERROR);
'''.replace("HTTPSPORT", str(https)).replace("FIXTURECA", json.dumps(cert.as_posix())).replace("EXTERNALCA", json.dumps(external_ca.as_posix()))
        (run / "htdocs/probe.php").write_text(php, encoding="utf-8")
        command = [str(run / "bin/httpd.exe"), "-d", str(run), "-f", str(run / "conf/httpd.conf")]
        syntax = subprocess.run([*command, "-t"], cwd=xampp, creationflags=flags, timeout=30)
        result["syntax_exit_code"] = syntax.returncode
        ensure(syntax.returncode == 0, "isolated-syntax-failed")
        process = subprocess.Popen(command, cwd=xampp, creationflags=flags)
        result["parent_pid"] = process.pid
        ready = None
        for _ in range(100):
            ensure(process.poll() is None, "isolated-apache-exited")
            try:
                ready = fetch(f"http://127.0.0.1:{http}/probe.php")
                break
            except (OSError, ValueError):
                time.sleep(0.2)
        ensure(ready is not None, "isolated-readiness-timeout")
        result["http"] = ready
        result["modules"] = {str(pid): process_modules(pid) for pid in (process.pid, ready["pid"])}
        ensure(ready["sapi"] == "apache2handler", "shared-mod-php-not-running")
        ensure(Path(ready["ini"]).resolve() == (xampp / "php/php.ini").resolve(), "shared-ini-mismatch")
        expected_extensions = ("curl", "openssl", "mysqli", "pdo_mysql", "pdo_sqlite", "mbstring", "intl", "gd", "zip")
        ensure(all(name in ready["extensions"] for name in expected_extensions), "shared-extension-missing")
        result["tls"] = {}
        for version in (ssl.TLSVersion.TLSv1_2, ssl.TLSVersion.TLSv1_3):
            context = ssl.create_default_context(cafile=str(cert))
            context.minimum_version = context.maximum_version = version
            tls_result = fetch(f"https://127.0.0.1:{https}/probe.php", context)
            ensure(tls_result["sapi"] == "apache2handler", "https-php-handler-failed")
            ensure(tls_result["tls"] == version.name.replace("_", "."), "tls-protocol-mismatch")
            result["tls"][version.name] = tls_result
        result["curl"] = fetch(f"http://127.0.0.1:{http}/probe.php?curl=1")
        for name in ("trusted", "external"):
            probe = result["curl"][name]
            ensure(probe["ok"] and probe["errno"] == 0 and probe["verify"] == 0 and probe["http"] == 200,
                   f"verified-curl-failed:{name}")
        for name in ("untrusted", "wrong_name"):
            probe = result["curl"][name]
            ensure(not probe["ok"] and probe["errno"] == 60, f"tls-negative-not-rejected:{name}")
        ensure(result["curl"]["sqlite"] == 17, "shared-sqlite-failed")
        for modules in result["modules"].values():
            by_name = {Path(item["path"]).name.lower(): item for item in modules}
            for name, expected in candidates.items():
                ensure(by_name[name]["sha256"] == expected, f"loaded-dll-hash-mismatch:{name}")
                ensure(Path(by_name[name]["path"]).resolve().parent == (run / "bin").resolve(), "loaded-outside-isolation")
            ensure("php_curl.dll" in by_name and "php_openssl.dll" in by_name, "curl-openssl-module-not-loaded")
        result["success"] = True
    except Exception as exc:
        result["error"] = f"{type(exc).__name__}: {exc}"
    finally:
        if process:
            try:
                result["shutdown_exit_code"] = stop_own_apache(process)
                ensure(result["shutdown_exit_code"] == 0, "isolated-shutdown-failed")
            except Exception as exc:
                result["success"] = False
                result["shutdown_error"] = f"{type(exc).__name__}: {exc}"
        log = run / "logs/error.log"
        if log.exists():
            warnings = [line for line in log.read_text(encoding="utf-8", errors="replace").splitlines()
                        if "PHP Startup" in line or "Unable to load dynamic library" in line]
            result["startup_warnings"] = warnings
            if warnings:
                result["success"] = False
        result["protected_inputs_unchanged"] = all(digest(path) == old for path, old in protected.items())
        result["success"] = result["success"] and result["protected_inputs_unchanged"]
        # Remove fixtures only after the child has exited. Retain private logs,
        # generated config and binaries for the replacement audit, not in Git.
        if process is None or process.poll() is not None:
            for relative in ("htdocs/probe.php", "htdocs/tls.txt", "conf/fixture.key", "conf/fixture.crt"):
                target = no_reparse(run / relative)
                if target.exists():
                    target.unlink()
            result["temporary_endpoints_and_key_removed"] = True
        (run / "result.json").write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(json.dumps({"success": result["success"], "result": str(run / "result.json"),
                      "error": result.get("error"), "shutdown_error": result.get("shutdown_error")}))
    return 0 if result["success"] else 1


if __name__ == "__main__":
    raise SystemExit(main())
