# Even project distribution among four supervisors

The command below distributes all non-archived projects among exactly four active Site Supervisor accounts. This includes completed projects for assignment/history purposes; completed projects remain excluded from milestone and phase management as required by the separate visibility rule.

Projects are ordered by ID and assigned in rotation to supervisors ordered by user ID. Each project receives one active supervisor; totals differ by at most one. For example, 22 projects are divided as 6, 6, 5, and 5. Existing assignment rows remain as history, with previous assignments deactivated. Reports, attendance, materials, workers, and project records are not moved or recreated. The command can be repeated without creating duplicate active assignments.

## Apply to the deployed system

Deploy the updated application code, then run these commands from the Laravel application directory on the server:

```sh
php artisan projects:distribute-supervisors
php artisan projects:distribute-supervisors --apply
```

The first command previews the four account names, emails, and resulting project counts. The second saves the assignments. The command refuses to proceed if exactly four active supervisors are not present. It does not create or enable user accounts. Confirm the preview lists the four intended accounts from User Management before applying.

The local database inspected during implementation contains only one project and the `supervisor@dg-corp.ph` supervisor account. Its preview correctly refused to run, and no local assignments were changed. This does not update the separate deployed database automatically.

## Newly created projects

When an administrator leaves Assigned Supervisor on the automatic option, the new project is assigned to the active Site Supervisor with the lowest number of distinct non-archived project assignments. Ties use user ID order. An explicit selection of an active supervisor is respected. Inactive accounts and non-supervisor accounts are rejected as explicit selections. If there are no active supervisors, the existing unassigned-project behavior is retained.

Automatic assignment uses the current pool of active Site Supervisors. Adding or disabling supervisors changes that pool for subsequent projects; it does not automatically redistribute existing projects. To rebalance existing projects among four active supervisors, rerun the distribution command.

Supervisor screens and access checks use active assignment rows, so deactivated historical assignments no longer grant project visibility or access. Newly assigned supervisors can view the project's existing operational records through the applicable modules; no demonstration data is fabricated. Where a project has no reports, workers, or materials, those sections remain empty until actual records are added. Role restrictions and the rules for completed/archived projects still apply.

## Verification

```sh
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --filter 'ProjectSupervisorDistributionTest|CompletedProjectVisibilityTest|SupervisorReportFilteringTest|TimelineViewsTest'
```

The distribution tests cover preview without changes, an even split, archived-project preservation, assignment history, repeat runs, rejection when the supervisor count differs from four, automatic assignment through project creation, explicit selection, invalid selections, and removal of visibility from previous supervisors.
