<?php

namespace Tests\Support;

use Closure;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CourseFixtureSnapshot
{
    /** Immutable reference only; never attach this file as a writable database. */
    private static ?array $snapshot = null;

    public static function restore(Closure $seed): void
    {
        $db = DB::connection();
        IsolatedTestEnvironment::assertSafeDatabase($db);
        $root = IsolatedTestEnvironment::prepare();
        $exports = $root.'/app/course-exports-'.bin2hex(random_bytes(8));
        mkdir($exports);
        IsolatedTestEnvironment::assertOwnedPath($exports);
        config(['questions.export_path' => $exports]);

        if (self::$snapshot === null) {
            $start = hrtime(true);
            $seed();
            $path = $root.'/course-fixture-'.bin2hex(random_bytes(8)).'.sqlite';
            $db->getPdo()->exec('VACUUM main INTO '.$db->getPdo()->quote($path));
            IsolatedTestEnvironment::assertOwnedPath($path);
            self::$snapshot = ['path' => $path, 'hash' => hash_file('sha256', $path)];
            fwrite(STDERR, sprintf("COURSE_FIXTURE_BUILD %.3fs; 41 real V2 seeders\n", (hrtime(true) - $start) / 1e9));
        }
        $snapshot = self::$snapshot;
        IsolatedTestEnvironment::assertOwnedPath($snapshot['path']);
        if (! hash_equals($snapshot['hash'], hash_file('sha256', $snapshot['path']))) {
            throw new RuntimeException('Immutable course fixture snapshot was modified.');
        }
        $copy = $root.'/course-test-'.bin2hex(random_bytes(8)).'.sqlite';
        if (file_exists($copy) || ! copy($snapshot['path'], $copy)) {
            throw new RuntimeException('Cannot create independent course fixture database.');
        }
        IsolatedTestEnvironment::assertOwnedPath($copy);
        DB::purge('sqlite');
        config(['database.connections.sqlite.database' => $copy]);
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        // Laravel creates a fresh application/cache/session per method. Snapshot
        // contains only DB data; no service, session, registry or export is reused.
    }
}
