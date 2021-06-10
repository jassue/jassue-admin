<?php

namespace App\Domain\Staff\Models;

use App\Domain\Admin\Models\Admin;
use App\Domain\Common\Services\MediaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'mobile', 'avatar_id', 'gender', 'position', 'job_number'
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, DepartmentStaff::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'staff_id', 'id');
    }

    public function scopeOfKeyword($query, $keyword)
    {
        return $query->where('name', 'like', '%'.$keyword.'%')
            ->orWhere('mobile', 'like', '%'.$keyword.'%');
    }

    public function scopeOfDepartments($query, $departmentIds)
    {
        return $query->whereIn('id', function ($query) use ($departmentIds) {
            $query->select('staff_id')->from('department_staff')->whereIn('department_id', $departmentIds);
        });
    }

    public function toArray()
    {
        $data = parent::toArray();
        isset($data['avatar_id']) && $data['avatar_url'] = resolve(MediaService::class)->getUrlById($data['avatar_id']);
        isset($data['id']) && $data['is_preset'] = $data['id'] === 1;
        return $data;
    }
}
