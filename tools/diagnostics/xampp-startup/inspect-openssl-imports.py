#!/usr/bin/env python3
"""Read PE tables without loading DLLs; stdout is metadata, never binary contents.

This is an import/export prerequisite audit, not a Windows loader simulation or
proof of ABI/runtime compatibility. See Microsoft's PE format specification:
https://learn.microsoft.com/en-us/windows/win32/debug/pe-format
"""

import argparse
import hashlib
import json
from pathlib import Path
import struct


OPENSSL_DLLS = ("libssl-3-x64.dll", "libcrypto-3-x64.dll")
CONSUMERS = (
    "php/ext/php_curl.dll", "php/ext/php_openssl.dll", "php/php8ts.dll",
    "php/libpq.dll", "php/php8apache2_4.dll", "apache/modules/mod_ssl.so",
    "apache/bin/apr_crypto_openssl-1.dll",
    "apache/bin/libssl-3-x64.dll", "php/libssl-3-x64.dll",
)
OPTIONAL_CONSUMERS = ("apache/bin/libcurl.dll", "php/libcurl.dll")
SYMBOL = "SSL_get0_group_name"


class PEError(ValueError):
    """Malformed or unsupported PE input (no binary content in the message)."""


class PE:
    def __init__(self, data):
        self.data = data
        if data[:2] != b"MZ":
            raise PEError("missing DOS signature")
        pe = self.unpack("<I", 0x3C)[0]
        if self.slice(pe, 4) != b"PE\0\0":
            raise PEError("missing PE signature")
        machine, count, _, _, _, optional_size, _ = self.unpack("<HHIIIHH", pe + 4)
        if machine != 0x8664:
            raise PEError("only x64 images are supported")
        optional = pe + 24
        self.slice(optional, optional_size)
        if optional_size < 112 or self.unpack("<H", optional)[0] != 0x20B:
            raise PEError("invalid PE32+ optional header")
        self.header_size = self.unpack("<I", optional + 60)[0]
        self.slice(0, self.header_size)
        directory_count = self.unpack("<I", optional + 108)[0]
        if directory_count > (optional_size - 112) // 8:
            raise PEError("data directories exceed optional header")
        self.directories = [self.unpack("<II", optional + 112 + i * 8)
                            for i in range(directory_count)]
        if not 1 <= count <= 96:
            raise PEError("invalid section count")
        section_start = optional + optional_size
        if section_start + count * 40 > self.header_size:
            raise PEError("section table exceeds headers")
        self.sections = []
        for i in range(count):
            values = self.unpack("<8sIIIIIIHHI", section_start + i * 40)
            virtual_size, virtual, size, raw = values[1:5]
            if size:
                self.slice(raw, size)
                if raw < self.header_size:
                    raise PEError("section overlaps headers")
            end = virtual + max(virtual_size, size)
            if virtual < self.header_size or end > 0x100000000:
                raise PEError("invalid section RVA span")
            for previous, previous_end, previous_raw, previous_size in self.sections:
                if virtual < previous_end and previous < end:
                    raise PEError("overlapping section RVAs")
                if size and previous_size and raw < previous_raw + previous_size and previous_raw < raw + size:
                    raise PEError("overlapping section file spans")
            self.sections.append((virtual, end, raw, size))

    def slice(self, offset, size):
        if offset < 0 or size < 0 or offset + size > len(self.data):
            raise PEError("file range out of bounds")
        return self.data[offset:offset + size]

    def unpack(self, fmt, offset):
        return struct.unpack(fmt, self.slice(offset, struct.calcsize(fmt)))

    def offset(self, rva, size=1):
        if rva < 0 or size < 1:
            raise PEError("invalid RVA")
        if rva + size <= self.header_size:
            return rva
        for virtual, _, raw, raw_size in self.sections:
            if virtual <= rva and rva + size <= virtual + raw_size:
                return raw + rva - virtual
        raise PEError("RVA is not backed by file bytes")

    def at(self, fmt, rva):
        return self.unpack(fmt, self.offset(rva, struct.calcsize(fmt)))

    def string(self, rva):
        offset = self.offset(rva)
        end = self.data.find(b"\0", offset, min(len(self.data), offset + 4096))
        if end < 0:
            raise PEError("unterminated PE name")
        self.offset(rva, end - offset + 1)
        try:
            value = self.data[offset:end].decode("ascii")
        except UnicodeDecodeError as exc:
            raise PEError("non-ASCII PE name") from exc
        if not value or any(ord(c) < 33 or ord(c) > 126 for c in value):
            raise PEError("invalid PE name")
        return value

    def directory(self, index):
        rva, size = self.directories[index] if index < len(self.directories) else (0, 0)
        if bool(rva) != bool(size):
            raise PEError("incomplete data directory")
        if size:
            self.offset(rva, size)
        return rva, size

    def thunks(self, rva):
        if not rva:
            raise PEError("missing import lookup table")
        symbols = []
        for i in range(len(self.data) // 8):
            value = self.at("<Q", rva + i * 8)[0]
            if value == 0:
                return symbols
            if value & (1 << 63):
                if value & ~((1 << 63) | 0xFFFF):
                    raise PEError("invalid ordinal import")
                symbols.append("#" + str(value & 0xFFFF))
            elif value <= 0xFFFFFFFF:
                self.at("<H", value)  # The hint precedes the name.
                symbols.append(self.string(value + 2))
            else:
                raise PEError("invalid import name RVA")
        raise PEError("unterminated import lookup table")

    def imports(self):
        imports = []
        for index, width, kind in ((1, 20, "normal"), (13, 32, "delay")):
            rva, size = self.directory(index)
            if not size:
                continue
            terminated = False
            for position in range(0, size - width + 1, width):
                fields = self.at("<" + "I" * (width // 4), rva + position)
                if not any(fields):
                    terminated = True
                    break
                if kind == "normal":
                    lookup, _, _, name, address = fields
                    lookup = lookup or address
                else:
                    attributes, name, _, _, lookup, _, _, _ = fields
                    if attributes != 1:
                        raise PEError("only RVA delay-import descriptors are supported")
                dll = self.string(name).lower()
                if "/" in dll or "\\" in dll or ":" in dll:
                    raise PEError("import DLL name must be a basename")
                imports.append({"dll": dll, "kind": kind, "symbols": self.thunks(lookup)})
            if not terminated:
                raise PEError("unterminated import descriptor table")
        return imports

    def exports(self):
        rva, size = self.directory(0)
        if not size:
            return {}
        if size < 40:
            raise PEError("truncated export directory")
        fields = self.at("<IIHHIIIIIII", rva)
        _, _, _, _, name, base, count, named, addresses, names, ordinals = fields
        self.string(name)
        if named > count or count > len(self.data) // 4:
            raise PEError("invalid export counts")
        if count:
            self.offset(addresses, count * 4)
        if named:
            self.offset(names, named * 4)
            self.offset(ordinals, named * 2)
        exports = {}
        for i in range(count):
            value = self.at("<I", addresses + i * 4)[0]
            if value:
                forwarder = self.string(value) if rva <= value < rva + size else None
                if forwarder is None:
                    self.offset(value)
                exports["#" + str(base + i)] = forwarder
        for i in range(named):
            ordinal = self.at("<H", ordinals + i * 2)[0]
            if ordinal >= count:
                raise PEError("export ordinal out of bounds")
            symbol = self.string(self.at("<I", names + i * 4)[0])
            key = "#" + str(base + ordinal)
            if key not in exports or symbol in exports:
                raise PEError("invalid named export")
            exports[symbol] = exports[key]
        return exports


def compare(symbols, exports):
    return {
        "missing": sorted(set(symbols) - exports.keys()),
        "forwarded_unresolved": {s: exports[s] for s in sorted(set(symbols) & exports.keys())
                                 if exports[s] is not None},
    }


def audit(root):
    root = root.resolve(strict=True)
    images, files = {}, {}
    optional_present = [name for name in OPTIONAL_CONSUMERS if (root / name).exists()]
    consumers_to_read = list(CONSUMERS) + optional_present
    names = dict.fromkeys(consumers_to_read + [folder + "/" + dll
                         for folder in ("apache/bin", "php") for dll in OPENSSL_DLLS])
    for name in names:
        path = (root / name).resolve(strict=True)
        if not path.is_relative_to(root):
            raise PEError("input resolves outside XAMPP root")
        data = path.read_bytes()
        images[name] = PE(data)
        files[name] = {"bytes": len(data), "sha256": hashlib.sha256(data).hexdigest(), "machine": "x64"}
    providers = {folder: {dll: images[folder + "/" + dll].exports() for dll in OPENSSL_DLLS}
                 for folder in ("apache/bin", "php")}
    consumers = {}
    for name in consumers_to_read:
        details = []
        for imported in images[name].imports():
            dll, symbols = imported["dll"], imported["symbols"]
            if dll.startswith(("libssl", "libcrypto")):
                if dll not in OPENSSL_DLLS:
                    raise PEError("unexpected OpenSSL DLL basename")
                details.append({**imported, "providers": {
                    folder: compare(symbols, dlls[dll]) for folder, dlls in providers.items()
                }})
        consumers[name] = details
    curl_sources = [entry["dll"] for entry in consumers[CONSUMERS[0]] if SYMBOL in entry["symbols"]]
    # This observation is deliberately not a precondition: future repaired inputs
    # may no longer import the reported symbol and must report that honestly.
    return {
        "schema": 1, "method": "read-only PE import/export tables; no DLL execution",
        "limitation": "Export presence is necessary, not proof of ABI compatibility; forwarders are unresolved.",
        "coverage": {"required_consumers": list(CONSUMERS),
                     "optional_consumers": {name: "present" if name in optional_present else "absent"
                                            for name in OPTIONAL_CONSUMERS}},
        "files": files, "consumers": consumers,
        "reported_symbol": {"name": SYMBOL, "curl_import_sources": curl_sources,
                            "providers_export": {folder: SYMBOL in dlls[OPENSSL_DLLS[0]]
                                                 for folder, dlls in providers.items()}},
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--xampp", type=Path, default=Path("C:/Program Files/xampp"))
    args = parser.parse_args()
    try:
        result = audit(args.xampp)
    except (OSError, PEError) as exc:
        # OSError text may contain arbitrary filesystem paths; expose only its class.
        reason = str(exc) if isinstance(exc, PEError) else type(exc).__name__
        print(json.dumps({"error": reason}))
        return 2
    print(json.dumps(result, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
