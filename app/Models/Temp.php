<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temp extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'code',
        'google_sheet',
        'site_name',
        'site_domain',
        'status',
        'site_type',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
