import importlib.util
import os
from pathlib import Path
import tempfile
import unittest
import zipfile
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('composer_repro', Path(__file__).resolve().parents[2] / 'tools/diagnostics/composer-reproducibility.py')
repro = importlib.util.module_from_spec(spec)
spec.loader.exec_module(repro)


class ComposerReproducibilityTest(unittest.TestCase):
    def test_environment_excludes_credentials_and_security_bypasses(self):
        with tempfile.TemporaryDirectory() as temp, patch.dict(os.environ, {
            'COMPOSER_AUTH': 'private-value', 'COMPOSER_NO_AUDIT': '1',
            'COMPOSER_NO_SECURITY_BLOCKING': '1', 'COMPOSER_NO_BLOCKING': '1',
            'COMPOSER_IGNORE_PLATFORM_REQS': '1', 'DB_PASSWORD': 'private-value',
            'APP_KEY': 'private-value', 'HTTP_PROXY': 'http://127.0.0.1:1234',
        }):
            env = repro.safe_environment(Path(temp), 'C:/php/php.exe')
            for key in ['COMPOSER_AUTH', 'COMPOSER_NO_AUDIT', 'COMPOSER_NO_SECURITY_BLOCKING',
                        'COMPOSER_NO_BLOCKING', 'COMPOSER_IGNORE_PLATFORM_REQS', 'DB_PASSWORD',
                        'APP_KEY', 'HTTP_PROXY']:
                self.assertNotIn(key, env)
            self.assertEqual(env['COMPOSER_HOME'], str(Path(temp) / 'home'))
            self.assertEqual(env['GIT_CEILING_DIRECTORIES'], str(repro.PRIVATE))
            self.assertEqual(env['GIT_CONFIG_NOSYSTEM'], '1')
            self.assertNotIn(str(repro.ROOT / 'vendor'), env['PATH'])

    def test_official_download_rejects_production_http_credentials_and_other_hosts(self):
        repro.require_official('https://getcomposer.org/download/2.10.3/composer.phar')
        for url in ['http://getcomposer.org/a', 'https://gramlyze.com/a', 'https://gramlyze.ub/a',
                    'https://getcomposer.org.evil.example/a', 'https://user:secret@getcomposer.org/a',
                    'https://getcomposer.org:8080/a']:
            with self.assertRaises(ValueError):
                repro.require_official(url)

    def test_inventory_map_keeps_dev_and_production_without_fabricating_lock(self):
        value = {'packages': [{'name': 'a/b', 'version': '1.0'}],
                 'packages-dev': [{'name': 'c/d', 'version': '2.0'}]}
        self.assertEqual(set(repro.package_map(value)), {'a/b', 'c/d'})
        self.assertNotIn('content-hash', value)

    def test_evidence_is_exclusive(self):
        with tempfile.TemporaryDirectory() as temp:
            target = Path(temp) / 'evidence.json'
            repro.write_new(target, {'first': True})
            with self.assertRaises(FileExistsError):
                repro.write_new(target, {'first': False})
            self.assertEqual(repro.read(target), {'first': True})

    def test_resolver_forbids_existing_vendor_before_process(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            (root / 'candidate/vendor').mkdir(parents=True)
            with patch.object(repro, 'PRIVATE', root), patch.object(repro, 'capture') as capture:
                with self.assertRaisesRegex(ValueError, 'no vendor'):
                    repro.composer('php', 'unsafe', ['update'])
                capture.assert_not_called()

    def test_regular_tree_rejects_linked_entries(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            (root / 'ordinary').mkdir()
            with patch.object(Path, 'is_junction', lambda self: self.name == 'ordinary'):
                with self.assertRaisesRegex(ValueError, 'Linked entry'):
                    repro.regular_tree(root)

    def test_archive_comparison_reports_changes_without_extracting(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            local = root / 'vendor'
            local.mkdir()
            (local / 'same.php').write_bytes(b'unchanged')
            (local / 'changed.php').write_bytes(b'manual edit')
            (local / 'extra.php').write_bytes(b'extra')
            archive = root / 'source.zip'
            with zipfile.ZipFile(archive, 'w') as output:
                output.writestr('package-ref/same.php', b'unchanged')
                output.writestr('package-ref/changed.php', b'original')
                output.writestr('package-ref/missing.php', b'original')
            result = repro.compare_archive(archive, local)
            self.assertEqual(result['changed'], ['changed.php'])
            self.assertEqual(result['missing'], ['missing.php'])
            self.assertEqual(result['extra'], ['extra.php'])
            self.assertEqual((local / 'changed.php').read_bytes(), b'manual edit')
            self.assertFalse((local / 'missing.php').exists())

    def test_archive_traversal_and_multiple_roots_are_refused(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            for members in [('pkg/../bad',), ('first/a', 'second/b')]:
                archive = root / 'invalid.zip'
                with zipfile.ZipFile(archive, 'w') as output:
                    for member in members:
                        output.writestr(member, 'not executed')
                with self.assertRaises(ValueError):
                    repro.compare_archive(archive, root / 'absent-vendor')

    def test_official_minified_inventory_inherits_and_unsets_without_changing_requirements(self):
        data = {'minified': 'composer/2.0', 'packages': {'a/b': [
            {'name': 'a/b', 'version': 'v2.0', 'source': {'reference': 'new'}, 'require': {'php': '^8.2'}, 'extra': 'new-only'},
            {'version': 'v1.0', 'source': {'reference': 'old'}, 'extra': '__unset'},
        ]}}
        with patch.object(repro, 'read', return_value=data):
            result = repro.official_package('a/b', '1.0')
        self.assertEqual(result['source']['reference'], 'old')
        self.assertEqual(result['require'], {'php': '^8.2'})
        self.assertNotIn('extra', result)
        self.assertEqual(data['packages']['a/b'][0]['source']['reference'], 'new')


if __name__ == '__main__':
    unittest.main()
