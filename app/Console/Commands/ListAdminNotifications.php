<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminNotification;

class ListAdminNotifications extends Command
{
    protected $signature = 'notifications:list {--limit=10}';
    protected $description = 'List recent admin_notifications rows';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $rows = AdminNotification::query()->orderByDesc('created_at')->take($limit)->get();

        if ($rows->isEmpty()) {
            $this->info('No admin_notifications found.');
            return 0;
        }

        $headers = ['id', 'admin_id', 'type', 'title', 'is_read', 'created_at'];
        $data = $rows->map(function ($r) {
            return [
                $r->id,
                $r->admin_id,
                $r->type,
                $r->title,
                $r->is_read ? '1' : '0',
                $r->created_at->toDateTimeString(),
            ];
        })->toArray();

        $this->table($headers, $data);

        return 0;
    }
}
