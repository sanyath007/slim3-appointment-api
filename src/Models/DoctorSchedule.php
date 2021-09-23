<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table = "doctor_schedules";

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor', 'emp_id');
    }
}
