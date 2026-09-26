import importlib.util
from pathlib import Path
import unittest
from unittest.mock import patch


SPEC = importlib.util.spec_from_file_location(
    "seo_m10_http",
    Path(__file__).resolve().parents[2] / "tools/diagnostics/seo-m10-production-http.py",
)
m10 = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(m10)


class ProductionHttpTest(unittest.TestCase):
    def test_origin_allowlist_is_exact_https_and_has_no_credentials(self):
        self.assertEqual(m10.validate_origin("https://gramlyze.com"), "https://gramlyze.com")
        self.assertEqual(m10.validate_origin("https://www.gramlyze.com/"), "https://www.gramlyze.com")
        for value in (
            "http://gramlyze.com", "http://gramlyze.loc", "https://localhost",
            "https://example.com", "https://user:pass@gramlyze.com",
            "https://gramlyze.com:8443", "https://gramlyze.com/path",
        ):
            with self.subTest(value=value), self.assertRaises(ValueError):
                m10.validate_origin(value)

    def test_redirect_location_cannot_escape_allowlist(self):
        self.assertEqual(
            m10.public_location("https://www.gramlyze.com/theory", "https://gramlyze.com/"),
            "https://www.gramlyze.com/theory",
        )
        with self.assertRaises(ValueError):
            m10.public_location("https://attacker.invalid/", "https://gramlyze.com/")

    def test_metadata_is_sanitized_and_hashed_without_html(self):
        body = b"""<!doctype html><title>Title</title><meta name='description' content='Description'>
        <meta property='og:title' content='OG'><meta property='og:url' content='https://gramlyze.com/theory'>
        <link rel='canonical' href='https://gramlyze.com/theory'><link rel='stylesheet' href='/build/assets/app.css'>
        <h1>Heading</h1><script src='/build/assets/app.js'></script><main>Learning text</main>"""
        parsed, assets, links, text = m10.parse_html(body, "https://gramlyze.com/theory")
        self.assertEqual(parsed["metadata"]["canonical"], ["https://gramlyze.com/theory"])
        self.assertEqual(len(parsed["metadata_sha256"]), 64)
        self.assertEqual(assets, ["https://gramlyze.com/build/assets/app.css", "https://gramlyze.com/build/assets/app.js"])
        self.assertIn("Learning text", text)
        self.assertNotIn("html", parsed)
        self.assertEqual(links, [])

    def test_json_evidence_stores_shape_and_count_not_question_values(self):
        evidence = m10.inspect_json(b'{"questions":[{"id":1,"answer":"secret"}],"mode":"x"}')
        self.assertEqual(evidence["question_count"], 1)
        self.assertNotIn("secret", str(evidence))
        self.assertEqual(len(evidence["question_shapes_sha256"]), 64)

    def test_sitemap_comparison_is_order_sensitive_and_not_count_based(self):
        a = [{"loc": "https://gramlyze.com/a", "lastmod": None}, {"loc": "https://gramlyze.com/b", "lastmod": None}]
        self.assertTrue(m10.compare_entries(a, a)["ordered_equal"])
        self.assertFalse(m10.compare_entries(a, list(reversed(a)))["ordered_equal"])
        changed = m10.compare_entries(a, [a[0], {"loc": "https://gramlyze.com/c", "lastmod": None}])
        self.assertEqual(changed["added"], ["https://gramlyze.com/c"])
        self.assertEqual(changed["removed"], ["https://gramlyze.com/b"])

    def test_sample_has_required_edges_and_at_least_twenty_when_available(self):
        paths = ["/"] + [f"/theory/t-{i}" for i in range(20)] + [f"/test/x-{i}" for i in range(20)] + ["/courses/english-grammar-theory"]
        entries = [{"loc": "https://gramlyze.com" + path, "lastmod": None} for path in paths]
        sample = m10.deterministic_sample(entries)
        self.assertGreaterEqual(len(sample), 20)
        for expected in ("/", "/theory/t-0", "/theory/t-10", "/theory/t-19", "/test/x-0", "/test/x-10", "/test/x-19", "/courses/english-grammar-theory"):
            self.assertIn(expected, sample)

    def test_chain_does_not_retry_network_errors(self):
        with patch.object(m10, "read_once", return_value=({"url": "https://gramlyze.com/", "error": {"category": "network"}}, b"")) as read:
            result, _ = m10.chain("https://gramlyze.com/")
        self.assertEqual(read.call_count, 1)
        self.assertEqual(result["retry_count"], 0)

    def test_asset_mime_contract(self):
        self.assertTrue(m10.asset_content_type_ok("https://gramlyze.com/build/assets/app.css", "text/css; charset=utf-8"))
        self.assertTrue(m10.asset_content_type_ok("https://gramlyze.com/build/assets/app.js", "application/javascript"))
        self.assertFalse(m10.asset_content_type_ok("https://gramlyze.com/build/assets/app.js", "text/html"))


if __name__ == "__main__":
    unittest.main()
