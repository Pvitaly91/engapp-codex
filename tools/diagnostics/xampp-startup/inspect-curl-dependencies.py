#!/usr/bin/env python3
"""Read local PE imports/exports for the extra cURL dependency discovered in isolation.

Reports export compatibility only; the isolated Apache test supplies runtime
evidence. No DLL is loaded and no source file is modified.
"""

import argparse
import hashlib
import json
from pathlib import Path
import runpy


def audit(root):
    pe_module = runpy.run_path(str(Path(__file__).with_name("inspect-openssl-imports.py")))
    pe, pe_error = pe_module["PE"], pe_module["PEError"]
    root = root.resolve(strict=True)
    providers = {folder: pe((root / folder / "libssh2.dll").read_bytes()).exports()
                 for folder in ("apache/bin", "php")}
    paths = (list((root / "apache/bin").glob("*.dll")) + list((root / "apache/bin").glob("*.exe"))
             + list((root / "apache/modules").glob("*.so")) + [root / "php/ext/php_curl.dll"])
    consumers, skipped, examined = [], [], []
    for path in sorted(paths):
        relative = path.relative_to(root).as_posix()
        if not path.resolve(strict=True).is_relative_to(root):
            raise ValueError("Input resolves outside XAMPP root")
        try:
            imports = pe(path.read_bytes()).imports()
        except pe_error as error:
            if str(error) != "only x64 images are supported":
                raise
            skipped.append({"path": relative, "reason": "not an x64 consumer"})
            continue
        examined.append(relative)
        for entry in imports:
            if entry["dll"] == "libssh2.dll":
                consumers.append({"consumer": relative, "kind": entry["kind"],
                                  "required_symbols": entry["symbols"],
                                  "missing": {name: sorted(set(entry["symbols"]) - exports.keys())
                                              for name, exports in providers.items()}})
    candidate = root / "php/libssh2.dll"
    dependencies = []
    for entry in pe(candidate.read_bytes()).imports():
        dependency = {"dll": entry["dll"], "kind": entry["kind"]}
        if entry["dll"] == "libcrypto-3-x64.dll":
            exports = pe((root / "php" / entry["dll"]).read_bytes()).exports()
            dependency["missing_in_candidate_crypto"] = sorted(set(entry["symbols"]) - exports.keys())
        dependencies.append(dependency)
    return {
        "schema": "gramlyze-curl-libssh2-pe-v1",
        "method": "read-only PE tables, no DLL execution",
        "limitation": "Static imports and named exports, not runtime ABI or dynamic GetProcAddress coverage",
        "examined_x64": examined, "skipped": skipped, "consumers": consumers,
        "candidate_dependencies": dependencies,
        "provider_sha256": {folder: hashlib.sha256((root / folder / "libssh2.dll").read_bytes()).hexdigest()
                            for folder in providers},
        "old_named_exports_absent_in_candidate": sorted(
            name for name in providers["apache/bin"].keys() - providers["php"].keys() if not name.startswith("#")),
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--xampp", type=Path, default=Path("C:/Program Files/xampp"))
    args = parser.parse_args()
    result = audit(args.xampp)
    print(json.dumps(result, indent=2))


if __name__ == "__main__":
    main()
