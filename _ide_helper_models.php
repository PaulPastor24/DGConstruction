<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $log_id
 * @property int|null $worker_id
 * @property int|null $deployment_id
 * @property int $recorded_by
 * @property \Illuminate\Support\Carbon $log_date
 * @property string|null $time_in
 * @property string|null $break_out
 * @property string|null $break_in
 * @property string|null $time_out
 * @property string $status
 * @property string|null $remarks
 * @property bool $biometric_matched
 * @property string $created_at
 * @property-read \App\Models\ProjectWorker|null $deployment
 * @property-read mixed $display_project
 * @property-read mixed $display_worker
 * @property-read \App\Models\User|null $recordedBy
 * @property-read \App\Models\Worker|null $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereBiometricMatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereBreakIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereBreakOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereDeploymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereLogDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTimeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTimeOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereWorkerId($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $client_id
 * @property int $user_id
 * @property string|null $company_name
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Project> $projects
 * @property-read int|null $projects_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUserId($value)
 */
	class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $client_id
 * @property string|null $type
 * @property string $title
 * @property string|null $message
 * @property array<array-key, mixed>|null $data
 * @property int|null $related_id
 * @property string|null $related_type
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereRelatedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientNotification whereUpdatedAt($value)
 */
	class ClientNotification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $phase_id
 * @property int $project_id
 * @property string $phase_name
 * @property int $phase_order
 * @property \Illuminate\Support\Carbon $planned_start_date
 * @property \Illuminate\Support\Carbon $planned_end_date
 * @property \Illuminate\Support\Carbon|null $actual_start_date
 * @property \Illuminate\Support\Carbon|null $actual_end_date
 * @property numeric $completion_percentage
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Milestone> $milestones
 * @property-read int|null $milestones_count
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereActualEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereActualStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereCompletionPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase wherePhaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase wherePhaseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase wherePhaseOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase wherePlannedEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase wherePlannedStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstructionPhase whereUpdatedAt($value)
 */
	class ConstructionPhase extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $category
 * @property string $unit
 * @property numeric $current_stock
 * @property numeric $minimum_stock_level
 * @property string|null $supplier
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialDelivery> $deliveries
 * @property-read int|null $deliveries_count
 * @property-read string $stock_badge_class
 * @property-read string $stock_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialUsage> $usages
 * @property-read int|null $usages_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCurrentStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereMinimumStockLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUpdatedAt($value)
 */
	class Material extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Material|null $material
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDelivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDelivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDelivery query()
 */
	class MaterialDelivery extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $project_id
 * @property int $phase_id
 * @property int $material_id
 * @property numeric $quantity_used
 * @property string|null $unit
 * @property \Illuminate\Support\Carbon $usage_date
 * @property string|null $remarks
 * @property int $recorded_by
 * @property string|null $site_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material|null $material
 * @property-read \App\Models\ConstructionPhase|null $phase
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\User|null $recorder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage wherePhaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereQuantityUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereSitePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialUsage whereUsageDate($value)
 */
	class MaterialUsage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $milestone_id
 * @property int $phase_id
 * @property string $milestone_name
 * @property \Illuminate\Support\Carbon $planned_date
 * @property \Illuminate\Support\Carbon|null $actual_date
 * @property bool $is_completed
 * @property bool $is_delayed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ConstructionPhase $phase
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone delayed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereActualDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereIsDelayed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereMilestoneName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone wherePhaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone wherePlannedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereUpdatedAt($value)
 */
	class Milestone extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $project_id
 * @property string $project_name
 * @property string|null $location
 * @property int $client_id
 * @property int $engineer_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $target_end_date
 * @property \Illuminate\Support\Carbon|null $actual_end_date
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendanceLogs
 * @property-read int|null $attendance_logs_count
 * @property-read \App\Models\Client $client
 * @property-read \App\Models\User $engineer
 * @property-read mixed $active_supervisor
 * @property-read mixed $current_phase
 * @property-read mixed $current_phase_name
 * @property-read mixed $manager_name
 * @property-read mixed $name
 * @property-read mixed $progress_percentage
 * @property-read mixed $status_badge
 * @property-read mixed $status_label
 * @property-read mixed $status_text
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConstructionPhase> $phases
 * @property-read int|null $phases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectMaterial> $projectMaterials
 * @property-read int|null $project_materials_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectWorker> $projectWorkers
 * @property-read int|null $project_workers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $supervisors
 * @property-read int|null $supervisors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Worker> $workers
 * @property-read int|null $workers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereActualEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereEngineerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereTargetEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereUpdatedAt($value)
 */
	class Project extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $project_archive_id
 * @property int $project_id
 * @property string $project_name
 * @property string|null $project_location
 * @property int|null $client_id
 * @property int|null $engineer_id
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $target_end_date
 * @property \Illuminate\Support\Carbon|null $actual_end_date
 * @property string|null $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\User|null $engineer
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereActualEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereEngineerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereProjectArchiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereProjectLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereTargetEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectArchive whereUpdatedAt($value)
 */
	class ProjectArchive extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $project_id
 * @property int $material_id
 * @property numeric $planned_quantity
 * @property numeric $used_quantity
 * @property string|null $unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material|null $material
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial wherePlannedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectMaterial whereUsedQuantity($value)
 */
	class ProjectMaterial extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $deployment_id
 * @property int $project_id
 * @property int $worker_id
 * @property string $deployed_date
 * @property bool $is_active
 * @property string $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendanceLogs
 * @property-read int|null $attendance_logs_count
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker whereDeployedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker whereDeploymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectWorker whereWorkerId($value)
 */
	class ProjectWorker extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $report_id
 * @property int $project_id
 * @property int $phase_id
 * @property int $submitted_by
 * @property int|null $reviewed_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon $report_date
 * @property string $report_text
 * @property array<array-key, mixed>|null $site_images
 * @property string $ai_status
 * @property string $approval_status
 * @property string|null $approval_remarks
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read string $report_identifier
 * @property-read string $report_title
 * @property-read string $status_badge_class
 * @property-read string $status_label
 * @property-read \App\Models\ConstructionPhase $phase
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User|null $reviewedBy
 * @property-read \App\Models\User $submittedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereAiStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereApprovalRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report wherePhaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereSiteImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereSubmittedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 */
	class Report extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $supervisor_id
 * @property string|null $type
 * @property string $title
 * @property string|null $message
 * @property array<array-key, mixed>|null $data
 * @property int|null $related_id
 * @property string|null $related_type
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $supervisor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereRelatedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereSupervisorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupervisorNotification whereUpdatedAt($value)
 */
	class SupervisorNotification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $log_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $description
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog forAction($action)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog whereLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemLog whereUserId($value)
 */
	class SystemLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $password
 * @property string $role
 * @property string|null $contact_number
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendanceLogs
 * @property-read int|null $attendance_logs_count
 * @property-read \App\Models\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Project> $engineeredProjects
 * @property-read int|null $engineered_projects_count
 * @property-read mixed $name
 * @property-read mixed $role_badge
 * @property-read mixed $role_name
 * @property-read mixed $status_badge
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\LaravelPasskeys\Models\Passkey> $passkeys
 * @property-read int|null $passkeys_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Report> $submittedReports
 * @property-read int|null $submitted_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Project> $supervisedProjects
 * @property-read int|null $supervised_projects_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserId($value)
 */
	class User extends \Eloquent implements \Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys {}
}

namespace App\Models{
/**
 * @property int $worker_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $trade
 * @property string|null $contact_number
 * @property bool $is_active
 * @property string|null $credential_id
 * @property string|null $credential_json
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendanceLogs
 * @property-read int|null $attendance_logs_count
 * @property-read mixed $full_name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereCredentialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereCredentialJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereTrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereWorkerId($value)
 */
	class Worker extends \Eloquent {}
}

