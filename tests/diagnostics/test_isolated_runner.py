"""No Laravel or working files: exercise the wrapper under a cp1251 console."""
import importlib.util
import io
import json
import os
import subprocess
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

SPEC = importlib.util.spec_from_file_location(
    'isolated_runner', Path(__file__).resolve().parents[2] / 'tools/diagnostics/run-isolated-tests.py')
RUNNER = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(RUNNER)


class IsolatedRunnerTest(unittest.TestCase):
    def test_private_php_startup_override_preserves_scan_dirs_and_is_inherited(self):
        for existing in [None, '', 'existing-one' + os.pathsep + 'existing-two']:
            with self.subTest(existing=existing), tempfile.TemporaryDirectory() as temporary:
                root = Path(temporary)
                env = {} if existing is None else {'PHP_INI_SCAN_DIR': existing}
                RUNNER.configure_php_startup(env, root)
                scan = root / 'php-ini'
                self.assertEqual((existing or '') + os.pathsep + str(scan), env['PHP_INI_SCAN_DIR'])
                self.assertEqual('opcache.enable_cli=0\n', (scan / '99-isolated-tests.ini').read_text(encoding='utf-8'))
                child = {**env, 'GRAMLYZE_TEST_RUNTIME': str(root / 'child')}
                self.assertEqual(env['PHP_INI_SCAN_DIR'], child['PHP_INI_SCAN_DIR'])
                self.assertEqual([scan / '99-isolated-tests.ini'], list(root.rglob('*.ini')))

    def test_utf8_output_and_child_failure_survive_cp1251_console(self):
        stdout_bytes, stderr_bytes = io.BytesIO(), io.BytesIO()
        stdout = io.TextIOWrapper(stdout_bytes, encoding='cp1251')
        stderr = io.TextIOWrapper(stderr_bytes, encoding='cp1251')
        child_out = 'Перевірка ✓ 日本語\n'.encode('utf-8')
        child_err = 'Помилка → №22\n'.encode('utf-8') + b'\xff'
        responses = [subprocess.CompletedProcess([], 0, b'{"environment":"testing"}', b''),
                     subprocess.CompletedProcess([], 7, child_out, child_err)]
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            with patch.object(RUNNER, 'REPO', root), patch.object(RUNNER, 'fingerprint_files', return_value={'fixture': 'same'}), \
                    patch.object(RUNNER.subprocess, 'run', side_effect=responses), \
                    patch('sys.argv', ['runner', '--label', 'utf8', 'tests/FakeTest.php']), \
                    patch('sys.stdout', stdout), patch('sys.stderr', stderr):
                with self.assertRaises(SystemExit) as raised:
                    RUNNER.main()
            self.assertEqual(7, raised.exception.code)
            record = json.loads(next(root.rglob('*-result.json')).read_text(encoding='utf-8'))
            self.assertEqual(7, record['exit_code'])
            self.assertEqual(7, record['runner_exit_code'])
            self.assertEqual(child_out.decode('utf-8'), record['stdout'])
            self.assertTrue(record['stderr'].endswith('\\xff'))
            self.assertEqual(child_err, next(root.rglob('phpunit.stderr.bin')).read_bytes())
            stdout.flush()
            stderr.flush()
            self.assertIn(child_out.rstrip(b'\n'), stdout_bytes.getvalue())
            self.assertIn('Помилка → №22'.encode('utf-8'), stderr_bytes.getvalue())

    def test_fingerprint_digest_is_order_independent(self):
        self.assertEqual(RUNNER.fingerprint_digest({'b': '2', 'a': '1'}),
                         RUNNER.fingerprint_digest({'a': '1', 'b': '2'}))

    def test_protected_file_change_fails_wrapper_without_rewriting_child_exit(self):
        responses = [subprocess.CompletedProcess([], 0, b'{}', b''),
                     subprocess.CompletedProcess([], 0, b'OK', b'')]
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            with patch.object(RUNNER, 'REPO', root), \
                    patch.object(RUNNER, 'fingerprint_files', side_effect=[{'fixture': 'before'}, {'fixture': 'after'}]), \
                    patch.object(RUNNER.subprocess, 'run', side_effect=responses), \
                    patch('sys.argv', ['runner', 'tests/FakeTest.php']), \
                    patch('sys.stdout', io.StringIO()), patch('sys.stderr', io.StringIO()):
                with self.assertRaises(SystemExit) as raised:
                    RUNNER.main()
            record = json.loads(next(root.rglob('*-result.json')).read_text(encoding='utf-8'))
            self.assertEqual(3, raised.exception.code)
            self.assertEqual(0, record['exit_code'])
            self.assertEqual(['fixture'], record['protected_files_changed'])

    def test_reverse_is_applied_once_by_phpunit_not_cancelled_by_double_reversal(self):
        responses = [subprocess.CompletedProcess([], 0, b'{}', b''),
                     subprocess.CompletedProcess([], 0, b'OK', b'')]
        selected = ['tests/Public', 'tests/Theory', 'tests/Admin']
        with tempfile.TemporaryDirectory() as temporary:
            with patch.object(RUNNER, 'REPO', Path(temporary)), \
                    patch.object(RUNNER, 'fingerprint_files', return_value={}), \
                    patch.object(RUNNER.subprocess, 'run', side_effect=responses) as run, \
                    patch('sys.argv', ['runner', '--reverse', *selected]), \
                    patch('sys.stdout', io.StringIO()), patch('sys.stderr', io.StringIO()):
                with self.assertRaises(SystemExit) as raised:
                    RUNNER.main()
            self.assertEqual(0, raised.exception.code)
            command = run.call_args_list[1].args[0]
            self.assertEqual(selected, command[2:5])
            self.assertEqual(1, command.count('--order-by=reverse'))

    def test_matrix_keeps_distinct_child_runtimes_and_reports_all_failures(self):
        responses = [subprocess.CompletedProcess([], 0, b'{}', b'')]
        responses += [subprocess.CompletedProcess([], code, b'child result', b'') for code in [0, 1, 0, 2]]
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            with patch.object(RUNNER, 'REPO', root), \
                    patch.object(RUNNER, 'fingerprint_files', return_value={}) as fingerprints, \
                    patch.object(RUNNER.subprocess, 'run', side_effect=responses) as process, \
                    patch('sys.argv', ['runner', '--matrix', 'individual']), \
                    patch('sys.stdout', io.StringIO()), patch('sys.stderr', io.StringIO()):
                with self.assertRaises(SystemExit) as raised:
                    RUNNER.main()
            record = json.loads(next(root.rglob('*-result.json')).read_text(encoding='utf-8'))
            self.assertEqual(1, raised.exception.code)
            self.assertEqual(2, fingerprints.call_count)
            self.assertEqual([0, 1, 0, 2], [run['exit_code'] for run in record['runs']])
            self.assertEqual(4, len({run['runtime'] for run in record['runs']}))
            scans = [call.kwargs['env']['PHP_INI_SCAN_DIR'] for call in process.call_args_list]
            self.assertEqual(1, len(set(scans)), 'The startup override must reach preflight and every PHPUnit child.')
            self.assertTrue(scans[0].endswith(str(Path(record['runtime']) / 'php-ini')))

    def test_courses_matrix_runs_individual_combined_and_reversed_in_private_children(self):
        responses = [subprocess.CompletedProcess([], 0, b'{}', b'')]
        responses += [subprocess.CompletedProcess([], 0, b'OK', b'') for _ in range(4)]
        with tempfile.TemporaryDirectory() as temporary:
            root = Path(temporary)
            with patch.object(RUNNER, 'REPO', root), \
                    patch.object(RUNNER, 'fingerprint_files', return_value={'actual': 'same'}) as fingerprints, \
                    patch.object(RUNNER.subprocess, 'run', side_effect=responses), \
                    patch('sys.argv', ['runner', '--matrix', 'courses']), \
                    patch('sys.stdout', io.StringIO()), patch('sys.stderr', io.StringIO()):
                with self.assertRaises(SystemExit) as raised: RUNNER.main()
            self.assertEqual(0, raised.exception.code)
            record = json.loads(next(root.rglob('*-result.json')).read_text(encoding='utf-8'))
            self.assertEqual(2, fingerprints.call_count)
            self.assertEqual(['blueprint', 'landing', 'courses-combined', 'courses-reversed'], [run['name'] for run in record['runs']])
            self.assertEqual(4, len({run['runtime'] for run in record['runs']}))
            for index, run in enumerate(record['runs']):
                self.assertEqual(index == 3, '--order-by=reverse' in run['command'])
                self.assertIn('--log-junit', run['command'])


if __name__ == '__main__':
    unittest.main()
