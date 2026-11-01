# APPENDIX C
## SOFTWARE REGRESSION TESTING

This appendix presents the actual results of an automated regression test run for the Construction Project Service and Operation Management System for D&G Dev't Corp. The results were recorded on November 1, 2026, using PHPUnit 11.5.55 and PHP 8.2.12. Tests used the testing configuration in `phpunit.xml`, including an in-memory SQLite database, an array mailer, and synchronous queues. The results describe the local application code and do not establish production MySQL behavior, physical-device compatibility, live email delivery, or user acceptance.

The fourteen checklist items supplied for this appendix describe broader workflows than some existing automated tests cover. Table C.1 distinguishes passing automated coverage from partially verified or unexecuted checklist procedures. A passing automated check must not be represented as a completed manual browser or device test.

### Table C.1. Checklist Verification Against Actual Automated Results

| Test ID | Function tested | Actual result and scope | Status against supplied procedure |
| --- | --- | --- | --- |
| REG-01 | User authentication | Valid Engineer and Client login tests passed with dashboard redirects. Invalid-password rejection, staff exclusion from the client portal, and logout also passed. Successful login for every management alias and Supervisor was not independently exercised by the login tests. | Partially verified |
| REG-02 | Role-based access control | Supervisor and Client requests to the management timeline were denied or redirected. Client worker-allocation requests and Supervisor requests to administrator-only worker registration routes were also rejected. | Passed — automated checks |
| REG-03 | Accomplishment report submission | An assigned Supervisor submitted a report with a private PDF; the report and attachment were saved and authorized downloads succeeded. Requests after assignment revocation were rejected. Appearance of that newly submitted report in the management review queue was not separately asserted. | Partially verified |
| REG-04 | Accomplishment report approval | Approval endpoint checks and report image handling passed. Approval without a supplied percentage preserved stored phase completion. A separate rejection test verified reviewer, timestamp, status, and remarks. The complete approval-plus-supplied-percentage phase-update workflow was not independently verified by these assertions. | Partially verified |
| REG-05 | Construction timeline monitoring | Timeline views and data checks passed. Milestone-derived phase progress, administrative overrides, and completion of the final milestone updating phase and project completion passed. Visual interaction in an actual browser was not performed. | Passed — automated checks |
| REG-06 | Camera-assisted attendance | Backend matching with synthetic descriptor samples, encrypted enrollment storage, required supervisor confirmation, proof expiry, and proof consumption passed. Physical camera capture and recognition of real workers were not tested. | Partially verified |
| REG-07 | Duplicate attendance prevention | Duplicate Time In and repeated manual actions were rejected. Manual Time In could not overwrite an existing record or create a second same-day entry through another project. Administrator duplicate-entry checks, including older deployment-linked records, passed. | Passed — automated checks |
| REG-08 | Attendance timekeeping | Tests adding Break In and Time Out with fresh face proofs passed. Manual Break Out was saved, a duplicate Break Out was rejected, and original Time In was preserved. This is endpoint-level verification, not a manual device test. | Passed — automated checks |
| REG-09 | Material request and approval | Existing tests exercised material-request listing/pagination, but no dedicated test was found executing the supplied Supervisor request → management approval → stock/allocation-update sequence. | Not executed as specified |
| REG-10 | Material inventory and usage | All 11 Supervisor material-usage tests passed, including project-allocation limits and prevention of a second warehouse deduction. However, 4 of 11 inventory tests failed, covering missing view data and stock-receipt expectations. A blanket inventory-and-usage pass is unsupported. | Partially verified — inventory failures remain |
| REG-11 | Project worker allocation | All 8 allocation tests passed, covering assignment, bulk assignment, transfer, preservation of historical attendance, invalid requests, and Client access rejection. | Passed — automated checks |
| REG-12 | Management dashboard | Three management dashboard summary tests passed: active worker count, missing-worker-table fallback, and the ten latest reports. A separate management project-details test passed. The complete project/material-update → dashboard-refresh comparison was not independently tested. | Partially verified |
| REG-13 | Client dashboard access | Six Client/dashboard interaction tests passed, including assigned-project rendering, project status displays, pagination, and selected-project report filtering. Rejection of an attempt to view another client's project was not directly asserted by these tests. | Partially verified |
| REG-14 | Report publication and visibility | Client report-list tests used reports already marked approved and published. No dedicated test was found comparing access before approval, after approval but before publication, and after publication. | Not executed as specified |

The statuses above do not imply that every possible access-control path, timekeeping combination, or timeline interaction has been tested. They describe the observed automated evidence for the supplied checklist.

### C.2 Actual Automated Suite Summary

| Testing indicator | Actual result |
| --- | ---: |
| Total test cases reported by PHPUnit | 185 |
| Executed cases, excluding skipped | 184 |
| Passed | 178 |
| Failed assertions/test cases | 6 |
| Errors | 0 |
| Skipped | 1 |
| Assertions | 1,138 |
| Elapsed time reported by PHPUnit | 52.241 seconds |
| Pass rate among executed cases | 96.74% (178 / 184) |
| Passed as a proportion of all reported cases | 96.22% (178 / 185) |

The full suite did not pass. The 185 automated cases are not the same denominator as the fourteen checklist procedures. For Table C.1, five items have passing automated checks, seven are partially verified, and two were not executed as specified. Manual completion and pass rates for the original fourteen procedures cannot be calculated from this automated run.

### C.3 Actual Failures

| Test | Observed result |
| --- | --- |
| `InventoryCrudTest::test_inventory_supplies_tool_ledger_paginators_when_tables_are_missing` | Expected view-data key `activeToolLoans` was absent. |
| `InventoryCrudTest::test_receive_stock_can_create_new_material_when_name_is_provided` | Expected success message `Stock received successfully.` was not present. |
| `InventoryCrudTest::test_receive_stock_requires_material_selection_or_name` | Expected material-selection/name validation message was absent. |
| `InventoryCrudTest::test_inventory_crud_actions_work` | Expected `Stock received successfully.`; the observed session message was `Material updated successfully.` |
| `SupervisorPhaseUpdateTest::test_it_rejects_no_change_phase_progress_update` | Expected HTTP 422; received HTTP 403. |
| `SupervisorPhaseUpdateTest::test_it_rejects_no_change_phase_status_update` | Expected HTTP 422; received HTTP 403. |

These are observed mismatches between tests and implementation. This run alone does not establish whether each mismatch requires a code correction or an updated test expectation. In particular, HTTP 403 can reflect the intended restriction on direct Supervisor phase changes; the expected product behavior must determine the resolution.

### C.4 Skipped Test

`MilestoneModalUiTest::test_admin_timeline_modal_renders_enhanced_phase_guidance` was skipped. It must not be counted as passed.

### C.5 Test Evidence and Reproduction

Command executed:

```sh
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --log-junit storage/logs/appendix-c-regression-junit.xml
```

The machine-readable run log is `storage/logs/appendix-c-regression-junit.xml`. Table C.1 is based on the assertions in the following existing test files, not solely on test method names:

- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/TimelineViewsTest.php`
- `tests/Feature/TimelineControllerTest.php`
- `tests/Feature/ProgressConsistencyTest.php`
- `tests/Unit/ProgressTrackingTest.php`
- `tests/Feature/ReportRouteTest.php`
- `tests/Feature/ReportUploadsTest.php`
- `tests/Feature/LocalFaceAttendanceTest.php`
- `tests/Feature/InventoryCrudTest.php`
- `tests/Feature/SupervisorMaterialUsageTest.php`
- `tests/Feature/WorkerAllocationTest.php`
- `tests/Feature/AdminDashboardSummaryTest.php`
- `tests/Feature/ClientDashboardInteractionsTest.php`

No production migration, physical-camera test, live email test, or manual browser workflow was performed for this report. Application behavior and test expectations were not modified to obtain these results.
