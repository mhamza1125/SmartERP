# Owner actions after the 2026-11-25 stabilisation work

Things only you can do (production server, passwords, data). In order of urgency.

## 1. Before deploying this code

- [ ] **Take a backup** of the database and `public/resources` ([BACKUP.md](BACKUP.md)).
- [ ] Run `php artisan migrate` on the target database. Expected:
  - `2026_11_25_000001` (department break) and `2026_11_25_100000` (lookup indexes) and the UNIQUE indexes
    on order no, job no and delivery no apply;
  - `2026_11_25_100004` (UNIQUE stock no) **stops with a message listing 10 duplicate stock numbers**. That is
    intended: it changes nothing. Leave it until question Q20 in [AUDIT_QUESTIONS.md](AUDIT_QUESTIONS.md) is
    answered (the message will repeat on every `migrate` until then).
  - If one of the other UNIQUE migrations stops, it lists the duplicate numbers: correct them by hand, run again.
- [ ] Clear caches after deploying: `php artisan optimize:clear` (the routes depend on `ALLOW_DATA_RESET`, see 4).

## 2. Production `.env` (D11)

- [ ] `APP_ENV=production`, `APP_DEBUG=false` (debug pages show code, queries and settings to anyone).
- [ ] A dedicated database user for the app with rights on `smarterp_erp` only; **never `root`, never an empty
  password**. The local `.env` uses root without a password: fine on a development PC only.
- [ ] A new `APP_KEY` for production (`php artisan key:generate` on the server, once; changing it later logs
  everybody out).
- [ ] `ALLOW_DATA_RESET=false` (or leave it out; see 4).

## 3. Was the repository or its history ever shared? (Q17)

The git history contains 8 database dumps (`database/smarterp*.sql`, removed from the tree in commit `19e671f`
but still in history). They probably contain user password hashes and business data. Nothing was rewritten
(D12); `.gitignore` now keeps new dumps out of git (`database/*.sql`; the schema file in `database/schema/`
stays tracked).

- [ ] If the repository was ever pushed or copied anywhere (GitHub, a client, a USB copy): **change every
  user's password** in the app and any database password that was in use when those dumps were made.
- [ ] Whether to remove the dumps from history (e.g. `git filter-repo`) is your decision; it rewrites every
  commit hash and every clone must be re-made.

## 4. Settings > Reset Test Data (D10)

The tool (it deletes all transactional data) now exists only when `ALLOW_DATA_RESET=true` in `.env`;
otherwise its menu entry and URLs (`/settings/reset-data`) do not exist. Your local `.env` does not have the
variable, so the menu entry is gone locally too.

- [ ] Keep it `false`. To use it once: take a backup, set `ALLOW_DATA_RESET=true`, run
  `php artisan optimize:clear`, use it, set it back to `false`, `php artisan optimize:clear` again.

## 5. Check over HTTP that uploads cannot run as code

During the audit the development address `http://05_smarterp.test` answered 404 for every page, so these
checks could not be made from here. On the real server (or once the dev vhost works):

- [ ] Open `https://<your server>/resources/att2000.mdb` → must answer **403 Forbidden** (not download).
- [ ] Put a file `test.php` containing `<?php echo 'RUNS';` into `public/resources/product/` by hand (not
  through the app), open `https://<your server>/resources/product/test.php` → must answer **403**, never show
  `RUNS`. Delete the test file afterwards.
- [ ] Open any existing product image → must still display.
- [ ] If every page answers **500** after deploying, the server does not allow the `Require` lines in
  `public/.htaccess` / `public/resources/.htaccess` (Apache `AllowOverride` without `AuthConfig`). Ask the
  hosting provider to allow `AuthConfig`, or tell the developer.

## 6. Data fixes

- [ ] After a backup, work through [DATA_FIXES.md](DATA_FIXES.md) in order: A (hidden product lines), B (your
  decision on product 407 at stage 146), C (batch rebuild), D (orphan entry), E (review only), F (only after Q20).

## 7. Decisions waiting for you

- [ ] Questions Q19-Q24 in [AUDIT_QUESTIONS.md](AUDIT_QUESTIONS.md), in particular Q20 (stock number index) and
  Q23 (dependency patches: nothing was installed).
- [ ] The attendance-machine path: `ATTENDANCE_DB_PATH` / Settings > Company. The deny rule for `.mdb` files
  only blocks downloads over HTTP; the import reads the file from disk and is not affected.
