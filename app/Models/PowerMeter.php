<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowerMeter extends Model
{
    protected $fillable = [
        'panel_code', 'panel_name', 'converter_name',
        'ip_address', 'port', 'slave_id', 'is_active'
    ];

    public function energyLogs()
    {
        return $this->hasMany(EnergyLog::class);
    }
}