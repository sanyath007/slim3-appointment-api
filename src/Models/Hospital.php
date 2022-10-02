<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $table = "hospcode";
    protected $primaryKey = "hospcode";
    public $incrementing = false;
    public $timestamps = false;

    public function changwat()
    {
        return $this->belongsTo(Changwat::class, 'chwpart', 'chw_id');
    }

    public function amphur()
    {
        return $this->belongsTo(Amphur::class, 'amppart', 'amp_id');
    }
}