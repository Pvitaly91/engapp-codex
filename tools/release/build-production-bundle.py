#!/usr/bin/env python3
"""Package an already-built, pinned Gramlyze checkout without server data."""
from __future__ import annotations

import argparse
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import shutil
import subprocess
import tarfile
import tempfile
from datetime import datetime, timezone

SOURCE_SHA = "2afd1ec1423c725610b6fcca20c13a62256c6801"
RUNTIME_SHA = "9d54759af57d898880b107db14c39e9acc451172"
EXPECTED_ENTRIES = {
    "resources/css/app.css", "resources/js/app.js",
    "resources/css/catalog-public.css", "resources/js/catalog-public.js",
}
PRIVATE_PREFIXES = (
    ".git/", ".github/", ".claude/", "nbproject/", "node_modules/", "vendor/",
    "storage/", "bootstrap/cache/", "public/build/", "public/storage/",
    "database/dumps/", "database/backups/", "backups/",
)
PRIVATE_FILES = {"public/storage", "public/hot", ".env", ".git", "auth.json"}
EMPTY_DIRS = (
    "bootstrap/cache", "storage/app/public", "storage/app/private",
    "storage/app/theory-menu", "storage/framework/cache/data",
    "storage/framework/sessions", "storage/framework/views", "storage/logs",
)
INSTALL_TEXT = """GRAMLYZE — ГОТОВА PRODUCTION-ЗБІРКА

Джерело: 2afd1ec1423c725610b6fcca20c13a62256c6801
Код застосунку: 9d54759af57d898880b107db14c39e9acc451172

В архіві є код, vendor без dev-залежностей, готовий public/build,
канонічні джерела й контракти. Composer/npm встановлювати на сервері
для складання цього пакета не потрібно. Потрібен PHP ^8.5 та потрібні
розширення. Перевірте саме веб-PHP, а також CLI.

ПЕРЕНЕСЕННЯ
1. Звірте зовнішню SHA256SUMS і розпакуйте архів у НОВИЙ каталог
   поруч із чинним релізом.
2. У корені розпакованого пакета: sha256sum -c SHA256SUMS.
3. Підключіть наявний серверний .env, збережіть APP_KEY, БД,
   поточне сховище сесій та завантаження. public/storage налаштуйте
   на наявні серверні uploads. Локальних даних у цьому пакеті немає.
4. Надайте PHP потрібний доступ до bootstrap/cache та storage.
   Порожні sessions/uploads у пакеті не замінюють чинні серверні дані.
5. Із нового кореня виконайте:
   php -r 'require "vendor/autoload.php"; echo "PHP dependencies OK", PHP_EOL;'
   php artisan package:discover --ansi
   php artisan theory:menu-build
   php artisan theory:menu-build --check
6. Після успішної підготовки перемкніть document root на public/
   нового релізу та штатно оновіть веб-PHP/OPcache.
7. Перевірте меню, урок і окрему гостьову тестову спробу.

Не генеруйте новий APP_KEY, не імпортуйте локальну БД, не запускайте
міграції, сіди чи content apply як частину цього перенесення.
Config/routes/views/menu-кеші не зібрані під чужий домен або шлях;
за потреби створіть їх у новому серверному релізі з його конфігурацією.

ВІДКАТ
Поверніть document root на попередній public/ та оновіть веб-PHP.
БД, сесії і завантаження цей build не змінює.

Повні передумови та відомі результати приймання:
docs/reports/gramlyze-perf-release-deploy.md
docs/reports/gramlyze-perf-release-acceptance.md

Це production-пакет із перевіреного коду, а не виконаний деплой.
Створення архіву не змінює зафіксованих статусів приймання.
"""

def run(args: list[str], cwd: Path | None = None) -> str:
    return subprocess.check_output(args, cwd=cwd, text=True).strip()

def digest(path: Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()

def inside(root: Path, value: str) -> Path:
    rel = PurePosixPath(value)
    if rel.is_absolute() or not rel.parts or ".." in rel.parts or "\\" in value:
        raise RuntimeError("Unsafe package path: " + value)
    target = root.joinpath(*rel.parts)
    if not target.resolve().is_relative_to(root.resolve()):
        raise RuntimeError("Path escapes package: " + value)
    return target

def excluded(name: str) -> bool:
    parts = PurePosixPath(name).parts
    if name in PRIVATE_FILES or name.startswith(PRIVATE_PREFIXES):
        return True
    if any(p in {".git", "node_modules", "__pycache__"} for p in parts):
        return True
    if any(p == ".env" or (p.startswith(".env.") and p != ".env.example") for p in parts):
        return True
    return name.lower().endswith((".sqlite", ".sqlite3", ".db", ".sql", ".dump", ".bak", ".log"))

def assert_source(root: Path) -> int:
    result = run(["node", "tools/diagnostics/verify-perf-release-inputs.cjs"], root)
    print(result, flush=True)
    expected = json.loads((root / "tests/Fixtures/perf-release-sha256.v1.json").read_text())["files"]
    if len(expected) != 523:
        raise RuntimeError("Unexpected source manifest size")
    return len(expected)

def assert_assets(root: Path) -> dict:
    build = root / "public/build"
    manifest = json.loads((build / "manifest.json").read_text())
    if not EXPECTED_ENTRIES.issubset(manifest):
        raise RuntimeError("Missing Vite entry point")
    for key, entry in manifest.items():
        files = [entry["file"]] + entry.get("css", []) + entry.get("assets", [])
        for value in files:
            target = inside(build, value)
            if not target.is_file() or not target.stat().st_size:
                raise RuntimeError("Missing/empty Vite output: " + value)
        for value in entry.get("imports", []) + entry.get("dynamicImports", []):
            if value not in manifest:
                raise RuntimeError("Missing Vite manifest import: " + value)
    return {key: {"file": manifest[key]["file"],
                  "sha256": digest(inside(build, manifest[key]["file"]))}
            for key in sorted(EXPECTED_ENTRIES)}

def assert_vendor(root: Path) -> int:
    lock = json.loads((root / "composer.lock").read_text())
    installed = json.loads((root / "vendor/composer/installed.json").read_text())
    packages = installed["packages"] if isinstance(installed, dict) else installed
    actual = {x["name"] for x in packages}
    expected = {x["name"] for x in lock["packages"]}
    if actual != expected:
        raise RuntimeError("Installed vendor differs from production packages in lock")
    if isinstance(installed, dict) and installed.get("dev") is not False:
        raise RuntimeError("Composer installation is not marked no-dev")
    for package in packages:
        pinned = next(x for x in lock["packages"] if x["name"] == package["name"])
        if package["version"] != pinned["version"]:
            raise RuntimeError("Package version differs from lock: " + package["name"])
    probe = ('require $argv[1]; foreach (["Illuminate\\\\Foundation\\\\Application",'
             '"App\\\\Http\\\\Controllers\\\\TestJsV2Controller",'
             '"App\\\\Support\\\\ConditionalsWidgetHints"] as $class) {'
             'if (!class_exists($class)) {fwrite(STDERR, $class." missing\\n"); exit(1);}}'
             'echo "Production autoload OK\\n";')
    print(run(["php", "-r", probe, str(root / "vendor/autoload.php")]), flush=True)
    return len(packages)

def assert_portable(root: Path) -> None:
    if (root / ".env").exists() or (root / "public/hot").exists():
        raise RuntimeError("Environment or Vite development marker in package")
    for folder in EMPTY_DIRS:
        path = root / folder
        if not path.is_dir():
            raise RuntimeError("Missing runtime directory: " + folder)
    for path in root.rglob("*"):
        rel = path.relative_to(root).as_posix()
        if path.is_symlink():
            target = os.readlink(path)
            if os.path.isabs(target) or not path.resolve().is_relative_to(root.resolve()):
                raise RuntimeError("External/absolute symlink in package: " + rel)
        if path.is_file() and (rel.startswith("storage/") or rel.startswith("bootstrap/cache/")):
            raise RuntimeError("Runtime state in portable package: " + rel)

def check_checksums(root: Path) -> int:
    n = 0
    for line in (root / "SHA256SUMS").read_text().splitlines():
        expected, relative = line.split("  ", 1)
        path = inside(root, relative)
        if digest(path) != expected:
            raise RuntimeError("Packaged file checksum mismatch: " + relative)
        n += 1
    return n

def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--source", required=True, type=Path)
    parser.add_argument("--output", required=True, type=Path)
    args = parser.parse_args()
    source, output = args.source.resolve(), args.output.resolve()
    if run(["git", "rev-parse", "HEAD"], source) != SOURCE_SHA:
        raise RuntimeError("Build source is not the pinned release")
    if run(["git", "diff", "--name-only", "HEAD", "--"], source):
        raise RuntimeError("A tracked source file changed during the build")
    if (source / ".env").exists():
        raise RuntimeError("Build checkout must not contain .env")
    assert_source(source)
    assets = assert_assets(source)
    production_count = assert_vendor(source)
    output.mkdir(parents=True, exist_ok=True)
    if any(output.iterdir()):
        raise RuntimeError("Output directory must be empty")
    label = "gramlyze-production-" + SOURCE_SHA[:12]
    with tempfile.TemporaryDirectory(prefix="gramlyze-production-") as temp:
        stage = Path(temp) / label
        stage.mkdir()
        entries = subprocess.check_output(["git", "ls-tree", "-r", "-z", SOURCE_SHA], cwd=source)
        excluded_count = 0
        for record in entries.split(b"\0"):
            if not record:
                continue
            descriptor, raw_name = record.split(b"\t", 1)
            mode, kind, _ = descriptor.decode().split(" ")
            name = os.fsdecode(raw_name)
            if excluded(name):
                excluded_count += 1
                continue
            if kind != "blob":
                raise RuntimeError("Unsupported tracked entry: " + name)
            target = inside(stage, name)
            target.parent.mkdir(parents=True, exist_ok=True)
            original = source / name
            if mode == "120000":
                link = os.readlink(original)
                if os.path.isabs(link) or not (target.parent / link).resolve().is_relative_to(stage.resolve()):
                    raise RuntimeError("Unexpected external tracked symlink: " + name)
                target.symlink_to(link)
            else:
                shutil.copy2(original, target)
        shutil.copytree(source / "vendor", stage / "vendor", symlinks=True)
        shutil.copytree(source / "public/build", stage / "public/build", symlinks=True)
        for folder in EMPTY_DIRS:
            (stage / folder).mkdir(parents=True, exist_ok=True)
        assert_portable(stage)
        source_count = assert_source(stage)
        assert_assets(stage)
        assert_vendor(stage)
        (stage / "INSTALL_UK.txt").write_text(INSTALL_TEXT, encoding="utf-8")
        metadata = {
            "format": 1, "source_sha": SOURCE_SHA, "runtime_sha": RUNTIME_SHA,
            "builder_sha": os.environ.get("GITHUB_SHA", "local"),
            "built_at": datetime.now(timezone.utc).isoformat(),
            "php": run(["php", "-r", "echo PHP_VERSION;"]),
            "node": run(["node", "--version"]),
            "composer_production_packages": production_count,
            "source_files_verified": source_count, "frontend": assets,
            "composer_lock_sha256": digest(stage / "composer.lock"),
            "package_lock_sha256": digest(stage / "package-lock.json"),
            "excluded_tracked_paths": excluded_count,
            "environment_included": False, "database_data_included": False,
            "server_bound_caches_included": False, "deployment_performed": False,
        }
        (stage / "PRODUCTION_BUILD.json").write_text(
            json.dumps(metadata, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        files = sorted(p for p in stage.rglob("*") if p.is_file() and not p.is_symlink())
        (stage / "SHA256SUMS").write_text(
            "".join(digest(p) + "  " + p.relative_to(stage).as_posix() + "\n" for p in files),
            encoding="utf-8")
        archive = output / (label + ".tar.gz")
        def clean_info(info: tarfile.TarInfo) -> tarfile.TarInfo:
            info.uid = info.gid = 0
            info.uname = info.gname = ""
            return info
        with tarfile.open(archive, "w:gz", compresslevel=6) as tar:
            tar.add(stage, arcname=label, filter=clean_info)
        with tempfile.TemporaryDirectory(prefix="gramlyze-unpack-check-") as extracted:
            with tarfile.open(archive, "r:gz") as tar:
                tar.extractall(extracted, filter="data")
            unpacked = Path(extracted) / label
            count = check_checksums(unpacked)
            assert_portable(unpacked)
            assert_source(unpacked)
            assert_assets(unpacked)
            assert_vendor(unpacked)
        checksum = digest(archive)
        (output / "SHA256SUMS").write_text(checksum + "  " + archive.name + "\n")
        shutil.copy2(stage / "INSTALL_UK.txt", output / "INSTALL_UK.txt")
        shutil.copy2(stage / "PRODUCTION_BUILD.json", output / "PRODUCTION_BUILD.json")
        print(json.dumps({"archive": archive.name, "bytes": archive.stat().st_size,
                          "sha256": checksum, "payload_files_verified": count,
                          "source_files_verified": source_count,
                          "production_packages": production_count}, indent=2), flush=True)

if __name__ == "__main__":
    main()

