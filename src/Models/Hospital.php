<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $table = "hospcode";

    public function changwat()
    {
        return $this->belongsTo(Changwat::class, 'chwpart', 'chw_id');
    }

    public function amphur()
    {
        return $this->belongsTo(Amphur::class, 'amppart', 'amp_id');
    }
}