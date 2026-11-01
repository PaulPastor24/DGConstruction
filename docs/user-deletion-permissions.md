# User deletion permission

Only the designated Giezelle Esta account, identified during migration by `dgcon.ad@gmail.com`, may delete other user accounts. Other active Engineer/Admin/Administrator accounts retain user creation and ordinary account management but cannot delete accounts. The user list hides their delete controls, and the deletion endpoint returns HTTP 403 for unauthorized requests.

The `can_delete_users` database flag is assigned once by migration to the existing designated management account. It is excluded from mass assignment and is not accepted by the user creation or update actions. Changing an email address does not grant or transfer deletion permission. Other administrators cannot edit or disable the designated account, including changing its password or email through user management.

Self-deletion is blocked through both user management and the profile deletion endpoint. Existing related-record checks and the requirement to retain at least one active management account remain in place. Profile pages explain that account deletion is managed by the designated administrator.

## Deployment

Deploy the application changes and run:

```sh
php artisan migrate --force
```

Migration: `database/migrations/2026_10_31_000001_add_user_deletion_permission.php`.

The designated account must already exist with email `dgcon.ad@gmail.com` and role `engineer`, `admin`, or `administrator` when the migration runs. If it does not, no account is granted deletion permission. New users default to having no deletion permission. The migration does not create accounts or change passwords.

## Verification

```sh
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --filter 'UserManagementPresenceTest|ProfileTest'
```

Tests cover authorized deletion, denied requests from other management roles, user creation, attempted permission escalation, designated-account protection, profile self-deletion, migration assignment, and retention of the last active administrator.
