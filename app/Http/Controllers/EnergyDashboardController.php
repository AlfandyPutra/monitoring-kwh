<?php

namespace App\Http\Controllers;

use App\Models\PowerMeter;
use App\Models\EnergyLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EnergyDashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $converter = $request->input('converter', 'ALL');
        $panelCode = $request->input('panel', 'ALL');

        $query = PowerMeter::where('is_active', true);

        if ($converter !== 'ALL') {
            $query->where('converter_name', $converter);
        }
        if ($panelCode !== 'ALL') {
            $query->where('panel_code', $panelCode);
        }

        $meters = $query->with(['energyLogs' => function ($q) use ($month) {
            $q->where('recorded_at', 'like', $month . '%')
              ->orderBy('recorded_at', 'desc');
        }])->get();

        $allMeters = PowerMeter::where('is_active', true)->get();

        return view('dashboard', compact('meters', 'allMeters', 'month', 'converter', 'panelCode'));
    }

    // Endpoint simulasi tarik data dinamis (menggantikan cron job sementara saat demo)
    public function simulateFetch()
    {
        $meters = PowerMeter::where('is_active', true)->get();

        foreach ($meters as $meter) {
            $lastLog = EnergyLog::where('power_meter_id', $meter->id)
                ->orderBy('recorded_at', 'desc')
                ->first();

            // Base kWh awal jika belum ada log sama sekali
            $baseKwh = match ($meter->panel_code) {
                'INCOMING'  => 145280.50,
                'MDP-01NJK' => 68420.20,
                'MDP-02NJK' => 54190.80,
                'SDP-D1'    => 12450.10,
                'SDP-E1'    => 10219.40,
                default     => 10000.00,
            };

            $usage = round(mt_rand(150, 850) / 10, 2); // Tambahan 15.0 - 85.0 kWh
            $newTotal = $lastLog ? ($lastLog->kwh_total + $usage) : ($baseKwh + $usage);

            EnergyLog::create([
                'power_meter_id' => $meter->id,
                'kwh_total'      => $newTotal,
                'kwh_usage'      => $usage,
                'voltage_avg'    => round(mt_rand(3780, 3845) / 10, 1), // ~380V 3-Phase
                'current_avg'    => round(mt_rand(450, 1850) / 10, 1),  // Ampere
                'power_factor'   => round(mt_rand(92, 99) / 100, 2),    // 0.92 - 0.99
                'recorded_at'    => Carbon::now(),
            ]);
        }

        return redirect()->back()->with('success', 'Data kWh terbaru berhasil ditarik dari seluruh Converter USR-DR302!');
    }
}