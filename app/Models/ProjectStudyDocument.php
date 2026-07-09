<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStudyDocument extends Model
{
    protected $fillable = ['project_study_request_id', 'original_name', 'path'];

    public function projectStudyRequest(): BelongsTo
    {
        return $this->belongsTo(ProjectStudyRequest::class);
    }
}
