<?php

namespace App\Domain\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'desc', 'is_super', 'is_preset'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $casts = [
        'is_super' => 'boolean',
        'is_preset' => 'boolean'
    ];
}
