<?php
/**
 * View: Prediksi Kondisi Jalan Setelah Penanganan
 * Menampilkan perbandingan kondisi jalan SEBELUM vs PREDIKSI SESUDAH penanganan
 * berdasarkan matriks logika Ide Strip Map.
 */

// Helper: format km
$fkm = fn($v) => number_format((float)$v, 2, ',', '.');

// Encode data untuk chart
$chartLabels  = [];
$chartSebelumBaik   = [];
$chartSebelumSedang = [];
$chartSebelumRR     = [];
$chartSebelumRB     = [];
$chartSesudahBaik   = [];
$chartSesudahSedang = [];
$chartSesudahRR     = [];
$chartSesudahRB     = [];

foreach ($perRuas as $r) {
    if (!$r['ada_penanganan']) continue; // hanya tampilkan ruas yang ada penanganan
    $chartLabels[]        = $r['kode_ruas'];
    $chartSebelumBaik[]   = $r['sebelum']['baik_km'];
    $chartSebelumSedang[] = $r['sebelum']['sedang_km'];
    $chartSebelumRR[]     = $r['sebelum']['rusak_ringan_km'];
    $chartSebelumRB[]     = $r['sebelum']['rusak_berat_km'];
    $chartSesudahBaik[]   = $r['sesudah']['baik_km'];
    $chartSesudahSedang[] = $r['sesudah']['sedang_km'];
    $chartSesudahRR[]     = $r['sesudah']['rusak_ringan_km'];
    $chartSesudahRB[]     = $r['sesudah']['rusak_berat_km'];
}

$mantapDelta = round($totalSesudah['pct_mantap'] - $totalSebelum['pct_mantap'], 1);
$deltaPositif = $mantapDelta >= 0;
?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('prediksiChart', () => ({
        chartRendered: false,
        initChart() {
            if (this.chartRendered) return;
            if (typeof Chart === 'undefined' || typeof ChartDataLabels === 'undefined') {
                setTimeout(() => this.initChart(), 100);
                return;
            }
            // Register plugin datalabels secara eksplisit
            Chart.register(ChartDataLabels);
            this.chartRendered = true;
            this.$nextTick(() => {
                this.renderKomparChart();
                this.renderDistribusiBar();
                this.renderKemantapanBar();
                this.renderMultiTahunBar();
            });
        },
        renderMultiTahunBar() {
            const ctx = document.getElementById('chartMultiTahun');
            if (!ctx) return;
            // Data disiapkan dari PHP (mode semua)
            const allYears   = <?= json_encode(array_keys($allYearsData ?? [])) ?>;
            const pctSebelum = <?= json_encode(array_values(array_map(fn($v) => $v['pct_sebelum'], $allYearsData ?? []))) ?>;
            const pctSesudah = <?= json_encode(array_values(array_map(fn($v) => $v['pct_sesudah'], $allYearsData ?? []))) ?>;
            if (!allYears.length) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: allYears.map(y => String(y)),
                    datasets: [
                        {
                            label: 'Saat Ini / Target (n-1)',
                            data: pctSebelum,
                            backgroundColor: 'rgba(99,102,241,0.82)',
                            borderColor: '#4f46e5',
                            borderWidth: 2,
                            borderRadius: 7,
                            borderSkipped: false,
                        },
                        {
                            label: 'Prediksi Setelah Penanganan (n)',
                            data: pctSesudah,
                            backgroundColor: 'rgba(139,92,246,0.38)',
                            borderColor: '#7c3aed',
                            borderWidth: 2,
                            borderRadius: 7,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 22 } },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: { font: { size: 11, weight: '600' }, padding: 16, boxWidth: 14, boxHeight: 14, usePointStyle: true, pointStyle: 'rectRounded' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return '  ' + ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(1) + '%'; }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            offset: 2,
                            formatter: function(value) { return value.toFixed(1) + '%'; },
                            font: { size: 10, weight: '700' },
                            color: function(ctx) { return ctx.datasetIndex === 0 ? '#4f46e5' : '#7c3aed'; },
                            clip: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 12, weight: '700' } }
                        },
                        y: {
                            min: 0,
                            max: 100,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 11 }, callback: function(v) { return v + '%'; } },
                            title: { display: true, text: 'Kemantapan (%)', font: { size: 11 }, color: '#6b7280' }
                        }
                    }
                }
            });
        },
        renderKomparChart() {
            const ctx = document.getElementById('chartKomparasi');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [
                        { label: 'Sebelum \u2014 Baik', data: <?= json_encode($chartSebelumBaik) ?>, backgroundColor: 'rgba(34,197,94,0.85)', borderColor: '#16a34a', borderWidth: 1, borderRadius: 3, stack: 'sebelum' },
                        { label: 'Sebelum \u2014 Sedang', data: <?= json_encode($chartSebelumSedang) ?>, backgroundColor: 'rgba(234,179,8,0.85)', borderColor: '#ca8a04', borderWidth: 1, borderRadius: 3, stack: 'sebelum' },
                        { label: 'Sebelum \u2014 Rusak Ringan', data: <?= json_encode($chartSebelumRR) ?>, backgroundColor: 'rgba(249,115,22,0.85)', borderColor: '#ea580c', borderWidth: 1, borderRadius: 3, stack: 'sebelum' },
                        { label: 'Sebelum \u2014 Rusak Berat', data: <?= json_encode($chartSebelumRB) ?>, backgroundColor: 'rgba(239,68,68,0.85)', borderColor: '#dc2626', borderWidth: 1, borderRadius: 3, stack: 'sebelum' },
                        { label: 'Prediksi \u2014 Baik', data: <?= json_encode($chartSesudahBaik) ?>, backgroundColor: 'rgba(34,197,94,0.35)', borderColor: '#16a34a', borderWidth: 2, borderRadius: 3, stack: 'sesudah' },
                        { label: 'Prediksi \u2014 Sedang', data: <?= json_encode($chartSesudahSedang) ?>, backgroundColor: 'rgba(234,179,8,0.35)', borderColor: '#ca8a04', borderWidth: 2, borderRadius: 3, stack: 'sesudah' },
                        { label: 'Prediksi \u2014 Rusak Ringan', data: <?= json_encode($chartSesudahRR) ?>, backgroundColor: 'rgba(249,115,22,0.35)', borderColor: '#ea580c', borderWidth: 2, borderRadius: 3, stack: 'sesudah' },
                        { label: 'Prediksi \u2014 Rusak Berat', data: <?= json_encode($chartSesudahRB) ?>, backgroundColor: 'rgba(239,68,68,0.35)', borderColor: '#dc2626', borderWidth: 2, borderRadius: 3, stack: 'sesudah' },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 12, boxWidth: 12, boxHeight: 12 } },
                        tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' km'; } } }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { stacked: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 }, callback: function(v) { return v + ' km'; } }, title: { display: true, text: 'Panjang (km)', font: { size: 11 } } }
                    }
                }
            });
        },
        renderDistribusiBar() {
            const ctx = document.getElementById('chartDistribusiBar');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'],
                    datasets: [
                        {
                            label: 'Saat Ini (n-1)',
                            data: [<?= $totalSebelum['baik_km'] ?>, <?= $totalSebelum['sedang_km'] ?>, <?= $totalSebelum['rusak_ringan_km'] ?>, <?= $totalSebelum['rusak_berat_km'] ?>],
                            backgroundColor: ['rgba(34,197,94,0.85)', 'rgba(234,179,8,0.85)', 'rgba(249,115,22,0.85)', 'rgba(239,68,68,0.85)'],
                            borderColor:     ['#16a34a', '#ca8a04', '#ea580c', '#dc2626'],
                            borderWidth: 1.5,
                            borderRadius: 5,
                            borderSkipped: false,
                        },
                        {
                            label: 'Prediksi (n)',
                            data: [<?= $totalSesudah['baik_km'] ?>, <?= $totalSesudah['sedang_km'] ?>, <?= $totalSesudah['rusak_ringan_km'] ?>, <?= $totalSesudah['rusak_berat_km'] ?>],
                            backgroundColor: ['rgba(34,197,94,0.28)', 'rgba(234,179,8,0.28)', 'rgba(249,115,22,0.28)', 'rgba(239,68,68,0.28)'],
                            borderColor:     ['#16a34a', '#ca8a04', '#ea580c', '#dc2626'],
                            borderWidth: 2,
                            borderRadius: 5,
                            borderSkipped: false,
                            borderDash: [4, 3],
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: { font: { size: 11, weight: '600' }, padding: 14, boxWidth: 14, boxHeight: 14, usePointStyle: true, pointStyle: 'rectRounded' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return '  ' + ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' km'; }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '600' } }
                        },
                        y: {
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 10 }, callback: function(v) { return v.toFixed(0) + ' km'; } },
                            title: { display: true, text: 'Panjang (km)', font: { size: 11 }, color: '#6b7280' }
                        }
                    }
                }
            });
        },
        renderKemantapanBar() {
            const ctx = document.getElementById('chartKemantapanBar');
            if (!ctx) return;
            const tahun = <?= (int)$tahunPenanganan ?>;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['n-1 (Data Saat Ini)', 'n (Prediksi <?= (int)$tahunPenanganan ?>)'],
                    datasets: [
                        {
                            label: 'Kemantapan (%)',
                            data: [<?= $totalSebelum['pct_mantap'] ?>, <?= $totalSesudah['pct_mantap'] ?>],
                            backgroundColor: [
                                'rgba(99,102,241,0.80)',
                                'rgba(139,92,246,0.45)'
                            ],
                            borderColor: ['#4f46e5', '#7c3aed'],
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.45,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return '  Kemantapan: ' + ctx.parsed.y.toFixed(1) + '%'; }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '700' } }
                        },
                        y: {
                            min: 0,
                            max: 100,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 10 }, callback: function(v) { return v + '%'; } },
                            title: { display: true, text: 'Kemantapan (%)', font: { size: 11 }, color: '#6b7280' }
                        }
                    }
                }
            });
        }
    }));
});
</script>

<div x-data="prediksiChart()" x-init="initChart()" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= base_url() ?>" class="text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-blue-600">Prediksi Kondisi Jalan</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <span>Prediksi Kondisi Jalan Setelah Penanganan</span>
                <span class="text-xs px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 font-semibold border border-purple-200">Tahun <?= $tahunPenanganan ?></span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Prediksi kondisi jalan berdasarkan matriks penanganan (Ide Strip Map). Warna solid = kondisi saat ini (n-1), transparan = prediksi setelah penanganan (n).</p>
        </div>

        <!-- Filter Tahun -->
        <form method="GET" action="" class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-600">Tahun Penanganan:</label>
            <select name="tahun" onchange="this.form.submit()"
                class="text-sm font-semibold border border-gray-300 rounded-xl px-3 py-2 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="semua" <?= $tahunPenanganan === 'semua' ? 'selected' : '' ?>>🗓 Semua Tahun</option>
                <?php foreach (range(2025, 2030) as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $tahunPenanganan ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($modeSemua): ?>
    <!-- ===== SECTION: Chart Multi-Tahun (Mode Semua) ===== -->
    <div class="bg-white p-6 rounded-2xl border border-indigo-200 shadow-sm">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Kemantapan Jaringan Jalan — Semua Tahun Penanganan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbandingan kemantapan <strong>saat ini / target (n-1)</strong> vs <strong>prediksi setelah penanganan (n)</strong> untuk setiap tahun (2025–2030).</p>
            </div>
            <span class="shrink-0 text-xs px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 font-semibold border border-indigo-200">2025 – 2030</span>
        </div>
        <div class="relative" style="height: 320px">
            <canvas id="chartMultiTahun"></canvas>
        </div>
        <!-- Mini tabel nilai per tahun -->
        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-3 py-2 text-left font-bold text-gray-600">Tahun</th>
                        <th class="px-3 py-2 text-center font-bold text-indigo-700 bg-indigo-50">Saat Ini / Target (%)</th>
                        <th class="px-3 py-2 text-center font-bold text-purple-700 bg-purple-50">Prediksi Setelah Penanganan (%)</th>
                        <th class="px-3 py-2 text-center font-bold text-gray-600">Delta (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($allYearsData as $thn => $d): ?>
                    <?php $delta = round($d['pct_sesudah'] - $d['pct_sebelum'], 1); ?>
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-3 py-2.5 font-black text-gray-800"><?= $thn ?></td>
                        <td class="px-3 py-2.5 text-center bg-indigo-50/50">
                            <span class="font-bold text-indigo-700"><?= $d['pct_sebelum'] ?>%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center bg-purple-50/50">
                            <span class="font-bold text-purple-700"><?= $d['pct_sesudah'] ?>%</span>
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold <?= $delta > 0 ? 'text-green-600' : ($delta < 0 ? 'text-red-600' : 'text-gray-400') ?>">
                                <?= $delta >= 0 ? '+' : '' ?><?= $delta ?> poin
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI Summary Cards -->
    <?php
    $mantapSebelumKm = $totalSebelum['baik_km'] + $totalSebelum['sedang_km'];
    $mantapSesudahKm = $totalSesudah['baik_km'] + $totalSesudah['sedang_km'];
    $deltaKm = round($mantapSesudahKm - $mantapSebelumKm, 3);
    $deltaM  = round($deltaKm * 1000);
    ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Bar Chart: Kemantapan Saat Ini vs Prediksi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm flex flex-col">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Kemantapan: Saat Ini vs Prediksi</p>
            <div class="relative flex-1" style="min-height:160px">
                <canvas id="chartKemantapanBar"></canvas>
            </div>
        </div>
        <!-- KPI: Kemantapan Saat Ini -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kemantapan Saat Ini</p>
            <div class="flex items-baseline gap-1.5">
                <span class="text-3xl font-black text-gray-900"><?= $totalSebelum['pct_mantap'] ?></span>
                <span class="text-sm font-semibold text-gray-500">%</span>
            </div>
            <p class="text-xs text-gray-500 mt-1"><?= $fkm($mantapSebelumKm) ?> km mantap dari <?= $fkm($totalPanjangKm) ?> km</p>
            <div class="mt-3 space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-green-600 font-semibold">Baik</span>
                    <span class="font-mono text-gray-700"><?= $fkm($totalSebelum['baik_km']) ?> km</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-yellow-600 font-semibold">Sedang</span>
                    <span class="font-mono text-gray-700"><?= $fkm($totalSebelum['sedang_km']) ?> km</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-orange-500 font-semibold">Rusak Ringan</span>
                    <span class="font-mono text-gray-700"><?= $fkm($totalSebelum['rusak_ringan_km']) ?> km</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-red-600 font-semibold">Rusak Berat</span>
                    <span class="font-mono text-gray-700"><?= $fkm($totalSebelum['rusak_berat_km']) ?> km</span>
                </div>
            </div>
        </div>
        <!-- KPI: Prediksi Kemantapan -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-600 p-5 rounded-2xl shadow-lg shadow-purple-500/25">
            <p class="text-xs font-bold text-white/70 uppercase tracking-wider mb-2">Prediksi Kemantapan</p>
            <div class="flex items-baseline gap-1.5">
                <span class="text-3xl font-black text-white"><?= $totalSesudah['pct_mantap'] ?></span>
                <span class="text-sm font-semibold text-white/80">%</span>
            </div>
            <div class="flex flex-col gap-0.5 mt-1 mb-3">
                <span class="text-xs font-bold text-white/90">
                    <?= $deltaPositif ? '↑' : ($mantapDelta == 0 ? '→' : '↓') ?>
                    <?= abs($mantapDelta) ?> poin <?= $deltaPositif ? 'naik' : ($mantapDelta == 0 ? '(belum berubah)' : 'turun') ?>
                </span>
                <span class="text-[11px] font-semibold text-white/70">
                    Delta: <?= $deltaKm >= 0 ? '+' : '' ?><?= number_format($deltaKm, 3, ',', '.') ?> km
                    (<?= $deltaM >= 0 ? '+' : '' ?><?= number_format($deltaM, 0, ',', '.') ?> m)
                </span>
            </div>
            <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-green-300 font-semibold">Baik</span>
                    <span class="font-mono text-white/80"><?= $fkm($totalSesudah['baik_km']) ?> km</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-yellow-300 font-semibold">Sedang</span>
                    <span class="font-mono text-white/80"><?= $fkm($totalSesudah['sedang_km']) ?> km</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-orange-300 font-semibold">Rusak Ringan</span>
                    <span class="font-mono text-white/80"><?= $fkm($totalSesudah['rusak_ringan_km']) ?> km</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-red-300 font-semibold">Rusak Berat</span>
                    <span class="font-mono text-white/80"><?= $fkm($totalSesudah['rusak_berat_km']) ?> km</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Chart Distribusi Kondisi: Saat Ini vs Prediksi -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Distribusi Kondisi Jalan: Saat Ini vs Prediksi</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbandingan panjang jalan per kondisi sebelum dan setelah penanganan tahun <?= $tahunPenanganan ?>.</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded bg-green-500 opacity-90"></div>
                    <span class="text-gray-600 font-medium">Solid = Saat Ini (n-1)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded border-2 border-green-500 bg-green-200/30"></div>
                    <span class="text-gray-600 font-medium">Transparan = Prediksi (n)</span>
                </div>
            </div>
        </div>
        <div class="relative h-72">
            <canvas id="chartDistribusiBar"></canvas>
        </div>
    </div>

    <!-- Bar Chart Komparasi Per Ruas -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Komparasi Kondisi Per Ruas Jalan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Hanya ruas yang memiliki data penanganan tahun <?= $tahunPenanganan ?> yang ditampilkan.</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded bg-green-500 opacity-90"></div>
                    <span class="text-gray-600 font-medium">Solid = Saat Ini</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded border-2 border-green-500 bg-green-200/40"></div>
                    <span class="text-gray-600 font-medium">Transparan = Prediksi</span>
                </div>
            </div>
        </div>
        <?php if (empty($chartLabels)): ?>
            <div class="flex flex-col items-center justify-center h-48 text-center">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm font-semibold text-gray-500">Belum ada data penanganan untuk tahun <?= $tahunPenanganan ?></p>
                <p class="text-xs text-gray-400 mt-1">Tambahkan data penanganan dan pilih <strong>Jenis Pelaksana</strong> pada halaman Strip Map.</p>
            </div>
        <?php else: ?>
            <div class="relative h-80">
                <canvas id="chartKomparasi"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Detail Per Ruas -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Detail Per Ruas Jalan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbandingan kemantapan sebelum dan prediksi sesudah penanganan per ruas.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Kode / Nama Ruas</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Panjang</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider bg-amber-50 border-l border-amber-100" colspan="2">Kondisi Saat Ini (n-1)</th>
                        <th class="px-4 py-3 text-center font-bold text-purple-600 uppercase tracking-wider bg-purple-50 border-l border-purple-100" colspan="2">Prediksi Sesudah (n)</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Penanganan</th>
                    </tr>
                    <tr class="text-[10px] text-gray-500">
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2 text-center">km</th>
                        <th class="px-4 py-2 text-center bg-amber-50 border-l border-amber-100">Mantap %</th>
                        <th class="px-4 py-2 text-center bg-amber-50">Rusak Berat km</th>
                        <th class="px-4 py-2 text-center bg-purple-50 border-l border-purple-100">Mantap %</th>
                        <th class="px-4 py-2 text-center bg-purple-50">Rusak Berat km</th>
                        <th class="px-4 py-2 text-center">Segmen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($perRuas as $r): ?>
                    <tr class="hover:bg-gray-50/80 transition-colors <?= !$r['ada_penanganan'] ? 'opacity-50' : '' ?>">
                        <td class="px-4 py-3">
                            <a href="<?= base_url('rekap/prediksi/' . $r['id'] . '?tahun=' . $tahunPenanganan) ?>"
                               class="font-bold text-blue-600 hover:underline"><?= htmlspecialchars($r['kode_ruas']) ?></a>
                            <p class="text-gray-500 mt-0.5 line-clamp-1"><?= htmlspecialchars($r['nama_ruas']) ?></p>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700"><?= $fkm($r['panjang_km']) ?></td>
                        <!-- Sebelum -->
                        <td class="px-4 py-3 text-center bg-amber-50/40 border-l border-amber-100">
                            <?php
                            $pct = $r['sebelum']['pct_mantap'];
                            $color = $pct >= 80 ? 'text-green-600' : ($pct >= 60 ? 'text-amber-600' : 'text-red-600');
                            ?>
                            <span class="font-bold <?= $color ?>"><?= $pct ?>%</span>
                        </td>
                        <td class="px-4 py-3 text-center bg-amber-50/40">
                            <span class="font-semibold text-red-600"><?= $fkm($r['sebelum']['rusak_berat_km']) ?></span>
                        </td>
                        <!-- Sesudah -->
                        <td class="px-4 py-3 text-center bg-purple-50/40 border-l border-purple-100">
                            <?php
                            $pctS = $r['sesudah']['pct_mantap'];
                            $delta = round($pctS - $r['sebelum']['pct_mantap'], 1);
                            $colorS = $pctS >= 80 ? 'text-green-600' : ($pctS >= 60 ? 'text-amber-600' : 'text-red-600');
                            ?>
                            <div class="flex items-center justify-center gap-1">
                                <span class="font-bold <?= $colorS ?>"><?= $pctS ?>%</span>
                                <?php if ($r['ada_penanganan']): ?>
                                    <span class="text-[10px] font-bold <?= $delta >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                                        <?= $delta >= 0 ? '↑' : '↓' ?><?= abs($delta) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center bg-purple-50/40">
                            <span class="font-semibold text-purple-700"><?= $fkm($r['sesudah']['rusak_berat_km']) ?></span>
                        </td>
                        <!-- Penanganan -->
                        <td class="px-4 py-3 text-center">
                            <?php if ($r['ada_penanganan']): ?>
                                <a href="<?= base_url('rekap/prediksi/' . $r['id'] . '?tahun=' . $tahunPenanganan) ?>"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-[11px] font-bold hover:bg-purple-200 transition-colors">
                                    <?= $r['total_penanganan'] ?> segmen
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 font-medium">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <!-- Totals Row -->
                <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                    <tr>
                        <td class="px-4 py-3 font-black text-gray-800 text-xs">TOTAL JARINGAN</td>
                        <td class="px-4 py-3 text-center font-black text-gray-800"><?= $fkm($totalPanjangKm) ?></td>
                        <td class="px-4 py-3 text-center bg-amber-50/60 border-l border-amber-100">
                            <span class="font-black text-gray-800"><?= $totalSebelum['pct_mantap'] ?>%</span>
                        </td>
                        <td class="px-4 py-3 text-center bg-amber-50/60">
                            <span class="font-black text-red-700"><?= $fkm($totalSebelum['rusak_berat_km']) ?> km</span>
                        </td>
                        <td class="px-4 py-3 text-center bg-purple-50/60 border-l border-purple-100">
                            <div class="flex items-center justify-center gap-1">
                                <span class="font-black text-purple-800"><?= $totalSesudah['pct_mantap'] ?>%</span>
                                <span class="text-xs font-bold <?= $deltaPositif ? 'text-green-600' : 'text-red-500' ?>">
                                    <?= $deltaPositif ? '↑' : '↓' ?><?= abs($mantapDelta) ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center bg-purple-50/60">
                            <span class="font-black text-purple-700"><?= $fkm($totalSesudah['rusak_berat_km']) ?> km</span>
                        </td>
                        <td class="px-4 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Panel Detail Angka (untuk verifikasi/debugging) -->
    <details class="group bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden">
        <summary class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-gray-100 transition-colors">
            <span class="text-xs font-bold text-gray-600 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Angka Detail Jaringan (klik untuk expand)
            </span>
            <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        <div class="px-5 pb-5 pt-3 border-t border-gray-200">
            <p class="text-[11px] text-gray-400 mb-3">Angka dalam meter (m), presisi 3 desimal. Berguna untuk verifikasi kalkulasi prediksi.</p>
            <?php
            $fmRaw = fn($v) => number_format((float)$v * 1000, 3, ',', '.');
            $totalMantapSebelum = ($totalSebelum['baik_km'] + $totalSebelum['sedang_km']) * 1000;
            $totalMantapSesudah = ($totalSesudah['baik_km'] + $totalSesudah['sedang_km']) * 1000;
            ?>
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left px-3 py-2 font-bold text-gray-600 rounded-tl-lg">Kondisi</th>
                        <th class="text-right px-3 py-2 font-bold text-amber-700 bg-amber-50">Saat Ini (m)</th>
                        <th class="text-right px-3 py-2 font-bold text-purple-700 bg-purple-50">Prediksi (m)</th>
                        <th class="text-right px-3 py-2 font-bold text-gray-600 rounded-tr-lg">Delta (m)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $kondisiRows = [
                        ['label' => 'Baik',         'k' => 'baik_km',         'color' => 'text-green-700'],
                        ['label' => 'Sedang',        'k' => 'sedang_km',       'color' => 'text-yellow-700'],
                        ['label' => 'Rusak Ringan',  'k' => 'rusak_ringan_km', 'color' => 'text-orange-700'],
                        ['label' => 'Rusak Berat',   'k' => 'rusak_berat_km',  'color' => 'text-red-700'],
                    ];
                    foreach ($kondisiRows as $row):
                        $sblm   = $totalSebelum[$row['k']] * 1000;
                        $ssdh   = $totalSesudah[$row['k']] * 1000;
                        $delta  = $ssdh - $sblm;
                        $dSign  = $delta >= 0 ? '+' : '';
                        $dColor = $delta > 0 ? 'text-green-600' : ($delta < 0 ? 'text-red-600' : 'text-gray-400');
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-semibold <?= $row['color'] ?>"><?= $row['label'] ?></td>
                        <td class="px-3 py-2 text-right font-mono text-gray-700 bg-amber-50/40"><?= number_format($sblm, 3, ',', '.') ?></td>
                        <td class="px-3 py-2 text-right font-mono text-purple-700 bg-purple-50/40"><?= number_format($ssdh, 3, ',', '.') ?></td>
                        <td class="px-3 py-2 text-right font-mono font-bold <?= $dColor ?>"><?= $dSign ?><?= number_format($delta, 3, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td class="px-3 py-2 font-black text-gray-700">TOTAL MANTAP</td>
                        <td class="px-3 py-2 text-right font-mono font-black text-amber-700 bg-amber-50"><?= number_format($totalMantapSebelum, 3, ',', '.') ?></td>
                        <td class="px-3 py-2 text-right font-mono font-black text-purple-700 bg-purple-50"><?= number_format($totalMantapSesudah, 3, ',', '.') ?></td>
                        <?php $deltaMantap = $totalMantapSesudah - $totalMantapSebelum; ?>
                        <td class="px-3 py-2 text-right font-mono font-black <?= $deltaMantap >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $deltaMantap >= 0 ? '+' : '' ?><?= number_format($deltaMantap, 3, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-black text-gray-700">TOTAL JARINGAN</td>
                        <td class="px-3 py-2 text-right font-mono font-black text-gray-600 bg-amber-50" colspan="3">
                            <?= number_format($totalPanjangKm * 1000, 3, ',', '.') ?> m
                        </td>
                    </tr>
                </tfoot>
            </table>
            <p class="text-[10px] text-gray-400 mt-2">
                💡 <?= number_format($deltaMantap ?? 0, 3, ',', '.') ?> m dari total <?= number_format($totalPanjangKm * 1000, 0, ',', '.') ?> m jaringan
                = <?= number_format(($deltaMantap ?? 0) / ($totalPanjangKm * 1000 ?: 1) * 100, 4, ',', '.') ?>%
                perubahan. Dibulatkan ke 1 desimal menghasilkan: <?= round(($deltaMantap ?? 0) / ($totalPanjangKm * 1000 ?: 1) * 100, 1) ?> poin.
            </p>
        </div>
    </details>

    <!-- Catatan / Keterangan Matriks -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <h4 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Keterangan Matriks Prediksi
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-blue-900">
            <?php foreach ($pelaksanaLabels as $key => $label): ?>
            <div class="bg-white/70 rounded-xl px-3 py-2 border border-blue-100">
                <span class="font-bold"><?= htmlspecialchars($label) ?>:</span>
                <?php
                switch ($key) {
                    case 'pihak_ke3_rigid': echo ' Semua kondisi → Baik - Rigid'; break;
                    case 'pihak_ke3_aspal': echo ' Semua kondisi → Baik - Aspal'; break;
                    case 'rutin_uptd': echo ' B/S → Baik. RR/RB → Tidak bisa (peringatan)'; break;
                    case 'urc_overlay_tanpa_finisher': echo ' B → Baik-Aspal. S/RR/RB → Sedang-Aspal'; break;
                    case 'urc_overlay_dengan_finisher': echo ' Semua kondisi → Baik - Aspal'; break;
                    case 'urc_rigid': echo ' Semua kondisi → Baik - Rigid'; break;
                    case 'urc_base': echo ' B/S/RR → Verifikasi Manual. RB → RB-Agregat/Tanah'; break;
                }
                ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
