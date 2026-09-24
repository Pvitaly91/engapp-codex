# Temporary release with bundled dependencies

Branch: `codex/temp-production-vendor-20260925`.
Base: `41820a2be` (application plus production Vite build).

This temporary branch includes production `vendor` installed from the committed
`composer.lock` with `--no-dev --prefer-dist --optimize-autoloader --no-scripts
--no-plugins`. It contains no environment file, database dump, Composer credentials,
or generated Laravel caches. It requires PHP 8.5 for both web and CLI.

From the project directory on the hosting server:

```bash
git fetch origin &&
git reset --hard origin/codex/temp-production-vendor-20260925 &&
/opt/alt/php85/usr/bin/php tools/deployment/activate-vendor-release.php
```

The PHP command clears only generated bootstrap caches and compiled views, then
discovers the installed packages. This is necessary when replacing an older
Laravel/Livewire installation. Composer, npm, database migrations and seeders
are not run on the server. The `public/build` manifest and its assets are tracked.

Do not merge this temporary vendor snapshot into `main`. Keep dependency changes
in the normal source branch and rebuild a release snapshot when required.
