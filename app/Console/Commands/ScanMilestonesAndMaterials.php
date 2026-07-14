<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Milestone;
use App\Models\Material;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ScanMilestonesAndMaterials extends Command
{
    protected $signature = 'notifications:scan';
    protected $description = 'Scan milestones for due-soon/overdue and materials for low stock and notify admins.';

    public function handle()
    {
        $this->info('Scanning milestones and materials...');

        // Due soon: within 3 days (including today)
        $dueSoon = Milestone::query()
            ->where('is_completed', false)
            ->whereNull('is_delayed')
            ->whereDate('end_date', '<=', now()->addDays(3)->toDateString())
            ->get();

        foreach ($dueSoon as $m) {
            try {
                NotificationService::notifyAdmins([
                    'type' => 'milestone',
                    'title' => 'Milestone Due Soon',
                    'message' => "Milestone '{$m->milestone_name}' for project ID {$m->project_id} is due on {$m->end_date}.",
                    'data' => ['module' => 'admin.timeline', 'milestone_id' => $m->milestone_id, 'project_id' => $m->project_id, 'recipient' => 'Admin'],
                    'related_id' => $m->milestone_id,
                    'related_type' => 'milestone',
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to notify admins for due-soon milestone: ' . $e->getMessage());
            }
        }

        // Overdue: end_date < today and not completed
        $overdue = Milestone::query()
            ->where('is_completed', false)
            ->whereDate('end_date', '<', now()->toDateString())
            ->get();

        foreach ($overdue as $m) {
            try {
                NotificationService::notifyAdmins([
                    'type' => 'milestone',
                    'title' => 'Milestone Overdue',
                    'message' => "Milestone '{$m->milestone_name}' for project ID {$m->project_id} is overdue (ended {$m->end_date}).",
                    'data' => ['module' => 'admin.timeline', 'milestone_id' => $m->milestone_id, 'project_id' => $m->project_id, 'recipient' => 'Admin'],
                    'related_id' => $m->milestone_id,
                    'related_type' => 'milestone',
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to notify admins for overdue milestone: ' . $e->getMessage());
            }
        }

        // Low stock
        $materials = Material::query()->whereColumn('current_stock', '<=', 'minimum_stock_level')->get();
        foreach ($materials as $mat) {
            try {
                NotificationService::notifyAdmins([
                    'type' => 'material',
                    'title' => 'Low Material Stock',
                    'message' => "Material '{$mat->name}' stock is low (current: {$mat->current_stock}).",
                    'data' => ['module' => 'admin.inventory', 'material_id' => $mat->id, 'material_name' => $mat->name, 'recipient' => 'Admin'],
                    'related_id' => $mat->id,
                    'related_type' => 'material',
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to notify admins for low stock material: ' . $e->getMessage());
            }
        }

        $this->info('Scan complete.');
        return 0;
    }
}
