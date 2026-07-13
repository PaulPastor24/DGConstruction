<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectArchive extends Model
{
    protected $table = 'project_archives';
    protected $primaryKey = 'project_archive_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'project_name',
        'project_location',
        'client_id',
        'engineer_id',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'status',
        'description',
        'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_end_date' => 'date',
        'actual_end_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'project_archive_id';
    }

    public static function fromProject(Project $project): self
    {
        return new self([
            'project_id' => $project->getKey(),
            'project_name' => $project->project_name,
            'project_location' => self::resolveLocation($project),
            'client_id' => $project->client_id,
            'engineer_id' => $project->engineer_id,
            'start_date' => $project->start_date,
            'target_end_date' => $project->target_end_date,
            'actual_end_date' => $project->actual_end_date,
            'status' => 'archived',
            'description' => $project->description,
            'archived_at' => now(),
        ]);
    }

    public static function resolveLocation(Project $project): ?string
    {
        foreach (['project_location', 'location'] as $attribute) {
            $value = $project->getAttribute($attribute);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id', 'user_id');
    }
}
