"""Bounded, sanitized M10 production acceptance for Gramlyze.

Only anonymous GET/HEAD requests are issued.  HTML, cookies, tokens and question
contents are kept in memory and are never written to evidence.
"""
from __future__ import annotations

import argparse
import concurrent.futures
import hashlib
import json
import re
import secrets
import ssl
import time
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from html.parser import HTMLParser
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / "storage/app/seo-m10-production"
ALLOWED_HOSTS = {"gramlyze.com", "www.gramlyze.com"}
PRIMARY_ORIGIN = "https://gramlyze.com"
LOCAL_ORIGIN = "http://gramlyze.loc"
QUESTIONS_STATE_PATH = "/test/future-perfect/questions/state"
MAX_BODY = 16 * 1024 * 1024
REDIRECT_STATUSES = {301, 302, 303, 307, 308}
NETWORK_ERRORS = (TimeoutError, urllib.error.URLError, ConnectionError)

MATRIX_PATHS = [
    "/",
    "/theory",
    "/theory/future-perfect",
    "/theory/basic-grammar/sentence-types",
    "/test/future-perfect/questions",
    "/test/future-perfect/questions/questions?mode=saved-test-js-v2",
    "/test/future-perfect/questions/step",
    "/courses/english-grammar-theory",
    "/courses/english-grammar-theory/lesson/basic-grammar/sentence-types",
    "/theory/zaimennyky-ta-vkazivni-slova/one-ones",
    "/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another",
    "/theory/passive-voice/theory-passive-voice-formation-rules",
    "/search?q=future+perfect",
    "/robots.txt",
    "/sitemap.xml",
    "/en/theory/future-perfect",
    "/pl/theory/future-perfect",
    "/test/future-perfect/questions?seo_m10=1",
]

ASSET_PAGES = [
    "/",
    "/theory/basic-grammar/sentence-types",
    "/test/future-perfect/questions",
    "/courses/english-grammar-theory",
]


def now() -> str:
    import datetime
    return datetime.datetime.now(datetime.timezone.utc).isoformat()


def digest(value: Any) -> str:
    encoded = json.dumps(value, ensure_ascii=False, sort_keys=True, separators=(",", ":")).encode("utf-8")
    return hashlib.sha256(encoded).hexdigest()


def validate_origin(value: str) -> str:
    parsed = urllib.parse.urlsplit(value)
    if (
        parsed.scheme != "https"
        or parsed.hostname not in ALLOWED_HOSTS
        or parsed.username
        or parsed.password
        or parsed.port not in (None, 443)
        or parsed.path not in ("", "/")
        or parsed.query
        or parsed.fragment
    ):
        raise ValueError("origin must be exactly https://gramlyze.com or https://www.gramlyze.com")
    return f"https://{parsed.hostname}"


def safe_public_url(value: str, *, allow_http_seed: bool = False) -> str:
    parsed = urllib.parse.urlsplit(value)
    if (
        parsed.hostname not in ALLOWED_HOSTS
        or parsed.username
        or parsed.password
        or parsed.port not in (None, 80, 443)
        or parsed.scheme not in ({"http", "https"} if allow_http_seed else {"https"})
        or "\\" in urllib.parse.unquote(value)
        or any(ord(char) < 32 for char in value)
    ):
        raise ValueError("unsafe or external production URL")
    return urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, parsed.path or "/", parsed.query, ""))


def public_location(value: str | None, base: str) -> str | None:
    if not value:
        return None
    return safe_public_url(urllib.parse.urljoin(base, value), allow_http_seed=True)


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):  # noqa: N802
        return None


def opener() -> urllib.request.OpenerDirector:
    context = ssl.create_default_context()
    return urllib.request.build_opener(
        urllib.request.ProxyHandler({}),
        NoRedirect(),
        urllib.request.HTTPSHandler(context=context),
    )


class PageParser(HTMLParser):
    KEYS = (
        "title", "description", "h1", "canonical", "robots", "og:title",
        "og:description", "og:url", "twitter:title", "twitter:description",
    )

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.values = {key: [] for key in self.KEYS}
        self.assets: set[str] = set()
        self.links: set[str] = set()
        self.version_meta: dict[str, str] = {}
        self.active: str | None = None
        self.parts: list[str] = []
        self.visible_parts: list[str] = []
        self.block_depth = 0
        self.current_block: str | None = None
        self.block_text: dict[str, list[str]] = {}

    def handle_starttag(self, tag: str, attrs):
        attributes = dict(attrs)
        lowered = tag.lower()
        if lowered in ("title", "h1"):
            self.active, self.parts = lowered, []
        if lowered == "meta":
            key = attributes.get("name", attributes.get("property", "")).lower()
            if key in self.values:
                self.values[key].append(attributes.get("content", "").strip())
            if any(token in key for token in ("version", "revision", "commit", "release", "build")):
                self.version_meta[key] = attributes.get("content", "").strip()
        if lowered == "link":
            rels = attributes.get("rel", "").lower().split()
            href = attributes.get("href")
            if "canonical" in rels and href:
                self.values["canonical"].append(href.strip())
            if href and any(rel in rels for rel in ("stylesheet", "modulepreload", "preload")):
                self.assets.add(href)
        if lowered == "script" and attributes.get("src"):
            self.assets.add(attributes["src"])
        if lowered == "a" and attributes.get("href"):
            self.links.add(attributes["href"])
        block_id = attributes.get("id", "")
        if self.current_block:
            self.block_depth += 1
        elif block_id.startswith("block-"):
            self.current_block = block_id
            self.block_depth = 1
            self.block_text.setdefault(block_id, [])

    def handle_endtag(self, tag: str):
        lowered = tag.lower()
        if self.active == lowered:
            self.values[lowered].append(re.sub(r"\s+", " ", "".join(self.parts)).strip())
            self.active = None
        if self.current_block and self.block_depth:
            self.block_depth -= 1
            if self.block_depth == 0:
                self.current_block = None

    def handle_data(self, data: str):
        if self.active:
            self.parts.append(data)
        if data.strip():
            self.visible_parts.append(data)
            if self.current_block:
                self.block_text[self.current_block].append(data)


def parse_html(body: bytes, final_url: str) -> tuple[dict[str, Any], list[str], list[str], str]:
    text = body.decode("utf-8", errors="strict")
    parser = PageParser()
    parser.feed(text)
    parser.close()
    metadata = {key: [re.sub(r"\s+", " ", item).strip() for item in values] for key, values in parser.values.items()}
    absolute_assets: list[str] = []
    mixed: list[str] = []
    for item in sorted(parser.assets):
        url = urllib.parse.urljoin(final_url, item)
        parsed = urllib.parse.urlsplit(url)
        if parsed.scheme == "http":
            mixed.append(urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, parsed.path, "", "")))
        absolute_assets.append(urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, parsed.path, "", "")))
    links = []
    for item in sorted(parser.links):
        joined = urllib.parse.urljoin(final_url, item)
        parsed = urllib.parse.urlsplit(joined)
        if parsed.hostname in ALLOWED_HOSTS:
            links.append(parsed.path or "/")
    visible = re.sub(r"\s+", " ", " ".join(parser.visible_parts)).strip()
    block_values = [re.sub(r"\s+", " ", " ".join(parts)).strip() for parts in parser.block_text.values()]
    evidence = {
        "metadata": metadata,
        "metadata_sha256": digest(metadata),
        "visible_character_count": len(visible),
        "block_count": len(block_values),
        "empty_block_count": sum(not value for value in block_values),
        "literal_inline_tags_visible": bool(re.search(r"<(?:strong|span)(?:\s|>)", visible, re.I)),
        "version_meta": parser.version_meta,
    }
    return evidence, absolute_assets, sorted(set(links)), text


def read_once(url: str, accept: str, timeout: float = 45.0) -> tuple[dict[str, Any], bytes]:
    url = safe_public_url(url, allow_http_seed=True)
    row: dict[str, Any] = {"url": url, "started_at": now()}
    started = time.monotonic()
    body = b""
    try:
        request = urllib.request.Request(
            url,
            headers={"Accept": accept, "User-Agent": "Gramlyze-M10-production-acceptance/1.0"},
            method="GET",
        )
        try:
            response = opener().open(request, timeout=timeout)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            body = response.read(MAX_BODY + 1)
            headers = response.headers
            row.update(
                status=response.status,
                content_type=headers.get("Content-Type", ""),
                content_length=headers.get("Content-Length"),
                body_bytes=len(body),
                location=public_location(headers.get("Location"), url),
                x_robots_tag=headers.get("X-Robots-Tag"),
                cache_control=headers.get("Cache-Control"),
                vary=headers.get("Vary"),
                public_release_headers={
                    key: value for key, value in headers.items()
                    if any(token in key.lower() for token in ("release", "revision", "commit", "version", "deploy"))
                    and key.lower() not in {"server"}
                },
            )
        if len(body) > MAX_BODY:
            raise ValueError("response-size-limit")
        row["body_sha256"] = hashlib.sha256(body).hexdigest()
    except NETWORK_ERRORS as error:
        row["error"] = {"category": "network", "type": type(error).__name__, "message_sha256": digest(str(error))}
    except Exception as error:  # keep proof sanitized
        row["error"] = {"category": "response", "type": type(error).__name__, "message_sha256": digest(str(error))}
    row["duration_ms"] = round((time.monotonic() - started) * 1000, 2)
    return row, body


def chain(seed: str, accept: str = "text/html", max_hops: int = 6) -> tuple[dict[str, Any], bytes]:
    current = safe_public_url(seed, allow_http_seed=True)
    result: dict[str, Any] = {"requested_url": current, "steps": [], "retry_count": 0}
    seen: set[str] = set()
    final_body = b""
    for _ in range(max_hops):
        if current in seen:
            result["error"] = "redirect-loop"
            break
        seen.add(current)
        row, body = read_once(current, accept)
        result["steps"].append(row)
        final_body = body
        if row.get("error") or row.get("status") not in REDIRECT_STATUSES:
            break
        if not row.get("location"):
            result["error"] = "redirect-without-location"
            break
        current = safe_public_url(row["location"], allow_http_seed=True)
    else:
        result["error"] = "redirect-hop-limit"
    final = result["steps"][-1] if result["steps"] else {}
    result["final_url"] = final.get("url")
    result["status"] = final.get("status")
    return result, final_body


def inspect_json(body: bytes) -> dict[str, Any]:
    value = json.loads(body)
    if not isinstance(value, dict):
        raise ValueError("JSON root must be an object")
    questions = value.get("questions")
    if not isinstance(questions, list):
        raise ValueError("questions array missing")
    structures = sorted({tuple(sorted(item.keys())) for item in questions if isinstance(item, dict)})
    return {
        "root_keys": sorted(value.keys()),
        "question_count": len(questions),
        "question_object_shape_count": len(structures),
        "question_shapes_sha256": digest(structures),
    }


def canonical_path(url: str) -> str | None:
    parsed = urllib.parse.urlsplit(url)
    if parsed.scheme != "https" or parsed.hostname != "gramlyze.com" or parsed.query or parsed.fragment:
        return None
    return parsed.path or "/"


def inspect_document(chain_result: dict[str, Any], body: bytes) -> tuple[dict[str, Any], list[str], list[str]]:
    final = chain_result["steps"][-1]
    content_type = final.get("content_type", "").lower()
    evidence: dict[str, Any] = {}
    assets: list[str] = []
    links: list[str] = []
    if "text/html" in content_type and body:
        parsed, assets, links, text = parse_html(body, final["url"])
        evidence.update(parsed)
        path = urllib.parse.urlsplit(chain_result["requested_url"]).path
        lower = text.lower()
        if path.endswith("theory-passive-voice-formation-rules"):
            evidence["content_spot_check"] = {
                "debug_marker_absent": "Page Folder Unseed Targets Debug" not in text,
                "unseed_marker_absent": "Page_V3 folder unseed block" not in text,
                "learning_content_present": parsed["visible_character_count"] > 500,
            }
        elif path.endswith("/one-ones"):
            evidence["content_spot_check"] = {
                "coffee_not_conflicting": not ("coffee" in lower and "both correct and incorrect" in lower),
                "cold_some_absent": "a cold some" not in lower,
                "possessive_explanation_present": "possessive" in lower or "присв" in lower,
                "some_any_adjective_ones_present": ("some" in lower and "any" in lower and "ones" in lower),
                "no_empty_blocks": parsed["empty_block_count"] == 0,
                "no_literal_inline_tags": not parsed["literal_inline_tags_visible"],
            }
        elif "reciprocal-pronouns" in path:
            evidence["content_spot_check"] = {
                "explanatory_blocks_present": parsed["block_count"] >= 10,
                "no_empty_blocks": parsed["empty_block_count"] == 0,
            }
        evidence["mixed_content_assets"] = [asset for asset in assets if asset.startswith("http://")]
    elif "application/json" in content_type and body:
        evidence["json"] = inspect_json(body)
    return evidence, assets, links


def parse_sitemap(body: bytes) -> list[dict[str, str | None]]:
    root = ET.fromstring(body.decode("utf-8", errors="strict"))
    namespace = "{http://www.sitemaps.org/schemas/sitemap/0.9}"
    if root.tag != namespace + "urlset":
        raise ValueError("unexpected sitemap root")
    entries: list[dict[str, str | None]] = []
    for node in root.findall(namespace + "url"):
        loc = node.find(namespace + "loc")
        if loc is None or not loc.text:
            raise ValueError("sitemap loc missing")
        lastmod = node.find(namespace + "lastmod")
        entries.append({"loc": loc.text.strip(), "lastmod": lastmod.text.strip() if lastmod is not None and lastmod.text else None})
    return entries


def normalize_entries(entries: list[dict[str, str | None]]) -> list[dict[str, str | None]]:
    result = []
    for entry in entries:
        parsed = urllib.parse.urlsplit(entry["loc"] or "")
        result.append({"loc": PRIMARY_ORIGIN + (parsed.path or "/") + (("?" + parsed.query) if parsed.query else ""), "lastmod": entry["lastmod"]})
    return result


def compare_entries(before: list[dict[str, str | None]], after: list[dict[str, str | None]]) -> dict[str, Any]:
    old = [entry["loc"] for entry in before]
    new = [entry["loc"] for entry in after]
    return {
        "ordered_equal": before == after,
        "before_count": len(old),
        "after_count": len(new),
        "added": sorted(set(new) - set(old)),
        "removed": sorted(set(old) - set(new)),
    }


def sitemap_contract(entries: list[dict[str, str | None]]) -> dict[str, Any]:
    locs = [entry["loc"] or "" for entry in entries]
    forbidden = ("/step", "/manual", "/input", "/select", "/drag-drop", "/match", "/dialogue")
    return {
        "count": len(locs),
        "unique": len(locs) == len(set(locs)),
        "origin_ok": all(urllib.parse.urlsplit(loc).scheme == "https" and urllib.parse.urlsplit(loc).netloc == "gramlyze.com" for loc in locs),
        "query_free": all(not urllib.parse.urlsplit(loc).query for loc in locs),
        "forbidden_modes": [loc for loc in locs if any(token in urllib.parse.urlsplit(loc).path for token in forbidden)],
        "technical_endpoints": [loc for loc in locs if urllib.parse.urlsplit(loc).path.endswith(("/questions", "/state")) and "/questions/questions" in loc],
        "course_lessons": [loc for loc in locs if "/courses/english-grammar-theory/lesson/" in loc],
        "lastmod_count": sum(entry["lastmod"] is not None for entry in entries),
        "ordered_loc_sha256": digest(locs),
    }


def deterministic_sample(entries: list[dict[str, str | None]], minimum: int = 20) -> list[str]:
    paths = [urllib.parse.urlsplit(entry["loc"] or "").path or "/" for entry in entries]
    chosen: list[str] = []

    def add(path: str):
        if path in paths and path not in chosen:
            chosen.append(path)

    add("/")
    groups: dict[str, list[str]] = {}
    for item in paths:
        first = item.strip("/").split("/", 1)[0] if item != "/" else "root"
        groups.setdefault(first, []).append(item)
    for name in sorted(groups):
        add(groups[name][0])
    for prefix in ("/theory/", "/test/"):
        values = [item for item in paths if item.startswith(prefix)]
        if values:
            for index in (0, len(values) // 2, len(values) - 1):
                add(values[index])
    for item in paths:
        if item.startswith("/courses/"):
            add(item)
    if len(chosen) < minimum and paths:
        step = max(1, len(paths) // minimum)
        for item in paths[::step]:
            add(item)
            if len(chosen) >= minimum:
                break
    return chosen


def asset_content_type_ok(url: str, content_type: str) -> bool:
    path = urllib.parse.urlsplit(url).path.lower()
    mime = content_type.lower().split(";", 1)[0].strip()
    if path.endswith(".css"):
        return mime == "text/css"
    if path.endswith((".js", ".mjs")):
        return mime in {"application/javascript", "text/javascript", "application/x-javascript"}
    if path.endswith(".woff2"):
        return mime in {"font/woff2", "application/font-woff2"}
    return bool(mime)


def asset_row(url: str) -> dict[str, Any]:
    row, body = read_once(url, "*/*")
    return {
        "url": urllib.parse.urlunsplit((*urllib.parse.urlsplit(url)[:3], "", "")),
        "status": row.get("status"),
        "content_type": row.get("content_type"),
        "body_bytes": row.get("body_bytes"),
        "duration_ms": row.get("duration_ms"),
        "sha256": hashlib.sha256(body).hexdigest() if body and row.get("status") == 200 else None,
        "mime_ok": asset_content_type_ok(url, row.get("content_type", "")) if row.get("status") == 200 else False,
        "error": row.get("error"),
    }


def save_report(target: Path, report: dict[str, Any]):
    target.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")


def run(origin: str, label: str, local_origin: str | None = LOCAL_ORIGIN) -> dict[str, Any]:
    origin = validate_origin(origin)
    if not re.fullmatch(r"[a-z0-9][a-z0-9-]*", label):
        raise ValueError("simple unique label required")
    if local_origin not in (None, LOCAL_ORIGIN):
        raise ValueError("local comparison origin must be http://gramlyze.loc")
    OUTPUT.mkdir(parents=True, exist_ok=True)
    target = OUTPUT / f"{label}-http.json"
    if target.exists():
        raise FileExistsError("evidence label already exists")
    unknown = f"/seo-m10-definitely-missing-{secrets.token_hex(6)}"
    report: dict[str, Any] = {
        "schema": "gramlyze-m10-production-http-v1",
        "started_at": now(),
        "origin": origin,
        "policy": "Anonymous GET only; no Cookie/Authorization/Referer; no automatic retry; at most two concurrent GET; no full HTML/question bodies persisted.",
        "rows": [],
        "redirects": [],
        "unknown_path": unknown,
    }
    target.write_text("{}", encoding="utf-8")

    for seed in ("http://gramlyze.com/", "https://www.gramlyze.com/", origin + "/"):
        result, body = chain(seed)
        if body and result.get("status") == 200 and "text/html" in result["steps"][-1].get("content_type", ""):
            parsed, _, _, _ = parse_html(body, result["final_url"])
            result["final_canonical"] = parsed["metadata"]["canonical"]
        report["redirects"].append(result)
        save_report(target, report)

    all_paths = MATRIX_PATHS + [unknown]
    asset_refs: set[str] = set()
    discovered_course_links: list[str] = []
    for path in all_paths:
        accept = "application/json" if "/questions/questions" in path else ("application/xml,text/xml" if path == "/sitemap.xml" else "text/plain" if path == "/robots.txt" else "text/html")
        result, body = chain(origin + path, accept)
        evidence, assets, links = inspect_document(result, body)
        row = {
            "requested_path": path,
            "redirect_chain": [{key: step.get(key) for key in ("url", "status", "location", "duration_ms")} for step in result["steps"]],
            "final_url": result.get("final_url"),
            "status": result.get("status"),
            "content_type": result["steps"][-1].get("content_type") if result["steps"] else None,
            "response_time_ms": round(sum(step.get("duration_ms", 0) for step in result["steps"]), 2),
            "content_length": result["steps"][-1].get("content_length") if result["steps"] else None,
            "body_bytes": result["steps"][-1].get("body_bytes") if result["steps"] else None,
            "x_robots_tag": result["steps"][-1].get("x_robots_tag") if result["steps"] else None,
            "cache_control": result["steps"][-1].get("cache_control") if result["steps"] else None,
            "vary": result["steps"][-1].get("vary") if result["steps"] else None,
            "retry_count": result.get("retry_count", 0),
            "error": result.get("error") or (result["steps"][-1].get("error") if result["steps"] else "no-response"),
            **evidence,
        }
        report["rows"].append(row)
        if path in ASSET_PAGES:
            asset_refs.update(assets)
        if path == "/courses/english-grammar-theory":
            discovered_course_links = [item for item in links if item.startswith("/courses/english-grammar-theory/lesson/")]
        if path == "/robots.txt" and body:
            text = body.decode("utf-8", errors="strict")
            row["robots"] = {
                "lines": [line.strip() for line in text.splitlines() if line.strip() and not line.lstrip().startswith("#")],
                "sitemap_exact": any(line.strip().lower() == "sitemap: https://gramlyze.com/sitemap.xml" for line in text.splitlines()),
                "contains_local": "localhost" in text.lower() or ".loc" in text.lower(),
            }
        save_report(target, report)

    report["course_lesson_paths"] = sorted(set(discovered_course_links))

    first_sitemap_row, first_sitemap = read_once(origin + "/sitemap.xml", "application/xml,text/xml")
    second_sitemap_row, second_sitemap = read_once(origin + "/sitemap.xml", "application/xml,text/xml")
    production_entries = parse_sitemap(first_sitemap) if first_sitemap_row.get("status") == 200 else []
    second_entries = parse_sitemap(second_sitemap) if second_sitemap_row.get("status") == 200 else []
    report["sitemap"] = {
        "first": {key: first_sitemap_row.get(key) for key in ("status", "content_type", "body_bytes", "body_sha256", "duration_ms", "error")},
        "second": {key: second_sitemap_row.get(key) for key in ("status", "content_type", "body_bytes", "body_sha256", "duration_ms", "error")},
        "contract": sitemap_contract(production_entries),
        "stable_order": production_entries == second_entries,
        "ordered_paths": [urllib.parse.urlsplit(entry["loc"] or "").path or "/" for entry in production_entries],
    }
    if local_origin:
        local_row, local_body = local_read_once(local_origin + "/sitemap.xml")
        local_entries = parse_sitemap(local_body) if local_row.get("status") == 200 else []
        report["sitemap"]["local"] = {key: local_row.get(key) for key in ("status", "content_type", "body_bytes", "body_sha256", "duration_ms", "error")}
        report["sitemap"]["comparison"] = compare_entries(normalize_entries(local_entries), normalize_entries(production_entries))
    else:
        report["sitemap"]["comparison"] = None
    save_report(target, report)

    sample_paths = deterministic_sample(production_entries)
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        sample_results = list(pool.map(lambda item: sample_get(origin, item), sample_paths))
    report["sitemap"]["availability_sample"] = sample_results

    production_assets = sorted(asset for asset in asset_refs if urllib.parse.urlsplit(asset).hostname in ALLOWED_HOSTS)
    external_assets = sorted(asset for asset in asset_refs if urllib.parse.urlsplit(asset).hostname not in ALLOWED_HOSTS)
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        asset_results = list(pool.map(asset_row, production_assets))
    report["assets"] = {
        "references": production_assets,
        "external_references": external_assets,
        "rows": asset_results,
        "public_hot_or_hmr": any("/@vite/" in item or item.endswith("/hot") for item in asset_refs),
        "tailwind_play_cdn": any("cdn.tailwindcss.com" in item for item in asset_refs),
        "unpkg_alpine": any("unpkg.com" in item and "alpine" in item.lower() for item in asset_refs),
        "mixed_content": [item for item in asset_refs if item.startswith("http://")],
    }
    manifest_row, manifest_body = read_once(origin + "/build/manifest.json", "application/json")
    report["assets"]["manifest"] = {key: manifest_row.get(key) for key in ("status", "content_type", "body_bytes", "body_sha256", "duration_ms", "error")}
    if manifest_row.get("status") == 200 and "json" in manifest_row.get("content_type", ""):
        manifest = json.loads(manifest_body)
        files = sorted({value.get("file") for value in manifest.values() if isinstance(value, dict) and value.get("file")})
        report["assets"]["manifest"].update(entry_count=len(manifest), files=files, files_sha256=digest(files))
        known = {urllib.parse.urlsplit(item["url"]).path for item in report["assets"]["rows"]}
        missing_urls = [origin + "/build/" + item.lstrip("/") for item in files if "/build/" + item.lstrip("/") not in known]
        with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
            report["assets"]["rows"].extend(pool.map(asset_row, missing_urls))
        report["assets"]["references"] = sorted(set(report["assets"]["references"] + [origin + "/build/" + item.lstrip("/") for item in files]))

    health, health_body = read_once(origin + "/health", "application/json")
    health_shape = None
    if health.get("status") == 200:
        try:
            parsed_health = json.loads(health_body)
            health_shape = {"keys": sorted(parsed_health.keys()), "status_value": parsed_health.get("status")}
        except Exception:
            health_shape = {"valid_json": False}
    home = next(row for row in report["rows"] if row["requested_path"] == "/")
    public_headers = report["redirects"][-1]["steps"][-1].get("public_release_headers", {})
    report["release_marker"] = {
        "public_release_headers": public_headers,
        "version_meta": home.get("version_meta", {}),
        "health": {"status": health.get("status"), "content_type": health.get("content_type"), "shape": health_shape},
        "exact_sha_confirmed": any(re.fullmatch(r"[0-9a-f]{40}", str(value), re.I) for value in list(public_headers.values()) + list(home.get("version_meta", {}).values())),
    }
    report["finished_at"] = now()
    report["request_summary"] = {
        "matrix_paths": len(report["rows"]),
        "redirect_seeds": len(report["redirects"]),
        "sitemap_stability_gets": 2,
        "sitemap_sample_gets": len(sample_results),
        "asset_gets": len(report["assets"]["rows"]),
        "automatic_retries": 0,
        "max_parallel_gets": 2,
    }
    save_report(target, report)
    print(json.dumps({"file": str(target), "paths": len(report["rows"]), "sitemap": report["sitemap"]["contract"]["count"], "assets": len(asset_results)}))
    return report


def run_supplement(origin: str, label: str, source: str) -> dict[str, Any]:
    origin = validate_origin(origin)
    if not re.fullmatch(r"[a-z0-9][a-z0-9-]*", label):
        raise ValueError("simple unique label required")
    source_path = Path(source).resolve()
    if source_path.parent != OUTPUT.resolve() or not source_path.name.endswith("-http.json"):
        raise ValueError("supplement source must be an M10 HTTP evidence file")
    base = json.loads(source_path.read_text(encoding="utf-8"))
    if base.get("origin") != origin or not base.get("finished_at"):
        raise ValueError("completed matching HTTP evidence required")
    target = OUTPUT / f"{label}-supplement.json"
    if target.exists():
        raise FileExistsError("evidence label already exists")
    manifest_files = base.get("assets", {}).get("manifest", {}).get("files", [])
    urls = [origin + "/build/" + item.lstrip("/") for item in manifest_files]
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        assets = list(pool.map(asset_row, urls))
    locale_queries = []
    query = "?seo_m10=1&next=https%3A%2F%2Fexample.invalid%2Foutside"
    for locale in ("en", "pl"):
        result, _ = chain(origin + f"/{locale}/theory/future-perfect" + query)
        locale_queries.append({
            "requested_path": f"/{locale}/theory/future-perfect" + query,
            "steps": [{key: step.get(key) for key in ("url", "status", "location", "duration_ms")} for step in result["steps"]],
            "final_url": result.get("final_url"),
            "external_destination": bool(result.get("final_url") and urllib.parse.urlsplit(result["final_url"]).hostname not in ALLOWED_HOSTS),
        })
    state_result, _ = chain(origin + QUESTIONS_STATE_PATH, "text/html")
    final_state = state_result["steps"][-1] if state_result["steps"] else {}
    report = {
        "schema": "gramlyze-m10-production-http-supplement-v1",
        "started_at": now(),
        "origin": origin,
        "source": source_path.name,
        "policy": "Targeted anonymous GET follow-up only; manifest assets, locale query confinement and state GET method contract; no retry.",
        "manifest_assets": assets,
        "locale_query_confinement": locale_queries,
        "state_get": {key: final_state.get(key) for key in ("url", "status", "content_type", "x_robots_tag", "duration_ms", "error")},
        "finished_at": now(),
    }
    save_report(target, report)
    print(json.dumps({"file": str(target), "manifest_assets": len(assets), "locale_queries": len(locale_queries), "state_status": final_state.get("status")}))
    return report


def run_content_spot(origin: str, label: str) -> dict[str, Any]:
    origin = validate_origin(origin)
    if not re.fullmatch(r"[a-z0-9][a-z0-9-]*", label):
        raise ValueError("simple unique label required")
    target = OUTPUT / f"{label}-content.json"
    if target.exists():
        raise FileExistsError("evidence label already exists")
    definitions = {
        "/theory/zaimennyky-ta-vkazivni-slova/one-ones": ROOT / "database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json",
        "/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another": ROOT / "database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesReciprocalPronounsTheorySeeder/definition.json",
    }
    rows = []
    for path, source in definitions.items():
        response, body = read_once(origin + path, "text/html")
        text = body.decode("utf-8", errors="strict") if response.get("status") == 200 else ""
        parsed_html, _, _, _ = parse_html(body, origin + path) if text else ({"literal_inline_tags_visible": False}, [], [], "")
        definition = json.loads(source.read_text(encoding="utf-8"))
        target_bodies = [block["body"] for block in definition["page"]["blocks"] if block.get("layout") and block.get("body")]
        rows.append({
            "path": path,
            "status": response.get("status"),
            "expected_target_blocks": len(target_bodies),
            "exact_target_blocks": sum(item in text for item in target_bodies),
            "literal_inline_tags_visible": parsed_html["literal_inline_tags_visible"],
            "body_sha256": response.get("body_sha256"),
        })
    passive_path = "/theory/passive-voice/theory-passive-voice-formation-rules"
    passive_response, passive_body = read_once(origin + passive_path, "text/html")
    passive_text = passive_body.decode("utf-8", errors="strict") if passive_response.get("status") == 200 else ""
    rows.append({
        "path": passive_path,
        "status": passive_response.get("status"),
        "debug_marker_absent": "Page Folder Unseed Targets Debug" not in passive_text,
        "unseed_marker_absent": "Page_V3 folder unseed block" not in passive_text,
        "body_sha256": passive_response.get("body_sha256"),
    })
    report = {"schema": "gramlyze-m10-production-content-spot-v1", "started_at": now(), "origin": origin,
              "policy": "Three anonymous GET, source-body equality in memory, no HTML persisted, no retry.", "rows": rows, "finished_at": now()}
    save_report(target, report)
    print(json.dumps({"file": str(target), "rows": rows}))
    return report


def local_read_once(url: str) -> tuple[dict[str, Any], bytes]:
    parsed = urllib.parse.urlsplit(url)
    if parsed.scheme != "http" or parsed.hostname != "gramlyze.loc" or parsed.username or parsed.password or parsed.port not in (None, 80):
        raise ValueError("unsafe local comparison URL")
    row: dict[str, Any] = {"url": url}
    body = b""
    started = time.monotonic()
    try:
        request = urllib.request.Request(url, headers={"Accept": "application/xml", "User-Agent": "Gramlyze-M10-local-comparison/1.0"})
        with urllib.request.build_opener(urllib.request.ProxyHandler({})).open(request, timeout=45) as response:
            body = response.read(MAX_BODY + 1)
            row.update(status=response.status, content_type=response.headers.get("Content-Type", ""), body_bytes=len(body), body_sha256=hashlib.sha256(body).hexdigest())
    except Exception as error:
        row["error"] = {"type": type(error).__name__, "message_sha256": digest(str(error))}
    row["duration_ms"] = round((time.monotonic() - started) * 1000, 2)
    return row, body


def sample_get(origin: str, path: str) -> dict[str, Any]:
    result, _ = chain(origin + path, "text/html")
    final = result["steps"][-1] if result["steps"] else {}
    return {"path": path, "status": final.get("status"), "final_url": result.get("final_url"), "content_type": final.get("content_type"), "duration_ms": final.get("duration_ms"), "error": result.get("error") or final.get("error")}


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--origin", required=True)
    parser.add_argument("--label", required=True)
    parser.add_argument("--no-local-comparison", action="store_true")
    parser.add_argument("--supplement-from")
    parser.add_argument("--content-spot", action="store_true")
    args = parser.parse_args()
    try:
        if args.content_spot:
            if args.supplement_from or args.no_local_comparison:
                parser.error("--content-spot does not combine with other modes")
            run_content_spot(args.origin, args.label)
        elif args.supplement_from:
            if args.no_local_comparison:
                parser.error("--no-local-comparison is not used with --supplement-from")
            run_supplement(args.origin, args.label, args.supplement_from)
        else:
            run(args.origin, args.label, None if args.no_local_comparison else LOCAL_ORIGIN)
    except (ValueError, FileExistsError) as error:
        parser.error(str(error))
