<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Monitoring System | MMM Plastic — Plant Nganjuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f4f4f6; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="text-zinc-800 antialiased">

    <!-- Top Header -->
    <header class="bg-zinc-950 text-white border-b-2 border-yellow-400 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap items-center justify-between gap-4">
            <!-- Brand Logo MMM Plastic -->
            <div class="flex items-center gap-3.5">
                <div class="bg-white px-2.5 py-1.5 rounded-lg flex items-center gap-2 shrink-0">
                    <svg width="32" height="28" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="10,75 32,88 32,58 10,45" fill="#18181B"/>
                        <polygon points="10,45 32,58 55,44 33,31" fill="#71717A"/>
                        <polygon points="35,60 57,73 57,43 35,30" fill="#FACC15"/>
                        <polygon points="35,30 57,43 80,29 58,16" fill="#71717A"/>
                        <polygon points="60,45 82,58 82,28 60,15" fill="#18181B"/>
                        <polygon points="60,15 82,28 105,14 83,1" fill="#71717A"/>
                        <polygon points="85,30 107,43 107,13 85,0" fill="#FACC15"/>
                        <polygon points="85,62 107,49 107,35 85,48" fill="#71717A"/>
                    </svg>
                    <div class="leading-none pr-0.5">
                        <span class="block text-zinc-900 font-extrabold text-sm tracking-tight">MMM</span>
                        <span class="block text-zinc-500 font-bold text-[8px] tracking-[0.24em] mt-0.5">PLASTIC</span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-400 text-sm font-bold">⚡</span>
                        <h1 class="text-base sm:text-lg font-bold tracking-tight text-white">Energy Monitoring System</h1>
                    </div>
                    <p class="text-xs text-zinc-400">PT Mitra Mulia Makmur — Plant Nganjuk (Utility &amp; 3-Phase Power Meter)</p>
                </div>
            </div>

            <!-- Right Status & Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="text-xs text-zinc-300 bg-zinc-900 border border-zinc-800 px-3 py-1.5 rounded-lg tabular-nums">
                    Update: {{ $lastUpdateLabel }}
                </span>

                <form action="{{ route('simulate.fetch') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-yellow-400 border border-yellow-400/50 text-xs font-semibold px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <span>↻</span>
                        <span>Tarik Data kWh</span>
                    </button>
                </form>

                <button type="button" onclick="exportTableToCSV('rekap_kwh_mmm_nganjuk_{{ $month }}.csv')" class="bg-white hover:bg-zinc-100 text-zinc-900 text-xs font-semibold px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <span>⤓</span>
                    <span>Export Excel</span>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-5">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-2.5 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-between">
            <span>✓ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold px-2">&times;</button>
        </div>
        @endif

        <!-- Filter Bar -->
        <section class="bg-white rounded-xl border border-zinc-200 p-4 shadow-2xs">
            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Periode Bulan (MTD)</label>
                    <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                        class="w-full border border-zinc-300 rounded-lg px-3 py-2 text-xs sm:text-sm font-medium text-zinc-800 bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                <div class="md:col-span-4">
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Converter Gateway</label>
                    <select name="converter" onchange="this.form.submit()"
                        class="w-full border border-zinc-300 rounded-lg px-3 py-2 text-xs sm:text-sm font-medium text-zinc-800 bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="ALL" {{ $converter === 'ALL' ? 'selected' : '' }}>Semua Converter (2x USR-DR302)</option>
                        <option value="USR-DR302 #1" {{ $converter === 'USR-DR302 #1' ? 'selected' : '' }}>USR-DR302 #1 (192.168.1.201)</option>
                        <option value="USR-DR302 #2" {{ $converter === 'USR-DR302 #2' ? 'selected' : '' }}>USR-DR302 #2 (192.168.1.202)</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Filter Panel Listrik</label>
                    <select name="panel" onchange="this.form.submit()"
                        class="w-full border border-zinc-300 rounded-lg px-3 py-2 text-xs sm:text-sm font-medium text-zinc-800 bg-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="ALL" {{ $panelCode === 'ALL' ? 'selected' : '' }}>Semua Panel ({{ $allMeters->count() }} Unit)</option>
                        @foreach($allMeters as $item)
                            <option value="{{ $item->panel_code }}" {{ $panelCode === $item->panel_code ? 'selected' : '' }}>
                                {{ $item->panel_code }} — {{ $item->panel_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <a href="{{ route('dashboard') }}" class="w-full inline-flex items-center justify-center gap-1.5 border border-zinc-300 hover:bg-zinc-50 text-zinc-700 font-semibold text-xs sm:text-sm py-2 px-3 rounded-lg transition">
                        <span>✕</span>
                        <span>Reset Filter</span>
                    </a>
                </div>
            </form>
        </section>

        <!-- Alert Kualitas Daya -->
        @php
            $pfAlertValue = $incomingLatestPf ?? 0.87;
            $normalCount = max(0, $meters->count() - $anomalyCount);
        @endphp
        <section class="bg-amber-50/80 border border-amber-200 rounded-xl px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 text-amber-950">
                <span class="text-amber-600 font-bold">⚠</span>
                <span>
                    <strong>Perhatian Kualitas Daya:</strong>
                    Faktor daya <strong>INCOMING</strong> berada di <strong>{{ number_format($pfAlertValue, 2, ',', '.') }}</strong>
                    (mendekati batas penalti PLN &lt; 0,85) saat beban puncak shift pagi.
                </span>
            </div>
            <div class="text-zinc-600 font-medium tabular-nums">
                {{ $anomalyCount }} Anomali Aktif · {{ $normalCount }} Panel Normal
            </div>
        </section>

        <!-- 4 KPI Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <!-- Card 1: Konsumsi Incoming PLN -->
            <div class="bg-white rounded-xl border border-zinc-200 p-4 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                    <span>Konsumsi Incoming PLN</span>
                    <span class="text-amber-500 text-sm">⚡</span>
                </div>
                <div class="my-2.5 tabular-nums">
                    <span class="text-2xl font-extrabold text-zinc-900">{{ number_format($incomingMtdKwh, 2, ',', '.') }}</span>
                    <span class="text-xs font-medium text-zinc-500 ml-1">kWh</span>
                </div>
                <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-xs tabular-nums">
                    <span class="text-zinc-400">Estimasi biaya MTD</span>
                    <span class="font-bold text-zinc-800">Rp {{ number_format($estimatedCost, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card 2: Total Distribusi -->
            <div class="bg-white rounded-xl border border-zinc-200 p-4 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                    <span>Total Distribusi ({{ $distributionMeters->count() }} Panel)</span>
                    <span class="text-zinc-400 text-xs">▤</span>
                </div>
                <div class="my-2.5 tabular-nums">
                    <span class="text-2xl font-extrabold text-zinc-900">{{ number_format($distributionMtdKwh, 2, ',', '.') }}</span>
                    <span class="text-xs font-medium text-zinc-500 ml-1">kWh</span>
                </div>
                <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-xs tabular-nums">
                    <span class="text-zinc-400">Selisih / losses</span>
                    <span class="font-bold text-emerald-600">
                        {{ number_format($lossesKwh, 2, ',', '.') }} kWh ({{ number_format($lossesPercent, 1, ',', '.') }}%)
                    </span>
                </div>
            </div>

            <!-- Card 3: Kualitas Daya 3-Fase -->
            <div class="bg-white rounded-xl border border-zinc-200 p-4 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                    <span>Kualitas Daya 3-Fase</span>
                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                        ● Stabil
                    </span>
                </div>
                <div class="my-2.5 flex items-baseline gap-2.5 tabular-nums">
                    <span class="text-2xl font-extrabold text-zinc-900">{{ number_format($avgVoltage, 1, ',', '.') }}</span>
                    <span class="text-xs font-medium text-zinc-500">V</span>
                    <span class="text-zinc-300">·</span>
                    <span class="text-sm font-bold text-zinc-800">PF {{ number_format($avgPf, 2, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-xs tabular-nums">
                    <span class="text-zinc-400">Toleransi 380V ±5%</span>
                    <span class="font-semibold text-zinc-700">361–399 V</span>
                </div>
            </div>

            <!-- Card 4: Beban Arus & Alat -->
            <div class="bg-white rounded-xl border border-zinc-200 p-4 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                    <span>Beban Arus &amp; Alat</span>
                    <span class="text-zinc-400 text-xs">((•))</span>
                </div>
                <div class="my-2.5 flex items-baseline gap-2 tabular-nums">
                    <span class="text-2xl font-extrabold text-zinc-900">{{ number_format($totalCurrent, 1, ',', '.') }}</span>
                    <span class="text-xs font-medium text-zinc-500">A</span>
                    <span class="text-zinc-300">·</span>
                    <span class="text-xs font-bold text-emerald-600">{{ $meters->count() }}/{{ $allMeters->count() }} Online</span>
                </div>
                <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-xs tabular-nums">
                    <span class="text-zinc-400">Beban puncak hari ini</span>
                    <span class="font-bold text-zinc-800">
                        {{ number_format($peakCurrent, 1, ',', '.') }} A{{ $peakCurrentTime ? ' (' . $peakCurrentTime . ')' : '' }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Chart + Gateway Status -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Grafik Analisis Konsumsi Energi -->
            <div class="lg:col-span-8 bg-white rounded-xl border border-zinc-200 p-5 shadow-2xs">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900">Analisis Konsumsi Energi (kWh)</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            Pemantauan tren harian Incoming PLN vs distribusi beban per panel ({{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('M Y') }})
                        </p>
                    </div>
                    <div class="inline-flex rounded-lg border border-zinc-200 p-0.5 bg-zinc-50 text-xs font-semibold">
                        <button type="button" id="btnTrenHarian" onclick="switchChartMode('daily')" class="bg-zinc-900 text-white px-3 py-1 rounded-md cursor-pointer">
                            Tren Harian
                        </button>
                        <button type="button" id="btnPorsiPanel" onclick="switchChartMode('panel')" class="text-zinc-600 hover:text-zinc-900 px-3 py-1 rounded-md cursor-pointer">
                            Porsi per Panel
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs mb-3">
                    <div id="chartLegendBox" class="flex items-center gap-4 text-zinc-600">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>
                            Incoming PLN (kWh/hari)
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-zinc-900 inline-block"></span>
                            Total Distribusi Panel
                        </span>
                    </div>
                    <div class="text-zinc-500 tabular-nums">
                        Rata-rata: <strong class="text-zinc-800">{{ number_format($avgDailyKwh, 1, ',', '.') }} kWh/hari</strong>
                    </div>
                </div>

                <div class="h-60">
                    <canvas id="energyChart"></canvas>
                </div>
            </div>

            <!-- Status Gateway Modbus -->
            <div class="lg:col-span-4 bg-zinc-950 text-white rounded-xl p-5 flex flex-col justify-between shadow-2xs">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-3 mb-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-yellow-400">Status Gateway Modbus</h2>
                        <span class="text-[11px] font-mono text-zinc-400 bg-zinc-900 border border-zinc-800 px-2 py-0.5 rounded">
                            TCP Port 502
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="bg-zinc-900/90 border border-zinc-800 rounded-lg p-3.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-white">USR-DR302 #1</span>
                                <span class="font-mono text-yellow-400">192.168.1.201</span>
                            </div>
                            <p class="text-[11px] text-zinc-400 mt-1">RS485 Slave: #1 INCOMING, #2 MDP-01, #3 MDP-02</p>
                            <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[11px] tabular-nums">
                                <span class="text-emerald-400 font-semibold">● Online · Polling 5 dtk</span>
                                <span class="text-zinc-400 font-mono">12 ms</span>
                            </div>
                        </div>

                        <div class="bg-zinc-900/90 border border-zinc-800 rounded-lg p-3.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-white">USR-DR302 #2</span>
                                <span class="font-mono text-yellow-400">192.168.1.202</span>
                            </div>
                            <p class="text-[11px] text-zinc-400 mt-1">RS485 Slave: #1 SDP-D1, #2 SDP-E1</p>
                            <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[11px] tabular-nums">
                                <span class="text-emerald-400 font-semibold">● Online · Polling 5 dtk</span>
                                <span class="text-zinc-400 font-mono">16 ms</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-zinc-800 flex items-center justify-between text-[11px] text-zinc-400">
                    <span>Switch: Linksys LGS116</span>
                    <span class="font-semibold text-zinc-300">RJ45 LAN</span>
                </div>
            </div>
        </section>

        <!-- Tabel Rekapitulasi Pembacaan kWh Meter per Panel -->
        <section class="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-2xs">
            <div class="bg-zinc-950 text-white px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider">
                    Rekapitulasi Pembacaan kWh Meter per Panel
                </h2>
                <span class="text-xs text-zinc-400 tabular-nums">
                    {{ $meters->count() }} Unit Terpantau · Standar 3-Phase 380V
                </span>
            </div>

            <div class="overflow-x-auto">
                <table id="panelSummaryTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                            <th class="px-4 py-3">Panel</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Gateway &amp; Slave</th>
                            <th class="px-4 py-3 text-right">Stand Meter Akhir</th>
                            <th class="px-4 py-3 text-right">Pemakaian MTD</th>
                            <th class="px-4 py-3 text-right">Tegangan</th>
                            <th class="px-4 py-3 text-right">Arus</th>
                            <th class="px-4 py-3 text-right">Cos φ</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-xs sm:text-sm tabular-nums">
                        @forelse($meters as $meter)
                            @php
                                $latestLog = $meter->energyLogs->first();
                                $mtdUsage = (float) $meter->energyLogs->sum('kwh_usage');
                                $isIncoming = $meter->panel_code === 'INCOMING';
                                $pf = $latestLog ? (float) $latestLog->power_factor : 0.96;
                                $isLowPf = $pf < 0.90;
                            @endphp
                            <tr class="hover:bg-zinc-50/80 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900">{{ $meter->panel_code }}</div>
                                    <div class="text-xs text-zinc-500">{{ $meter->panel_name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($isIncoming)
                                        <span class="inline-block bg-amber-100 text-amber-900 border border-amber-200 text-[11px] font-semibold px-2.5 py-0.5 rounded-md">
                                            Incoming PLN
                                        </span>
                                    @else
                                        <span class="inline-block bg-zinc-100 text-zinc-700 border border-zinc-200 text-[11px] font-medium px-2.5 py-0.5 rounded-md">
                                            Distribusi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs font-semibold text-zinc-800">
                                        {{ $meter->converter_name ?? 'USR-DR302' }} · ID #{{ $meter->slave_id }}
                                    </div>
                                    <div class="text-[11px] font-mono text-zinc-400">{{ $meter->ip_address }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-zinc-800">
                                    {{ $latestLog ? number_format((float) $latestLog->kwh_total, 2, ',', '.') . ' kWh' : '0,00 kWh' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-zinc-900">
                                    {{ number_format($mtdUsage, 2, ',', '.') }} kWh
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-700">
                                    {{ $latestLog ? number_format((float) $latestLog->voltage_avg, 1, ',', '.') . ' V' : '380,0 V' }}
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-700">
                                    {{ $latestLog ? number_format((float) $latestLog->current_avg, 1, ',', '.') . ' A' : '0,0 A' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold {{ $isLowPf ? 'text-amber-600' : 'text-zinc-800' }}">
                                    {{ number_format($pf, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($isLowPf)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700">
                                            <span>⚠</span>
                                            <span>PF Rendah</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                            <span>●</span>
                                            <span>Normal</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-zinc-400 text-sm">
                                    Belum ada data panel yang sesuai dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    @php
        $panelLabels = [];
        $panelMtdTotals = [];
        foreach ($meters as $m) {
            $panelLabels[] = $m->panel_code;
            $panelMtdTotals[] = round((float) $m->energyLogs->sum('kwh_usage'), 2);
        }
    @endphp

    <script>
        const dailyLabels = @json($chartLabels);
        const incomingDailyData = @json($incomingDailySeries);
        const distributionDailyData = @json($distributionDailySeries);

        const panelLabels = @json($panelLabels);
        const panelTotalsData = @json($panelMtdTotals);

        const ctx = document.getElementById('energyChart').getContext('2d');

        let energyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dailyLabels,
                datasets: [
                    {
                        label: 'Incoming PLN (kWh/hari)',
                        data: incomingDailyData,
                        backgroundColor: '#facc15',
                        hoverBackgroundColor: '#eab308',
                        borderRadius: 4,
                        barPercentage: 0.72,
                        categoryPercentage: 0.75
                    },
                    {
                        label: 'Total Distribusi Panel',
                        data: distributionDailyData,
                        backgroundColor: '#18181b',
                        hoverBackgroundColor: '#27272a',
                        borderRadius: 4,
                        barPercentage: 0.72,
                        categoryPercentage: 0.75
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#18181b',
                        titleColor: '#facc15',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#71717a', font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#52525b', font: { size: 11 } }
                    }
                }
            }
        });

        function switchChartMode(mode) {
            const btnDaily = document.getElementById('btnTrenHarian');
            const btnPanel = document.getElementById('btnPorsiPanel');
            const legendBox = document.getElementById('chartLegendBox');

            if (mode === 'panel') {
                btnPanel.className = 'bg-zinc-900 text-white px-3 py-1 rounded-md cursor-pointer';
                btnDaily.className = 'text-zinc-600 hover:text-zinc-900 px-3 py-1 rounded-md cursor-pointer';
                legendBox.innerHTML = `<span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>Pemakaian MTD per Panel (kWh)</span>`;

                energyChart.data.labels = panelLabels;
                energyChart.data.datasets = [{
                    label: 'Pemakaian MTD (kWh)',
                    data: panelTotalsData,
                    backgroundColor: '#facc15',
                    borderColor: '#18181b',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.55
                }];
            } else {
                btnDaily.className = 'bg-zinc-900 text-white px-3 py-1 rounded-md cursor-pointer';
                btnPanel.className = 'text-zinc-600 hover:text-zinc-900 px-3 py-1 rounded-md cursor-pointer';
                legendBox.innerHTML = `
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>Incoming PLN (kWh/hari)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-zinc-900 inline-block"></span>Total Distribusi Panel</span>
                `;

                energyChart.data.labels = dailyLabels;
                energyChart.data.datasets = [
                    {
                        label: 'Incoming PLN (kWh/hari)',
                        data: incomingDailyData,
                        backgroundColor: '#facc15',
                        hoverBackgroundColor: '#eab308',
                        borderRadius: 4,
                        barPercentage: 0.72,
                        categoryPercentage: 0.75
                    },
                    {
                        label: 'Total Distribusi Panel',
                        data: distributionDailyData,
                        backgroundColor: '#18181b',
                        hoverBackgroundColor: '#27272a',
                        borderRadius: 4,
                        barPercentage: 0.72,
                        categoryPercentage: 0.75
                    }
                ];
            }

            energyChart.update();
        }

        function exportTableToCSV(filename) {
            const rows = document.querySelectorAll('#panelSummaryTable tr');
            const csv = [];

            for (let i = 0; i < rows.length; i++) {
                const cols = rows[i].querySelectorAll('td, th');
                if (cols.length === 0) continue;
                const row = [];
                for (let j = 0; j < cols.length; j++) {
                    const cleanText = cols[j].innerText
                        .replace(/(\r\n|\n|\r)/gm, ' ')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .replace(/"/g, '""');
                    row.push('"' + cleanText + '"');
                }
                csv.push(row.join(';'));
            }

            const blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>