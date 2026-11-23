<?php

namespace Database\Seeders;

use App\Models\PowerMeter;
use Illuminate\Database\Seeder;

class PowerMeterSeeder extends Seeder
{
    public function run(): void
    {
        $meters = [
            // Terhubung ke Converter USR-DR302 Unit 1 (IP Simulasi: 192.168.1.201)
            [
                'panel_code' => 'INCOMING',
                'panel_name' => 'Panel Utama Incoming PLN',
                'converter_name' => 'USR-DR302 #1',
                'ip_address' => '192.168.1.201',
                'port' => 502,
                'slave_id' => 1,
            ],
            [
                'panel_code' => 'MDP-01NJK',
                'panel_name' => 'Main Distribution Panel 01',
                'converter_name' => 'USR-DR302 #1',
                'ip_address' => '192.168.1.201',
                'port' => 502,
                'slave_id' => 2,
            ],
            [
                'panel_code' => 'MDP-02NJK',
                'panel_name' => 'Main Distribution Panel 02',
                'converter_name' => 'USR-DR302 #1',
                'ip_address' => '192.168.1.201',
                'port' => 502,
                'slave_id' => 3,
            ],
            // Terhubung ke Converter USR-DR302 Unit 2 (IP Simulasi: 192.168.1.202)
            [
                'panel_code' => 'SDP-D1',
                'panel_name' => 'Sub Distribution Panel D1',
                'converter_name' => 'USR-DR302 #2',
                'ip_address' => '192.168.1.202',
                'port' => 502,
                'slave_id' => 1,
            ],
            [
                'panel_code' => 'SDP-E1',
                'panel_name' => 'Sub Distribution Panel E1',
                'converter_name' => 'USR-DR302 #2',
                'ip_address' => '192.168.1.202',
                'port' => 502,
                'slave_id' => 2,
            ],
        ];

        foreach ($meters as $meter) {
            PowerMeter::updateOrCreate(['panel_code' => $meter['panel_code']], $meter);
        }
    }
}