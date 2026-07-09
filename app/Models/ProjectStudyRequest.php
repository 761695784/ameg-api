<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStudyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'company', 'phone', 'email', 'city', 'establishment_type',
        'description', 'estimated_budget', 'desired_deadline', 'status',
        'admin_reply', 'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectStudyDocument::class);
    }
}
