<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['employee_id', 'display_name', 'business_unit_code', 'department_code', 'employee_level_code', 'employment_type_code'];
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_code', 'code');
    }
    public function employeeBand(): BelongsTo
    {
        return $this->belongsTo(EmployeeBand::class, 'employee_level_code', 'code');
    }
    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class, 'employee_id', 'employee_id');
    }
    public function onDuties(): HasMany
    {
        return $this->hasMany(OnDuty::class, 'employee_id', 'employee_id');
    }
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id');
    }
    public function attendanceRegularizations(): HasMany
    {
        return $this->hasMany(AttendanceRegularization::class, 'employee_id', 'employee_id');
    }
}
