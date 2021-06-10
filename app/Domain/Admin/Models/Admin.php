<?php

namespace App\Domain\Admin\Models;

use App\Domain\Admin\AdminService;
use App\Domain\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    const AUTH_CACHE_KEY_PRE = 'admin_auth:';

    protected $fillable = [
        'staff_id', 'password', 'name', 'mobile', 'status'
    ];

    protected $hidden = [
        'password'
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_has_roles', 'admin_id', 'role_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function hasPermission(string $permissionKey)
    {
        $ownedPermissions = Cache::remember(self::AUTH_CACHE_KEY_PRE.$this->id, 24 * 60 * 60, function () {
            return resolve(AdminService::class)->getAllPermissionByRoles($this->roles)->pluck('key');
        });

        return $ownedPermissions->contains($permissionKey);
    }

    public function scopeOfRoles($query, array $roleIds)
    {
        return $query->whereIn('id', function ($query) use ($roleIds) {
            $query->select('admin_id')->whereIn('role_id', $roleIds)->from('admin_has_roles');
        });
    }
}
