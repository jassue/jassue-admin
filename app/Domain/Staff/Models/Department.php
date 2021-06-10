<?php

namespace App\Domain\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'parent_id', 'path'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'pivot'
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, DepartmentStaff::class);
    }
}
