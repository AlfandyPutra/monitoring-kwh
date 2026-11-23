<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMM Plastic | Energy & kWh Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f4f4f5; }
    </style>
</head>
<body class="text-zinc-800 antialiased">

    <!-- Top Navbar (Identitas Warna MMM Plastic: Hitam Arang + Aksen Kuning Emas) -->
    <nav class="bg-zinc-900 border-b-4 border-yellow-400 text-white shadow-md sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap justify-between items-center gap-4">
            <!-- Brand & Logo SVG MMM Plastic -->
            <div class="flex items-center gap-4">
                <div class="bg-white px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-2.5">
                    <!-- Ikon Vektor 3D Pita Kuning-Hitam-Abu khas MMM Plastic -->
                    <svg width="38" height="32" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="10,75 32,88 32,58 10,45" fill="#18181B"/>
                        <polygon points="10,45 32,58 55,44 33,31" fill="#71717A"/>
                        <polygon points="35,60 57,73 57,43 35,30" fill="#FACC15"/>
                        <polygon points="35,30 57,43 80,29 58,16" fill="#71717A"/>
                        <polygon points="60,45 82,58 82,28 60,15" fill="#18181B"/>
                        <polygon points="60,15 82,28 105,14 83,1" fill="#71717A"/>
                        <polygon points="85,30 107,43 107,13 85,0" fill="#FACC15"/>
                        <polygon points="85,62 107,49 107,35 85,48" fill="#71717A"/>
                    </svg>
                    <div class="leading-none">
                        <span class="block text-zinc-900 font-extrabold text-lg tracking-tight">MMM</span>
                        <span class="block text-zinc-500 font-bold text-[9px] tracking-[0.28em] mt-0.5">PLASTIC</span>
                    </div>
                </div>
                <div class="border-l border-zinc-700 pl-4">
                    <h1 class="text-base sm:text-lg font-bold tracking-wide text-white">ENERGY MONITORING SYSTEM</h1>
                    <p class="text-xs text-zinc-400">PT Mitra Mulia Makmur — Plant Nganjuk (Utility & Power Meter)</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5">
                <form action="{{ route('simulate.fetch') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-yellow-400 hover:bg-yellow-300 text-zinc-900 font-bold text-xs sm:text-sm px-4 py-2 rounded-lg shadow transition flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-zinc-900 animate-ping"></span>
                        <span>Tarik Data kWh (Simulasi)</span>
                    </button>
                </form>
                <button onclick="exportTableToCSV('monitoring_kwh_mmm.csv')" class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-600 text-white text-xs sm:text-sm font-medium px-3.5 py-2 rounded-lg transition cursor-pointer">
                    📥 Export Excel
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Notifikasi Sukses -->
        @if(session('success'))
        <div class="mb-5 bg-yellow-50 border-l-4 border-yellow-400 text-zinc-800 px-4 py-3 rounded-r-lg shadow-sm flex justify-between items-center">
            <div class="flex items-center gap-2 text-sm font-medium">
                <span class="text-yellow-600 font-bold">⚡ Sukses:</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-zinc-400 hover:text-zinc-700 font-bold">&times;</button>
        </div>
        @endif

        <!-- Filter Bar Dinamis -->
        <div class="bg-white rounded-xl shadow-sm border border-zinc-200 p-4 mb-6">
            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-zinc-600 uppercase tracking-wider mb-1.5">Periode Bulan (MtD)</label>
                    <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                        class="w-full border border-zinc-300 rounded-lg px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-600 uppercase tracking-wider mb-1.5">Converter Gateway</label>
                    <select name="converter" onchange="this.form.submit()"
                        class="w-full border border-zinc-300 rounded-lg px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                        <option value="ALL" {{ $converter == 'ALL' ? 'selected' : '' }}>Semua Converter (USR-DR302)</option>
                        <option value="USR-DR302 #1" {{ $converter == 'USR-DR302 #1' ? 'selected' : '' }}>USR-DR302 #1 (192.168.1.201)</option>
                        <option value="USR-DR302 #2" {{ $converter == 'USR-DR302 #2' ? 'selected' : '' }}>USR-DR302 #2 (192.168.1.202)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-600 uppercase tracking-wider mb-1.5">Filter Panel Listrik</label>
                    <select name="panel" onchange="this.form.submit()"
                        class="w-full border border-zinc-300 rounded-lg px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                        <option value="ALL" {{ $panelCode == 'ALL' ? 'selected' : '' }}>Semua Panel ({{ $allMeters->count() }} Unit)</option>
                        @foreach($allMeters as $m)
                            <option value="{{ $m->panel_code }}" {{ $panelCode == $m->panel_code ? 'selected' : '' }}>
                                {{ $m->panel_code }} — {{ $m->panel_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="block text-center w-full bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold py-2 px-4 rounded-lg text-sm transition border border-zinc-300">
                        Reset Filter
                    </a>
                </div>
            </form>
        </div>

        @php
            $totalKwhMtd = 0;
            $avgVoltageList = [];
            $totalCurrent = 0;
            $chartLabels = [];
            $chartKwhValues = [];

            foreach ($meters as $m) {
                $mtdUsage = $m->energyLogs->sum('kwh_usage');
                $latest = $m->energyLogs->first();
                $totalKwhMtd += $mtdUsage;
                if ($latest) {
                    $avgVoltageList[] = $latest->voltage_avg;
                    $totalCurrent += $latest->current_avg;
                }
                $chartLabels[] = $m->panel_code;
                $chartKwhValues[] = round($mtdUsage, 2);
            }
            $overallVoltage = count($avgVoltageList) > 0 ? round(array_sum($avgVoltageList) / count($avgVoltageList), 1) : 0;
        @endphp

        <!-- 4 Kartu Ringkasan KPI Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 border border-zinc-200 shadow-sm border-l-4 border-l-yellow-400">
                <p class="text-xs font-bold text-zinc-500 uppercase">Total Pemakaian (MtD)</p>
                <p class="text-2xl font-extrabold text-zinc-900 mt-1">{{ number_format($totalKwhMtd, 2, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">kWh</span></p>
                <p class="text-xs text-zinc-400 mt-1">Akumulasi bulan {{ $month }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 border border-zinc-200 shadow-sm border-l-4 border-l-zinc-900">
                <p class="text-xs font-bold text-zinc-500 uppercase">Panel Terhubung</p>
                <p class="text-2xl font-extrabold text-zinc-900 mt-1">{{ $meters->count() }} <span class="text-sm font-semibold text-emerald-600">● Online</span></p>
                <p class="text-xs text-zinc-400 mt-1">Via 2x Gateway USR-DR302</p>
            </div>
            <div class="bg-white rounded-xl p-5 border border-zinc-200 shadow-sm border-l-4 border-l-zinc-500">
                <p class="text-xs font-bold text-zinc-500 uppercase">Rata-Rata Tegangan</p>
                <p class="text-2xl font-extrabold text-zinc-900 mt-1">{{ $overallVoltage }} <span class="text-sm font-semibold text-zinc-500">Volt</span></p>
                <p class="text-xs text-zinc-400 mt-1">Standar 3-Phase (380V ±5%)</p>
            </div>
            <div class="bg-white rounded-xl p-5 border border-zinc-200 shadow-sm border-l-4 border-l-yellow-500">
                <p class="text-xs font-bold text-zinc-500 uppercase">Total Beban Arus</p>
                <p class="text-2xl font-extrabold text-zinc-900 mt-1">{{ number_format($totalCurrent, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">Ampere</span></p>
                <p class="text-xs text-zinc-400 mt-1">Pembacaan terakhir panel aktif</p>
            </div>
        </div>

        <!-- Grid Grafik & Status Kartu Per Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Grafik Konsumsi kWh per Panel -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-zinc-200 p-5">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900">Distribusi Pemakaian Energi per Panel (kWh)</h2>
                        <p class="text-xs text-zinc-500">Perbandingan konsumsi Month to Date ({{ $month }})</p>
                    </div>
                    <span class="bg-yellow-100 text-zinc-900 text-xs font-bold px-2.5 py-1 rounded-md border border-yellow-300">Live Chart</span>
                </div>
                <div class="h-64">
                    <canvas id="kwhBarChart"></canvas>
                </div>
            </div>

            <!-- Info Jaringan Converter USR-DR302 -->
            <div class="bg-zinc-900 text-white rounded-xl shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-zinc-700 pb-3 mb-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-yellow-400">Status Gateway Modbus</h2>
                        <span class="text-xs bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-semibold">TCP Port 502</span>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="bg-zinc-800/80 p-3 rounded-lg border border-zinc-700">
                            <div class="flex justify-between font-bold">
                                <span>USR-DR302 #1</span>
                                <span class="text-yellow-400">192.168.1.201</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-1">RS485 Slave ID: 1 (INCOMING), 2 (MDP-01), 3 (MDP-02)</p>
                        </div>
                        <div class="bg-zinc-800/80 p-3 rounded-lg border border-zinc-700">
                            <div class="flex justify-between font-bold">
                                <span>USR-DR302 #2</span>
                                <span class="text-yellow-400">192.168.1.202</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-1">RS485 Slave ID: 1 (SDP-D1), 2 (SDP-E1)</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-700 text-xs text-zinc-400 flex justify-between">
                    <span>Switch: Linksys LGS116</span>
                    <span>Koneksi: RJ45 LAN</span>
                </div>
            </div>
        </div>

        <!-- Tabel Monitoring Detail Panel -->
        <div class="bg-white rounded-xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="bg-zinc-900 px-5 py-3.5 flex justify-between items-center border-b-2 border-yellow-400">
                <h2 class="text-sm sm:text-base font-bold text-white">REKAPITULASI PEMBACAAN kWh METER PER PANEL</h2>
                <span class="text-xs text-yellow-400 font-semibold">Update Terakhir: {{ now()->format('d M Y H:i') }} WIB</span>
            </div>
            <div class="overflow-x-auto">
                <table id="kwhTable" class="w-full text-sm text-left border-collapse">
                    <thead class="bg-zinc-100 text-zinc-700 uppercase text-xs font-bold border-b border-zinc-200">
                        <tr>
                            <th class="px-4 py-3">Kode Panel</th>
                            <th class="px-4 py-3">Nama Panel</th>
                            <th class="px-4 py-3">Gateway & IP</th>
                            <th class="px-4 py-3 text-center">Slave ID</th>
                            <th class="px-4 py-3 text-right">Stand Meter Akhir (kWh)</th>
                            <th class="px-4 py-3 text-right bg-yellow-50 text-zinc-900">Pemakaian MtD (kWh)</th>
                            <th class="px-4 py-3 text-right">Tegangan (V)</th>
                            <th class="px-4 py-3 text-right">Arus (A)</th>
                            <th class="px-4 py-3 text-center">Cos φ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($meters as $meter)
                            @php
                                $latestLog = $meter->energyLogs->first();
                                $mtdKwh = $meter->energyLogs->sum('kwh_usage');
                            @endphp
                            <tr class="hover:bg-yellow-50/40 transition">
                                <td class="px-4 py-3 font-extrabold text-zinc-900">{{ $meter->panel_code }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $meter->panel_name }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-semibold bg-zinc-200 text-zinc-800 px-2 py-0.5 rounded">{{ $meter->converter_name }}</span>
                                    <span class="block text-xs text-zinc-500 mt-0.5">{{ $meter->ip_address }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-zinc-700">#{{ $meter->slave_id }}</td>
                                <td class="px-4 py-3 text-right font-mono font-semibold text-zinc-800">
                                    {{ $latestLog ? number_format($latestLog->kwh_total, 2, ',', '.') : '0,00' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-extrabold text-zinc-900 bg-yellow-50/70">
                                    {{ number_format($mtdKwh, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-zinc-700">
                                    {{ $latestLog ? $latestLog->voltage_avg . ' V' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-zinc-700">
                                    {{ $latestLog ? $latestLog->current_avg . ' A' : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-semibold text-emerald-700">
                                    {{ $latestLog ? $latestLog->power_factor : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-zinc-500">Data panel tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        // Render Grafik Bar Chart.js dengan tema warna MMM Plastic (Kuning Emas & Hitam)
        const ctx = document.getElementById('kwhBarChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Pemakaian Energi (kWh)',
                    data: @json($chartKwhValues),
                    backgroundColor: '#facc15',
                    borderColor: '#18181b',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: '#eab308'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e4e4e7' },
                        ticks: { font: { family: 'Inter', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', weight: 'bold', size: 12 } }
                    }
                }
            }
        });

        // Fungsi Export Tabel ke Excel (CSV)
        function exportTableToCSV(filename) {
            let csv = [];
            let rows = document.querySelectorAll("#kwhTable tr");
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) {
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/;/g, ",");
                    row.push('"' + data.trim() + '"');
                }
                csv.push(row.join(";"));
            }
            let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
            let downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
    </script>
</body>
</html>