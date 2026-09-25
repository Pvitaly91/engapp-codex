"""Synthetic PE fixtures exercise parser rejection and symbol audit decisions."""

import importlib.util
from pathlib import Path
import struct
import tempfile
import unittest


ROOT = Path(__file__).resolve().parents[2]
SPEC = importlib.util.spec_from_file_location(
    "xampp_startup_pe", ROOT / "tools/diagnostics/xampp-startup/inspect-openssl-imports.py")
pe = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(pe)


def fixture(imports=(), exports=(), delay=False, fallback=False):
    """One x64 section; imports are (dll, symbols), exports (name, forwarder)."""
    data = bytearray(0x2200)
    data[:2] = b"MZ"
    struct.pack_into("<I", data, 0x3C, 0x80)
    data[0x80:0x84] = b"PE\0\0"
    struct.pack_into("<HHIIIHH", data, 0x84, 0x8664, 1, 0, 0, 0, 240, 0x2022)
    struct.pack_into("<H", data, 0x98, 0x20B)
    struct.pack_into("<I", data, 0x98 + 60, 0x200)
    struct.pack_into("<I", data, 0x98 + 108, 16)
    struct.pack_into("<8sIIIIIIHHI", data, 0x188,
                     b".rdata\0\0", 0x2000, 0x1000, 0x2000, 0x200, 0, 0, 0, 0, 0)
    cursor = 0x200

    def allocate(payload):
        nonlocal cursor
        offset = cursor
        data[offset:offset + len(payload)] = payload
        cursor += len(payload)
        return offset, offset + 0xE00

    def name(value):
        return allocate(value.encode("ascii") + b"\0")[1]

    if imports:
        width = 32 if delay else 20
        offset, rva = allocate(bytes(width * (len(imports) + 1)))
        index = 13 if delay else 1
        struct.pack_into("<II", data, 0x98 + 112 + index * 8, rva, width * (len(imports) + 1))
        for i, (dll, symbols) in enumerate(imports):
            dll_rva = name(dll)
            values = []
            for symbol in symbols:
                if symbol.startswith("#"):
                    values.append((1 << 63) | int(symbol[1:]))
                else:
                    values.append(allocate(b"\0\0" + symbol.encode("ascii") + b"\0")[1])
            lookup = allocate(struct.pack("<" + "Q" * (len(values) + 1), *values, 0))[1]
            if delay:
                fields = (1, dll_rva, 0, lookup, lookup, 0, 0, 0)
            else:
                fields = (0 if fallback else lookup, 0, 0, dll_rva, lookup)
            struct.pack_into("<" + "I" * (width // 4), data, offset + i * width, *fields)
    if exports:
        export_offset, export_rva = allocate(bytes(40))
        dll_name = name("fixture.dll")
        addresses_offset, addresses = allocate(bytes(len(exports) * 4))
        names_offset, names = allocate(bytes(len(exports) * 4))
        ordinal_offset, ordinals = allocate(bytes(len(exports) * 2))
        for i, (symbol, forwarder) in enumerate(exports):
            struct.pack_into("<I", data, names_offset + i * 4, name(symbol))
            struct.pack_into("<H", data, ordinal_offset + i * 2, i)
            struct.pack_into("<I", data, addresses_offset + i * 4,
                             name(forwarder) if forwarder else 0x2FF0)
        struct.pack_into("<IIHHIIIIIII", data, export_offset,
                         0, 0, 0, 0, dll_name, 1, len(exports), len(exports), addresses, names, ordinals)
        struct.pack_into("<II", data, 0x98 + 112, export_rva, cursor - export_offset)
    return bytes(data)


class PeAuditTest(unittest.TestCase):
    def test_named_ordinal_delay_and_fallback_imports(self):
        for delay, fallback in ((False, False), (False, True), (True, False)):
            with self.subTest(delay=delay, fallback=fallback):
                image = pe.PE(fixture([(pe.OPENSSL_DLLS[0].upper(), [pe.SYMBOL, "#7"])],
                                      delay=delay, fallback=fallback))
                self.assertEqual(image.imports(), [{"dll": pe.OPENSSL_DLLS[0],
                    "kind": "delay" if delay else "normal", "symbols": [pe.SYMBOL, "#7"]}])

    def test_absent_exports_and_forwarders_are_not_success(self):
        exports = pe.PE(fixture(exports=[("SSL_old", None), (pe.SYMBOL, "other.SSL_new")])).exports()
        self.assertEqual(exports["#1"], None)
        self.assertEqual(exports["#2"], "other.SSL_new")
        result = pe.compare(["SSL_old", pe.SYMBOL, "SSL_missing", "#9"], exports)
        self.assertEqual(result["missing"], ["#9", "SSL_missing"])
        self.assertEqual(result["forwarded_unresolved"], {pe.SYMBOL: "other.SSL_new"})

    def test_rejects_truncated_non_x64_and_invalid_headers(self):
        for offset, fmt, value in ((0x84, "<H", 0x14C), (0x98, "<H", 0x10B),
                                   (0x86, "<H", 97), (0x98 + 108, "<I", 17),
                                   (0x3C, "<I", 0x100000)):
            with self.subTest(offset=offset, value=value):
                data = bytearray(fixture())
                struct.pack_into(fmt, data, offset, value)
                with self.assertRaises(pe.PEError):
                    pe.PE(data)
        for length in (0, 64, 0x1FF, 0x2100):
            with self.subTest(length=length), self.assertRaises(pe.PEError):
                pe.PE(fixture()[:length])

    def test_rejects_virtual_bytes_without_file_backing(self):
        data = bytearray(fixture())
        struct.pack_into("<I", data, 0x188 + 8, 0x3000)
        image = pe.PE(data)
        with self.assertRaises(pe.PEError):
            image.offset(0x3001)

    def test_rejects_missing_descriptor_terminator_and_path_names(self):
        data = bytearray(fixture([(pe.OPENSSL_DLLS[0], [pe.SYMBOL])]))
        struct.pack_into("<I", data, 0x98 + 112 + 8 + 4, 20)
        with self.assertRaises(pe.PEError):
            pe.PE(data).imports()
        for name in ("../libssl.dll", "C:\\libssl.dll"):
            with self.subTest(name=name), self.assertRaises(pe.PEError):
                pe.PE(fixture([(name, [pe.SYMBOL])])).imports()

    def test_rejects_invalid_export_ordinal_and_empty_export_slot(self):
        original = fixture(exports=[(pe.SYMBOL, None)])
        image = pe.PE(original)
        export_rva, _ = image.directory(0)
        fields = image.at("<IIHHIIIIIII", export_rva)
        for rva, fmt, value in ((fields[10], "<H", 1), (fields[8], "<I", 0)):
            data = bytearray(original)
            struct.pack_into(fmt, data, image.offset(rva), value)
            with self.assertRaises(pe.PEError):
                pe.PE(data).exports()

    def test_complete_audit_identifies_curl_source_and_provider_gap(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            for relative in pe.CONSUMERS:
                path = root / relative
                path.parent.mkdir(parents=True, exist_ok=True)
                symbols = [pe.SYMBOL, "SSL_old"] if relative == pe.CONSUMERS[0] else ["SSL_old"]
                path.write_bytes(fixture([(pe.OPENSSL_DLLS[0], symbols)]))
            for folder in ("apache/bin", "php"):
                for dll in pe.OPENSSL_DLLS:
                    path = root / folder / dll
                    path.parent.mkdir(parents=True, exist_ok=True)
                    symbols = [("SSL_old", None), ("crypto_old", None)]
                    if folder == "php":
                        symbols.extend([(pe.SYMBOL, None), ("crypto_new", None)])
                    imports = [(pe.OPENSSL_DLLS[1], ["crypto_new" if folder == "php" else "crypto_old"])]
                    path.write_bytes(fixture(imports=imports if dll == pe.OPENSSL_DLLS[0] else [], exports=symbols))
            (root / pe.OPTIONAL_CONSUMERS[0]).write_bytes(fixture())
            result = pe.audit(root)
            self.assertEqual(result["reported_symbol"]["curl_import_sources"], [pe.OPENSSL_DLLS[0]])
            self.assertEqual(result["reported_symbol"]["providers_export"], {"apache/bin": False, "php": True})
            curl = result["consumers"][pe.CONSUMERS[0]][0]
            self.assertEqual(curl["providers"]["apache/bin"]["missing"], [pe.SYMBOL])
            self.assertEqual(curl["providers"]["php"]["missing"], [])
            dependency = result["consumers"]["php/libssl-3-x64.dll"][0]
            self.assertEqual(dependency["providers"]["apache/bin"]["missing"], ["crypto_new"])
            self.assertEqual(dependency["providers"]["php"]["missing"], [])
            self.assertEqual(result["coverage"]["optional_consumers"],
                             {"apache/bin/libcurl.dll": "present", "php/libcurl.dll": "absent"})
            self.assertEqual(len(result["files"][pe.CONSUMERS[0]]["sha256"]), 64)
            self.assertNotIn(str(root), str(result))


if __name__ == "__main__":
    unittest.main()
