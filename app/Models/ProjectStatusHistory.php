<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusHistory extends Model
{
    protected $table = 'project_status_histories';
    protected $primaryKey = 'project_status_history_id';

    protected $fillable = [
        'project_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by', 'user_id');
    }
}
