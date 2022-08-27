<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postponement extends Model
{
    protected $table = "postponements";

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appoint_id', 'id');
    }
}