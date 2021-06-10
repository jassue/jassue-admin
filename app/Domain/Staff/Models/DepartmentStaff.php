<?php

namespace App\Domain\Staff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id', 'staff_id'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
