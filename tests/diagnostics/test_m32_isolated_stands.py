import importlib.util
import io
import os
from pathlib import Path
import tarfile
import tempfile
import types
import unittest
import re
from unittest.mock import patch, MagicMock

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('stands', ROOT / 'tools/diagnostics/m32-isolated-stands.py')
stands = importlib.util.module_from_spec(spec)
spec.loader.exec_module(stands)


class IsolatedStandsTest(unittest.TestCase):
    def test_serve_rejects_non_loopback_manifest_before_any_child_or_inventory(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            target = folder / ('stands-' + 'a' * 32)
            for bad in ['http://0.0.0.0:50002', 'http://gramlyze.com:50002', 'http://localhost:50002',
                        'http://127.0.0.1:70000', 'http://user@127.0.0.1:50002', None]:
                with self.subTest(base=bad), patch.object(stands, 'OUT', folder), \
                     patch.object(stands, 'verify') as verify, patch.object(stands.subprocess, 'Popen') as spawn:
                    with self.assertRaisesRegex(ValueError, 'loopback'):
                        stands.serve({'folder': str(target), 'variants': {
                            'A': {'base': 'http://127.0.0.1:50001'}, 'B': {'base': bad}}})
                    verify.assert_not_called()
                    spawn.assert_not_called()
            stands.require_loopback_base('http://127.0.0.1:50001')

    def test_exporter_rejects_dangling_destination_links_before_dotenv_or_pdo(self):
        source = (ROOT / 'tools/diagnostics/m32-isolated-stands-export.php').read_text(encoding='utf-8')
        guard = source[:source.index('try {')]
        self.assertIn('file_exists($destination) || is_link($destination)', guard)
        self.assertIn('exit(3)', guard)
        self.assertLess(source.index('is_link($destination)'), source.index('Dotenv\\Dotenv::parse'))
        self.assertLess(source.index('is_link($destination)'), source.index('new PDO'))

    def test_actual_router_allows_minified_livewire_without_php_endpoints(self):
        source = (ROOT / 'tools/diagnostics/m32-isolated-stands-router.php').read_text(encoding='utf-8')
        pattern = re.search(r"preg_match\('(~\^/livewire[^']+~)'", source).group(1)[1:-1]
        for path in ['/livewire/livewire.js', '/livewire/livewire.min.js', '/livewire/livewire.min.js.map']:
            self.assertIsNotNone(re.fullmatch(pattern, path))
        for path in ['/livewire/restore.php', '/livewire/livewire.js/../restore.php', '/admin']:
            self.assertIsNone(re.fullmatch(pattern, path))

    def test_fixed_shas_and_secret_free_child_environment(self):
        self.assertEqual(stands.SHAS, {'A': '7c649117e747fb922da166c2e1fba59474fee203',
                                      'B': '54d34083a67e30084b967b572cc5a6b93c2eaf99'})
        with patch.dict(os.environ, {'DB_PASSWORD': 'sentinel', 'GITHUB_TOKEN': 'sentinel', 'PATH': 'bin'}, clear=True):
            self.assertEqual(stands.clean_env(), {'PATH': 'bin'})

    def test_owned_paths_fail_closed(self):
        with tempfile.TemporaryDirectory() as folder, patch.object(stands, 'OUT', Path(folder)):
            good = Path(folder) / ('stands-' + 'a' * 32) / 'A/data.sqlite'
            self.assertEqual(stands.owned(good), good.resolve())
            for path in [Path(folder) / 'stands-short/A', Path(folder).parent / 'data.sqlite',
                         Path(folder) / ('stands-' + 'z' * 32) / 'data.sqlite']:
                with self.assertRaises(ValueError):
                    stands.owned(path)

    def test_archive_traversal_and_links_rejected(self):
        for name, kind in [('../escape', tarfile.REGTYPE), ('unsafe-link', tarfile.SYMTYPE)]:
            with self.subTest(name=name), tempfile.TemporaryDirectory() as folder:
                folder = Path(folder)
                archive = folder / 'source.tar'
                with tarfile.open(archive, 'w') as handle:
                    item = tarfile.TarInfo(name)
                    item.type = kind
                    item.linkname = '../escape'
                    handle.addfile(item, io.BytesIO())
                with self.assertRaises(ValueError):
                    stands.extract_archive(archive, folder / 'source')

    def test_working_storage_link_and_question_snapshots_not_extracted(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            archive = folder / 'source.tar'
            with tarfile.open(archive, 'w') as handle:
                for name in ['public/storage', 'database/seeders/questions/one.json', 'app/example.php']:
                    item = tarfile.TarInfo(name)
                    if name == 'public/storage':
                        item.type = tarfile.SYMTYPE
                        item.linkname = '../../../working-storage'
                    handle.addfile(item, io.BytesIO())
            stands.extract_archive(archive, folder / 'source')
            self.assertFalse((folder / 'source/public/storage').exists())
            self.assertFalse((folder / 'source/database/seeders/questions/one.json').exists())
            self.assertTrue((folder / 'source/app/example.php').is_file())

    def test_dependencies_are_physical_copies_with_matching_digests(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            original = folder / 'original'
            original.mkdir()
            (original / 'one').write_bytes(b'original dependency bytes')
            a = stands.copy_tree(original, folder / 'A')
            b = stands.copy_tree(original, folder / 'B')
            self.assertEqual(a, b)
            (folder / 'A/one').write_bytes(b'private mutation')
            self.assertEqual((original / 'one').read_bytes(), b'original dependency bytes')
            self.assertEqual((folder / 'B/one').read_bytes(), b'original dependency bytes')
            with self.assertRaisesRegex(ValueError, 'differs'):
                stands.copy_tree(original, folder / 'A')

    def test_resume_verifies_existing_source_without_overwriting_it(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            archive = folder / 'source.tar'
            with tarfile.open(archive, 'w') as handle:
                item = tarfile.TarInfo('app/example.php')
                item.size = 2
                handle.addfile(item, io.BytesIO(b'{}'))
            stands.extract_archive(archive, folder / 'source')
            stands.extract_archive(archive, folder / 'source')
            target = folder / 'source/app/example.php'
            target.write_bytes(b'changed')
            with self.assertRaisesRegex(ValueError, 'differs'):
                stands.extract_archive(archive, folder / 'source')
            self.assertEqual(target.read_bytes(), b'changed')

    def test_archive_long_windows_paths(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            archive = folder / 'source.tar'
            name = '/'.join(['a' * 90, 'b' * 90, 'c' * 90, 'definition.json'])
            with tarfile.open(archive, 'w') as handle:
                item = tarfile.TarInfo(name)
                item.size = 2
                handle.addfile(item, io.BytesIO(b'{}'))
            stands.extract_archive(archive, folder / 'source')
            target = folder / 'source' / name
            self.assertEqual(stands.native(target).read_bytes(), b'{}')
            # tempfile's normal-path cleanup has the same Windows length limit.
            # Remove only these exact generated file/empty directories, deepest first.
            stands.native(target).unlink()
            parent = target.parent
            while parent != folder:
                parent.relative_to(folder)
                stands.native(parent).rmdir()
                parent = parent.parent

    def test_failed_preparation_still_captures_complete_after_inventory(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            with patch.object(stands, 'REPO', folder), patch.object(stands, 'OUT', folder / 'out'), \
                 patch.object(stands.guard, 'fingerprint_files', side_effect=[{'one': 'before'}, {'one': 'before'}]) as fingerprint, \
                 patch.object(stands, 'prepare_body', side_effect=RuntimeError('controlled failure')):
                with self.assertRaisesRegex(RuntimeError, 'controlled failure'):
                    stands.prepare(types.SimpleNamespace())
                self.assertEqual(fingerprint.call_count, 2)
                self.assertEqual(len(list((folder / 'out').glob('stands-*/prepare-finally-safety.json'))), 1)

    def test_provisioning_is_exactly_four_guest_gets_without_saving_html(self):
        with tempfile.TemporaryDirectory() as folder:
            folder = Path(folder)
            target = folder / ('stands-' + 'a' * 32)
            target.mkdir()
            variants = {key: {'base': f'http://127.0.0.1:{port}', 'php': '8.2.12',
                        'assets': {'resources/css/catalog-public.css': {'file': 'assets/public.css'}}}
                        for key, port in [('A', 50001), ('B', 50002)]}
            response = MagicMock()
            response.__enter__.return_value = response
            response.status = 200
            response.headers = {'X-M32-SAPI': 'cli-server', 'X-M32-PHP': '8.2.12',
                                'X-M32-Data-ReadOnly': 'sqlite-mode-ro-query-only', 'Content-Type': 'text/html'}
            response.read.return_value = ('<h1>Практика</h1>assets/public.css' + ' ' * 11000).encode()
            opener = MagicMock()
            opener.open.return_value = response
            with patch.object(stands, 'OUT', folder), patch.object(stands.urllib.request, 'build_opener', return_value=opener), patch('sys.stdout', io.StringIO()):
                stands.provision({'folder': str(target), 'variants': variants})
            calls = opener.open.call_args_list
            self.assertEqual(len(calls), 4)
            self.assertEqual(len({call.args[0].full_url for call in calls}), 4)
            self.assertTrue(all(call.args[0].get_method() == 'GET' and call.args[0].get_header('Cookie') is None for call in calls))
            files = list(target.iterdir())
            self.assertEqual(len(files), 1)
            self.assertEqual(files[0].suffix, '.json')
            self.assertNotIn('<h1>', files[0].read_text(encoding='utf-8'))


if __name__ == '__main__':
    unittest.main()
