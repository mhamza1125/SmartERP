# Audit questions for the owner

Note (2026-11-26): this file did not exist when the cleanup audit ran, although docs/OWNER_ACTIONS.md,
docs/STABILISATION_2026-11-25.md and claude-memory.md link to it for Q17 and Q19-Q24 (and to
docs/AUDIT_2026-11-25.md, also missing). Those earlier questions are not reproduced here because their
text was not found anywhere in the repository or its history. Numbering continues at Q25 to avoid clashes.

## Cleanup audit 2026-11-26 (docs/CLEANUP_REPORT_2026-11-26.md)

Each question has a recommended default. If you do not answer, the default is what the removal pass will propose.

**Q25. Theme demo copy (`resources/views/Template/`, 898 files, 40 MB, finding D-028).** Nothing in the app reads it; it is the purchased theme's demo pages plus an old copy of `public/assets`. CLAUDE.md says "do not modify". Delete it from the repository?
Default: **yes, delete it**, keep the original theme download outside git, and update CLAUDE.md / claude-rules §6.

**Q26. Delivery return "Edit" (D-014).** The Edit button on delivery returns opens a page whose view file never existed, so it fails (500). Build the edit page, or remove the Edit buttons and the edit/update routes (corrections = delete and enter again, as for stock adjustments)?
Default: **remove the Edit buttons and routes now**; build an edit page later only if users need it.

**Q27. Unused theme files in `public/assets` (D-025, D-026, D-027): 34 page scripts, 24 bundles (580 files, 14.3 MB), 87 demo images and 2 old logos (~9.8 MB).** Delete them?
Default: **yes**, in one commit, after a quick click-through of every menu on a dev copy.

**Q28. Unused packages and build tooling (D-035, D-036).** Vite, laravel-vite-plugin, axios, `resources/js`, `resources/css`, `vite.config.js` are not used by any page; `guzzlehttp/guzzle` and `laravel/sail` are not used. Remove them and change claude-rules §14 ("Vite handles all asset bundling", "AJAX via Axios")?
Default: **remove sail and the Vite/axios files; keep guzzle** (Laravel's HTTP client needs it if a future feature calls an outside service); update the rules to say jQuery + `public/assets`.

**Q29. `public/assets/js/print.js` (D-023).** None of its functions is called any more; its only effect is to strip auto-print scripts on page load. Remove it together with `print.css` and the `company/data` route?
Default: **yes**, after checking on a dev copy that no print opens the print dialog by itself afterwards.

**Q30. GitHub workflows (D-037).** Three workflows belong to the Laravel framework's own repository; `tests.yml` runs nightly and on every pull request with SQLite and cannot pass with this project's MySQL test guard. Delete all four, or replace `tests.yml` with one that builds `smarterp_test`?
Default: **delete all four** (tests run locally per docs/TESTING.md).

**Q31. Uploaded files in git (R-05).** git tracks `public/resources/att2000.mdb` and 86 uploaded customer / employee / vendor / product files. Stop tracking uploads (add `public/resources/*` to `.gitignore`, keep `.htaccess`)? The files stay on disk.
Default: **yes, stop tracking new uploads**; removing them from history is decided together with Q17.

**Q32. Is the GitHub repository (`origin`) public or private, and who has access?** It holds the code, the uploads above and (in history) 8 database dumps. This decides how urgent OWNER_ACTIONS §3 (password changes) is.
Default: **treat it as exposed** and follow OWNER_ACTIONS §3 until you confirm it was always private with a known list of collaborators.

**Q33. `DELETE /company/{id}` (D-013, R-01).** No screen uses it; it deletes the single company record that every print needs, without the usual deletion check. Remove the route and method?
Default: **yes, remove**.

**Q34. Five pages with no link (D-009 to D-012):** `/bank/{id}` (blank page), `/editBank/{id}` (error page), `/ajaxPayee`, `/ptc/{id}/receive` (the list page now links per issuance), `/company/{id}` (+ view `companyInfo`). Does anyone use these addresses directly (bookmarks, another program)?
Default: **no; remove them**.

**Q35. Role constants (D-015, DD-04).** `User::ROLE_MANAGER = 2` and `ROLE_ACCOUNTANT = 3` are unused, and id 2 is actually "Store Incharge" (roles: 1 Admin, 2 Store Incharge, 3 Accountant, 4 Export, 5 Director, 6 Manager). Remove the two constants and correct CLAUDE.md / claude-memory to "roles and permissions are data; only Admin (id 1) is special"?
Default: **yes**.

**Q36. Commented-out screen parts (D-031): 32 blocks, about 400 lines in 30 views.** Do you want any of them back later (e.g. extra columns on order / receive / vendor pages, the theme settings sidebar)? The dated block "hidden for now" in the PTC print stays either way.
Default: **delete all 32** (git history keeps them).

**Q37. `public/project.html` (D-029).** A public "project documentation" page for an older version of the system with links to `127.0.0.1:8000`. Delete it?
Default: **yes, delete**.

**Q38. Missing audit documents (DD-01).** Where are `docs/AUDIT_2026-11-25.md` and the original Q17 / Q19-Q24? If they exist outside the repository, add them; otherwise the links should be removed from the docs.
Default: **remove the dead links** and restate any still-open question (Q20 stock number index, Q23 dependency patches) in this file.
