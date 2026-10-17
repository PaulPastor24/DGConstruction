# System review — 17 October 2026

## Scope and results

Reviewed the Laravel routes, authentication, account access, presence tracking,
profile handling, migrations, test results, and frontend build. This is a local
code and automated verification review, not a guarantee that every workflow is
error free. Live hosting, email delivery, Google OAuth, passkey hardware, camera
recognition, mobile native builds, and concurrent real-user workflows were not
exercised.

Final suite: **122 tests: 114 passed, 7 failed, 1 skipped; 449 assertions; no errors.**
Initial suite: 119 tests, 42 errors, 5 failures, 1 skipped. Repairing the migration
allowed previously blocked tests to execute and reveal further failures.

Passed checks:

- Production frontend build (`npm.cmd run build`). Vite warns about large bundles.
- Application PHP syntax checks across app, routes, config, and migrations.
- Laravel route caching and Blade view compilation. Generated caches were cleared.
- All registered controller actions resolve to existing methods.
- Focused authentication, presence, and profile regression tests: 19 passed.
- Full-suite password reset, registration, and verification tests pass after repairs.

## Repairs made

- Role middleware now accepts every Laravel middleware role argument. Previously
  `role:engineer,admin,administrator` checked only `engineer`.
- Disabled accounts are rejected by password login and role-protected routes,
  including existing sessions accessing those routes.
- Profile email uniqueness ignores the correct `user_id` primary key. Profile
  updates also write first and last names and avoid the removed production `name`
  column.
- Duplicate milestone completion routes now share one POST/PATCH registration.
  Removed worker registrations that were overwritten by later identical routes.
  Renamed the worker passkey login route to avoid the package's `passkeys.login`
  name. Its URL remains `/passkeys/login`.
- Attendance compatibility backfill now uses portable subqueries instead of
  MySQL-only UPDATE JOIN statements, allowing SQLite migrations to complete.
- Added the missing password reset token table migration. Password reset requests
  previously failed because the configured table did not exist.
- Guest `/register` now redirects to login; guest POST registration returns 403.
  Existing user-creation validation already requires an administrator. Previously
  the guest page rendered an admin layout and crashed, while POST was forbidden.
- Corrected verification test URLs to use the model's actual primary key and added
  regression coverage for admin aliases and disabled accounts.

## Remaining failures and risks

1. **Inventory contract mismatch — 3 failures.** Tests expect receiving by a new
   material name or a supplied Material model. Current validation requires
   `material_id`. The CRUD test also expects an initial stock value supplied to
   `storeMaterial` to be saved, but that method deliberately omits initial stock.
   Decide whether those older entry paths should be supported or update tests to
   the intended create-then-receive workflow.
2. **Client UI expectations — 2 failures.** Dashboard and My Projects return HTTP
   200, but tests expect a `project-detail-trigger` element absent from the current
   templates. Verify intended detail navigation in a browser before deciding
   whether the controls or test expectations need repair.
3. **Supervisor phase status contract — 2 failures.** Tests expect HTTP 422 for
   unchanged values. Both controller actions now always return HTTP 403 and direct
   supervisors to submit an accomplishment report. Preserve this restriction
   unless the product's approval policy changes; align tests with that policy.
4. **Inventory consistency risk found by inspection.** `receiveStock` reads and
   saves a stock total, then creates a delivery outside a transaction. Concurrent
   receipts can overwrite increments; a delivery insert failure can leave stock
   updated without its delivery record. Allocation also performs several related
   writes without a surrounding transaction. Use transactions and appropriate row
   locking/atomic updates before relying on simultaneous inventory edits.
5. **Native mobile verification gap.** The Capacitor package has only the default
   failing placeholder test script. Android/iOS build and device behavior remain
   unverified.
6. **Frontend performance.** Vite reports large timeline and face-recognition
   bundles. The build succeeds, but slower devices may experience longer loads.

## Deployment

Deploy the source changes and run `php artisan migrate --force` through the normal
deployment process to create the password reset token table. No migration was run
against the application's configured live database during this review. Verify
actual email delivery after deployment. The attendance migration repair affects
fresh/pending migration runs; already-recorded migrations do not rerun.

Raw full-suite output is available locally at
`storage/logs/system-review-tests.txt` (ignored runtime data).
