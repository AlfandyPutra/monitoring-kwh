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

        $allMeters = PowerMeter::where('is_active', true)
            ->orderBy('converter_name')
            ->orderBy('slave_id')
            ->get();

        $query = PowerMeter::where('is_active', true)
            ->orderBy('converter_name')
            ->orderBy('slave_id');

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

        // Hitung metrik utama (Incoming vs 4 Panel Distribusi)
        $incomingMeter = $allMeters->firstWhere('panel_code', 'INCOMING');
        $incomingMtdKwh = 0;
        $incomingLatestPf = null;

        if ($incomingMeter) {
            $incomingLogs = EnergyLog::where('power_meter_id', $incomingMeter->id)
                ->where('recorded_at', 'like', $month . '%')
                ->orderBy('recorded_at', 'desc')
                ->get();
            $incomingMtdKwh = (float) $incomingLogs->sum('kwh_usage');
            $incomingLatestPf = optional($incomingLogs->first())->power_factor;
        }

        $distributionMeters = $allMeters->where('panel_code', '!=', 'INCOMING');
        $distributionMtdKwh = 0;
        foreach ($distributionMeters as $distMeter) {
            $distributionMtdKwh += (float) EnergyLog::where('power_meter_id', $distMeter->id)
                ->where('recorded_at', 'like', $month . '%')
                ->sum('kwh_usage');
        }

        $lossesKwh = max(0, round($incomingMtdKwh - $distributionMtdKwh, 2));
        $lossesPercent = $incomingMtdKwh > 0
            ? round(($lossesKwh / $incomingMtdKwh) * 100, 1)
            : 0;

        // Estimasi biaya MTD (contoh tarif Rp 1.445 / kWh, bisa disesuaikan)
        $tariffPerKwh = 1445;
        $estimatedCost = round($incomingMtdKwh * $tariffPerKwh);

        // Kualitas daya 3-fasa & beban arus dari panel yang sedang tampil
        $voltages = [];
        $currents = [];
        $pfs = [];
        $anomalyCount = 0;
        $peakCurrent = 0;
        $peakCurrentTime = null;

        foreach ($meters as $meter) {
            $latest = $meter->energyLogs->first();
            $pfVal = $latest ? (float) $latest->power_factor : 0.96;

            if ($latest) {
                $voltages[] = (float) $latest->voltage_avg;
                $currents[] = (float) $latest->current_avg;
                $pfs[] = $pfVal;
            }

            if ($pfVal > 0 && $pfVal < 0.90) {
                $anomalyCount++;
            }

            foreach ($meter->energyLogs as $log) {
                if ((float) $log->current_avg > $peakCurrent) {
                    $peakCurrent = (float) $log->current_avg;
                    $peakCurrentTime = Carbon::parse($log->recorded_at)->format('H:i');
                }
            }
        }

        $avgVoltage = count($voltages) > 0 ? round(array_sum($voltages) / count($voltages), 1) : 381.0;
        $avgPf = count($pfs) > 0 ? round(array_sum($pfs) / count($pfs), 2) : 0.94;
        $totalCurrent = round(array_sum($currents), 1);

        // Data grafik tren harian pada bulan terpilih
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $month === Carbon::now()->format('Y-m')
            ? Carbon::now()->endOfDay()
            : $startOfMonth->copy()->endOfMonth();

        $chartLabels = [];
        $incomingDailySeries = [];
        $distributionDailySeries = [];

        $cursor = $startOfMonth->copy();
        $daysCount = 0;
        while ($cursor->lte($endOfMonth)) {
            $dateStr = $cursor->format('Y-m-d');
            $chartLabels[] = $cursor->format('d M');

            $incDay = $incomingMeter
                ? (float) EnergyLog::where('power_meter_id', $incomingMeter->id)
                    ->whereDate('recorded_at', $dateStr)
                    ->sum('kwh_usage')
                : 0;

            $distDay = 0;
            foreach ($distributionMeters as $distMeter) {
                $distDay += (float) EnergyLog::where('power_meter_id', $distMeter->id)
                    ->whereDate('recorded_at', $dateStr)
                    ->sum('kwh_usage');
            }

            $incomingDailySeries[] = round($incDay, 2);
            $distributionDailySeries[] = round($distDay, 2);
            $daysCount++;
            $cursor->addDay();
        }

        $avgDailyKwh = $daysCount > 0 ? round($incomingMtdKwh / $daysCount, 1) : 0;

        $lastUpdate = EnergyLog::orderBy('recorded_at', 'desc')->first();
        $lastUpdateLabel = $lastUpdate
            ? Carbon::parse($lastUpdate->recorded_at)->format('d M Y, H:i') . ' WIB'
            : Carbon::now()->format('d M Y, H:i') . ' WIB';

        return view('dashboard', compact(
            'meters',
            'allMeters',
            'distributionMeters',
            'month',
            'converter',
            'panelCode',
            'incomingMtdKwh',
            'incomingLatestPf',
            'distributionMtdKwh',
            'lossesKwh',
            'lossesPercent',
            'estimatedCost',
            'avgVoltage',
            'avgPf',
            'totalCurrent',
            'peakCurrent',
            'peakCurrentTime',
            'anomalyCount',
            'chartLabels',
            'incomingDailySeries',
            'distributionDailySeries',
            'avgDailyKwh',
            'lastUpdateLabel'
        )); 
    }

    public function simulateFetch()
    {
        $meters = PowerMeter::where('is_active', true)->get();

        // Hitung pemakaian distribusi agar Incoming PLN selalu realistis sedikit lebih tinggi (losses wajar)
        $distUsages = [];
        foreach ($meters as $meter) {
            if ($meter->panel_code !== 'INCOMING') {
                $distUsages[$meter->id] = round(mt_rand(120, 480) / 10, 2);
            }
        }

        $totalDistThisFetch = array_sum($distUsages);
        $incomingUsage = round($totalDistThisFetch + (mt_rand(40, 180) / 10), 2);

        foreach ($meters as $meter) {
            $lastLog = EnergyLog::where('power_meter_id', $meter->id)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $baseTotal = match ($meter->panel_code) {
                'INCOMING'  => 146297.20,
                'MDP-01NJK' => 69349.40,
                'MDP-02NJK' => 55135.30,
                'SDP-D1'    => 13361.00,
                'SDP-E1'    => 11220.50,
                default     => 10000.00,
            };

            $usage = $meter->panel_code === 'INCOMING'
                ? $incomingUsage
                : ($distUsages[$meter->id] ?? 25.00);

            $newTotal = $lastLog ? round((float) $lastLog->kwh_total + $usage, 2) : round($baseTotal + $usage, 2);

            // Simulasi PF INCOMING sesekali di rentang 0.87 (mendekati batas 0.85) agar banner alert teruji
            $pf = $meter->panel_code === 'INCOMING'
                ? (mt_rand(0, 1) === 0 ? 0.87 : 0.93)
                : round(mt_rand(94, 99) / 100, 2);

            EnergyLog::create([
                'power_meter_id' => $meter->id,
                'kwh_total'      => $newTotal,
                'kwh_usage'      => $usage,
                'voltage_avg'    => round(mt_rand(3790, 3840) / 10, 1),
                'current_avg'    => round(mt_rand(650, 1850) / 10, 1),
                'power_factor'   => $pf,
                'recorded_at'    => Carbon::now(),
            ]);
        }

        return redirect()->back()->with('success', 'Data kWh terbaru berhasil ditarik dan diperbarui ke database.');
    }
}