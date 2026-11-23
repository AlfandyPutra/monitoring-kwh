<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyLog extends Model
{
    protected $fillable = [
        'power_meter_id', 'kwh_total', 'kwh_usage',
        'voltage_avg', 'current_avg', 'power_factor', 'recorded_at'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function powerMeter()
    {
        return $this->belongsTo(PowerMeter::class);
    }
}