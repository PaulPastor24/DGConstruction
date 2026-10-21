# System Functionality and Workflow Guide

**Purpose:** Describe how the current system works: its roles, features, business logic, and the connections between users and modules.

**Basis:** Review of the Laravel routes, controllers, models, migrations, and main role-specific views in this repository, as of October 20, 2026. This guide describes behavior represented in the code; deployed configuration can affect features such as scheduled jobs, email, and biometric authentication.

## 1. System at a glance

The system is a role-based construction project monitoring and operations application. Its main users are:

- **Engineer/Admin/Administrator:** Manages projects and operational records. The available routes are grouped for these roles, although some actions have narrower checks; for example, the report approval action currently checks for the `engineer` role.
- **Site Supervisor:** Works with assigned projects, worker attendance, material requests and usage, and accomplishment reports.
- **Project Client:** Views projects associated with their client account, project progress, timeline information, and reports approved and published for client viewing.
- **Public visitor:** Can view public landing-page/gallery content. This is separate from authenticated project dashboards.

After login, users are directed to a dashboard appropriate to their role. Access to role-specific routes is protected by authentication and role middleware.

## 2. Main workflows by role

### Engineer/Admin/Administrator

1. **Set up a project.** Create a project, select its client, enter project details and dates, and optionally assign a supervisor. Project creation can notify the assigned supervisor, client, and administrators.
2. **Configure the work plan.** Add construction phases and milestones, including schedule and status information. Monitor project schedules through the management timeline.
3. **Monitor progress.** Review project, phase, milestone, report, and operational information through management pages and dashboards. Phase and milestone information can be exported where export actions are available.
4. **Review accomplishment reports.** Inspect reports submitted by supervisors, evaluate them, and approve or return them for revision. Approved reports may be prepared or edited for client viewing and explicitly published to the client.
5. **Manage attendance.** View and maintain attendance records, update worker schedules, and manage attendance schedule rules. Attendance reports can be previewed and sent; a daily attendance email is also scheduled.
6. **Manage inventory.** Create and edit material records; receive stock; allocate materials to projects; and review supervisor material requests, usage records, and inventory levels.
7. **Manage tools/equipment.** Record tools or equipment, including optional purchase date and purchase price, issue them to workers, record returns, mark them lost, and delete eligible records.
8. **Manage other operations.** Review notifications and alerts, manage user accounts, update profile settings, and manage public landing-gallery content. Projects can be archived and restored.

### Site Supervisor

1. Select or work within a project assigned to them.
2. View assigned-project dashboards, progress, phases, and timelines.
3. Record worker attendance. Workers can be enrolled for browser/device biometric authentication (WebAuthn/passkey, such as a supported fingerprint reader, Face ID, or Touch ID). Manual attendance is also supported when biometric verification is unavailable; records indicate whether biometric matching was used.
4. Submit a material request for an assigned project. Requests are sent for administrator review and can be viewed by status.
5. Record material usage against a project and its construction phase. The record can include quantity, date, remarks, and an optional site photo. Usage updates the material stock and project allocation quantities.
6. Submit an accomplishment report by selecting the project and phase, entering the report date and narrative, optionally adding accomplishment percentage and site images, and submitting it for review.
7. Track report review status and revise reports that are returned, as permitted by the workflow.
8. Receive notifications and manage profile information.

The supervisor routes for directly changing phase progress or status currently return messages that these direct updates are restricted; the intended progress-review path is through accomplishment reports and Engineer review.

### Project Client

1. Log in to the client account associated with the project.
2. View the client dashboard, select among associated projects, and review project status, progress, current phase, milestones, timeline, and recent client-visible updates.
3. View accomplishment reports only after they have been approved and published for the client.
4. Download available report PDFs or project image PDFs.
5. Receive and manage client notifications.

The code supports access for an authenticated client associated with a project; it does not show a separate client request-to-access workflow. Client pages filter reports to approved and published reports.

### Public visitor

Public pages include landing/gallery content and public gallery project/demo endpoints. These are not the authenticated client dashboard or a way to view private project records.

## 3. End-to-end process examples

### Project setup to client update

1. Engineer/Admin creates a project, links a client, and optionally assigns a supervisor.
2. Engineer/Admin adds the project's phases and milestones.
3. Supervisor submits accomplishment reports for the selected project phases.
4. Engineer reviews and approves or returns the report.
5. If appropriate, Engineer publishes the approved report to the client.
6. Client views permitted project progress and published report information in the client area.

### Material request and use

1. Supervisor selects an assigned project and requests a material and quantity.
2. Administrator reviews and approves or rejects the request.
3. Material stock can be received and allocated to a project.
4. Supervisor records actual material usage against a project phase.
5. The system updates stock/allocation quantities and keeps a material usage record.


## 5. Module-by-module functionality and logic

This section describes each module's behavior, rules, and dependencies. Where a feature depends on deployment configuration or a related record, that dependency is called out.

### Authentication, accounts, and profiles

- Login, logout, registration, password reset, email verification, password confirmation, and Google sign-in routes are present. User passkey routes are also registered by the authentication package.
- The authenticated `/dashboard` route redirects to the dashboard for the user's role: engineer/admin/administrator, supervisor, or client. Unknown roles are denied.
- Role-specific management routes require authentication and a matching role. The engineer/admin management route group includes `engineer`, `admin`, and `administrator`; some individual operations are stricter. For instance, report approval checks specifically for `engineer`.
- Engineer/Admin users can create, edit, activate/deactivate, search, filter, and delete user accounts. User records carry role and active status; client accounts can be associated with a Client profile.
- Users can update profile details, profile photo where provided, and password. Supervisors and Engineer/Admin users have role-specific profile pages and actions.
- Public registration exists. The role that a public registrant receives is determined by the registration controller and configuration; role-specific management accounts are also managed through the Engineer/Admin user functions.

### Project lifecycle and assignments

- A project is associated with a Client and an Engineer (creator/manager). Supervisors are linked through a many-to-many assignment record with assignment date and active flag.
- Engineer/Admin users create, view, update, archive, restore, and delete projects. Creation can include project dates, location/details, status, image, client, and optional supervisor assignment.
- Project status is normalized to workflow values: Planning, In Progress, Completed, On Hold, or Archived. Legacy status variants are mapped for compatibility.
- The status model permits Planning → In Progress; In Progress → On Hold or Completed; On Hold → Planning; Completed remains Completed; Archived projects cannot transition through the normal status workflow. Status changes may be recorded with the actor and reason in project status history.
- A scheduled job can place stale ongoing projects On Hold after 14 days without project updates. See the automation table below.
- Archiving is separate from ordinary active-project views: many supervisor and client project queries omit archived projects. Restoring returns an archived project to normal project management.
- Creating a project can trigger notifications for the assigned supervisor, associated client, and Engineer/Admin users. These notifications are separate recipient-specific records.

### Construction phases, dependencies, milestones, and progress

- Each project can have ordered construction phases. A phase may store planned and actual dates, status, completion percentage, notes, a delay reason, and a dependency on another phase.
- Phase dependency links are self-referential: a phase can depend on another phase. The model checks for a dependency cycle and indicates readiness to start based on the dependency being completed.
- Phase statuses include Not Started, In Progress, Delayed, and Completed. Status transitions are constrained by the current status, and a completed phase is locked against reopening through the normal transition helper.
- A phase's displayed progress is derived from completed milestones as a percentage when milestones exist. If no milestones exist, stored completion percentage is used. An Administrator can apply an audited progress override with a reason; where present, the override is used as the displayed progress.
- Project overall progress is the average of its phase progress values; phases with no progress remain in the average. It is not a weighted calculation based on budget, duration, or work size.
- A phase is ready for completion when progress is at least 100% and no milestone remains incomplete. Milestone synchronization can update phase progress and completion status; actual end date is filled when the phase completes.
- Each milestone belongs to one phase, and each phase belongs to one project. Milestones store name, dates, completion flag, delayed flag, and status. Display status prioritizes Completed, then Delayed, then the stored status.
- Engineer/Admin users can create, edit, delete, complete, or mark milestones delayed. Timeline views present project/phase schedules and derived progress. Engineer/Admin and supervisors can export phase data; client timeline routes expose data for that client's own projects.
- Supervisor phase screens show assigned-project information. The exposed supervisor phase update API currently responds that direct status/progress changes are restricted; report review and Engineer/Admin controls are the intended progress-control paths.
- Project status may synchronize from phases: phases in progress can make a project In Progress; all phases reaching full progress can make it Completed and set its actual end date. Archived projects are excluded from this synchronization.

### Accomplishment reporting and approval

- A report belongs to a project phase, and records its submitting Supervisor, report date/text, optional progress percentage and site images, approval state, reviewer/approver, remarks, and client-publication fields.
- A Supervisor selects the project and phase, enters a report date and narrative, and may attach images and a completion percentage. A phase already marked complete or at 100% cannot accept a new report. The Supervisor must be assigned to the selected project.
- New reports start Pending and are not client-published. Supervisors can filter/search/sort their reports and can update reports in permitted states (for example, pending or returned reports).
- Engineer review can approve a report or return/reject it for revision. Approval may include remarks, an edited client-facing narrative, edited/selected images, an explanation, and an approved accomplishment percentage.
- Approval and publication are distinct: an approved report is only shown to clients if it is also marked published. A report returned/rejected is not client-published.
- The approved accomplishment percentage is written back to the phase's stored completion percentage and can update phase status. Where milestones exist, however, the phase progress accessor derives displayed progress from completed milestones (unless an admin override applies), rather than simply displaying the report percentage. Report approval can therefore affect stored phase data/status while progress displays may still be milestone-derived.
- Milestone completion/delay actions can notify the associated client; delayed milestones can also notify administrators. Thus project progress, client updates, and notifications are connected, but report publication is a separate visibility control for accomplishment reports.
- Clients can view/download only reports belonging to their associated project that are approved and published. Admin/Supervisor report lists can include reports not yet client-visible, subject to role permissions.
- Report and project-image PDF download actions are present for applicable roles.

### Worker deployment and attendance

- A Worker is distinct from an authenticated User. Workers are deployed to projects through `project_workers`, which records project, worker, assignment role, dates, and active state. Attendance links to the worker and may link to a specific project deployment.
- Supervisors can add/register workers, edit worker details and profile images, re-enroll an active worker's biometric credential, and deactivate a worker. Deactivation removes the worker from the active roster while preserving historical records; it is not the same as deleting the worker's past attendance.
- Supervisor attendance is scoped to projects assigned to that Supervisor. Active workers can be enrolled or re-enrolled using a browser/device WebAuthn credential; a credential is verified server-side. Compatible devices may use fingerprint, Face ID, Touch ID, or another passkey authenticator. The server persists credential data, not a readable image of a fingerprint.
- Attendance can also be recorded manually when a biometric device/flow is unavailable. Attendance records indicate whether biometric verification matched; manual entries are marked unverified.
- Attendance supports time-in, break-out, break-in, and time-out actions. The system prevents duplicate or out-of-order actions in the attendance workflow and records date, times, status, remarks, recorder, worker, and deployment. Break-in depends on a recorded break-out; time-out depends on time-in, is limited by the worker's scheduled end time, and duplicate time-outs are rejected.
- Attendance status is calculated using the configured schedule logic; the admin interface supports filtering by date, project, status, biometric verification, and worker/search terms.
- Administrator functions include viewing, manually creating, editing, and deleting attendance entries, setting worker schedules, managing attendance schedule rules, previewing a daily report, and sending attendance reports by email.
- Overtime can be derived from time-out relative to the configured logic/cutoff and displayed as minutes/hours. This calculation is an attendance feature, not a payroll module.

### Materials, requests, allocation, and usage

- A Material record represents a catalog item with name, category, unit, current stock, minimum stock threshold, supplier, and description. Engineer/Admin users can create/edit/delete material records.
- Engineer/Admin users can receive stock, raising the material's current stock and recording a delivery. The current receipt form does not capture a purchase price; delivery total price is written as null.
- A ProjectMaterial row connects a material to a project and tracks planned/allocation quantity and used quantity. Material requests connect a requesting Supervisor, material, and project, and have Pending/Approved/Rejected states plus requested/approved quantities and reviewer details.
- Supervisor requests must target an existing project assigned to that Supervisor and a material. The requested quantity is checked against warehouse stock. The request begins Pending and notifies administrators.
- An administrator can approve a pending request for the requested or a lower quantity, provided warehouse stock is sufficient. Approval reduces warehouse stock, updates/creates the project's material allocation, records an internal delivery, and notifies the requesting Supervisor. Rejection records optional remarks and reviewer/date and notifies the Supervisor. A processed request cannot be processed again.
- Supervisors record usage by project, phase, material, quantity, date, optional remarks, and optional site photo. The project must be assigned to that Supervisor and the phase must belong to that project. Usage checks warehouse stock and, when a positive remaining allocation exists, checks that quantity against it; it then reduces warehouse stock, increments the allocation's used quantity, and creates a usage log.
- The Supervisor materials view summarizes assigned-project inventory, phase usage, recent usage, and requests. The Engineer/Admin inventory page includes inventory, allocation, usage, request, and tool views.
- Usage records are associated with the project, phase, material, and recording user. Material usage does not include unit-cost or expense tracking.

### Tools and equipment

- Tool/equipment records can store a unique code, name/category/type, unit, condition, purchase date, optional purchase price, description, and current status. Engineer/Admin users can create and delete records.
- Issuing a tool checks that it is not lost/retired and has no existing active loan, then records the worker, optional project, expected return date, condition at issue, and loan date; the tool becomes In Use.
- Returning a tool requires return condition and closes the active loan; the tool becomes Available and its current borrower is cleared.
- Marking a borrowed tool Lost records the loss against the loan and can create a monetary deduction record with a reason. Deduction records are linked to the tool, loan, worker, and approving user; they are not connected to a payroll workflow.

### Client portal, notifications, and public gallery

- Client project queries are constrained by the Client profile associated with the logged-in User. Selecting a project affects the dashboard/timeline/report context and is remembered in session.
- Client views include dashboard/project snapshots, project list/details, timeline/milestones, published reports/updates, PDFs, and notifications. The client timeline JSON endpoint verifies ownership by client association before returning project data.
- Admin, Supervisor, and Client notifications are stored separately and have per-recipient read state. Users can mark individual or all notifications read. Admin notifications are sent to active engineer/admin/administrator accounts.
- Project creation, material requests, report publication, and automated deadline/stock checks can create in-app notifications. Notifications are stored in the application; the daily attendance report is a separate email workflow.
- The public landing page uses active curated gallery items where present. If none exist, it can fall back to completed projects with images, then demo entries. Engineer/Admin users can manage gallery items, visibility, ordering, hero settings, and image/project associations. Public gallery endpoints are not client-private project dashboard endpoints.

## 6. Connections between the system's data and users

The relationship map shows the main associations and flows between users and system records; it is not an exhaustive database schema.

```text
User
├── Client profile ──< Projects
├── Engineer ─────────< Projects
├── Supervisor >──────< Projects (assignment pivot: project_supervisors)
├── submitted_by ─────< Accomplishment Reports
├── recorded_by ──────< Attendance / Material Usage
└── recipient ───────── Admin / Supervisor / Client Notifications

Project
├── belongs to Client and Engineer
├── >──< Supervisors (project_supervisors)
├── ──< Construction Phases
│       ├── depends on another Construction Phase (optional)
│       ├── ──< Milestones
│       └── ──< Accomplishment Reports
├── ──< Project Material Allocations >── Material
├── ──< Material Usage >── Material, Phase, recording User
├── ──< Material Requests >── Material, requesting/reviewing Users
└── ──< Worker Deployments >── Worker ──< Attendance Logs

Tool ──< Tool Loans >── Worker
  └──── optional Project on each loan
Tool Loan ── optional Tool Deduction (amount/reason/status)

Project / project image ── optional Landing Gallery entries
```

### Main information flows between roles

```text
Engineer/Admin
  ├─ creates project ──> associates Client and optionally assigns Supervisor
  ├─ defines phases/milestones ──> Project Timeline and progress calculations
  ├─ reviews Supervisor report ──> approves/returns ──> may publish to Client
  ├─ manages inventory ──> stock/allocation/request decisions
  ├─ manages worker attendance and tools/equipment
  └─ receives operational alerts/notifications

Supervisor
  ├─ assigned project ──> attendance for deployed Workers
  ├─ material request ──> Engineer/Admin review ──> stock/allocation update
  ├─ material usage ──> stock + project allocation + phase usage log
  └─ accomplishment report ──> Engineer review ──> phase/project progress

Client
  └─ associated project ──> permitted project status/timeline
                          └─ approved AND published reports only

Public visitor ──> landing/gallery content only
```

## 7. Automated jobs and notification connections

The following tasks are registered in the Laravel scheduler. Their actual execution depends on the production/server scheduler invoking Laravel's scheduler (for example, the configured cron entry); registration in code alone does not prove they ran in production.

| Scheduled time | Job | Main effect and recipients |
|---|---|---|
| Daily 07:00 | `notifications:scan` | Scans incomplete milestones due within three days or overdue and materials at/below minimum stock; creates in-app notifications for Engineer/Admin recipients. |
| Daily 08:00 | `notify:phases-deadlines` | Notifies assigned Supervisors about phase deadlines within three days and overdue phases. |
| Daily 18:00 | `attendance:daily-report` | Emails attendance records for the prior day to active Engineer/Admin/Administrator accounts with email addresses. |
| Daily 00:20 | `projects:auto-hold-stale` | Marks overdue incomplete milestones delayed, starts Not Started phases whose actual start date has arrived, and places ongoing projects with no update in 14 days On Hold with a reason. Uses `withoutOverlapping`. |

## 8. System behavior and deployment dependencies

- Scheduled jobs run only when the server invokes Laravel's scheduler. Attendance report email additionally depends on working mail configuration.
- Biometric/passkey authentication requires a supported browser/device and valid HTTPS origin/relying-party configuration. The system supports device authenticators; it does not mean every worker uses a dedicated fingerprint scanner.
- The behavior described here is based on the current code. Actual availability can vary with deployment configuration, database migrations, role permissions, and enabled integrations.
- Material receipts and approved material requests currently create delivery records with `total_price = null`; material cost entry is not supported. Tool/equipment purchase prices and loss-related deductions are separate records, not consolidated project cost accounting.
- The system does not implement full payroll, labor-cost capture, logistics-cost capture, budget-versus-actual accounting, or a unified all-category project expense dashboard.
