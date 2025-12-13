## Seed Runs (V2) Livewire module

Created a lightweight Livewire v2 version of the admin Seed Runs screen.

### New/updated files
- `app/Services/SeedRuns/SeedRunsService.php` — shared helpers wrapping existing controller logic for overview/run actions.
- `app/Livewire/Admin/SeedRuns/Index.php` and `resources/views/livewire/admin/seed-runs/index.blade.php` — Livewire SPA page.
- `resources/views/layouts/app.blade.php` — Livewire assets + navigation link.
- `routes/web.php` — new `/admin/seed-runs-v2` route.

### Manual test checklist
1) Open `/admin/seed-runs-v2` (authenticated) and verify the page renders without a full reload.
2) Click “Виконати всі невиконані” and confirm overview refreshes.
3) Run/mark an individual pending seeder and ensure it moves to executed list.
4) Use the search box to filter executed seeders in real time.
5) Use the refresh button to re-sync data with the legacy controller state.
