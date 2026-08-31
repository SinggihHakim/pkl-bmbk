<?php
/**
 * View: Detail Prediksi Kondisi — Per Ruas Jalan
 */

$fkm = fn($v) => number_format((float)$v, 2, ',', '.');
$fm  = fn($v) => number_format((float)$v, 0, ',', '.');

$sebelum = $summary['sebelum'];
$sesudah = $summary['sesudah'];
$detail  = $summary['detail'];

$totalPanjangRuas = (float)$ruas['panjang'];
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= base_url() ?>" class="text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <a href="<?= base_url('rekap/prediksi') ?>" class="text-xs font-semibold text-gray-500 hover:text-blue-600 transition-colors">Prediksi Kondisi</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-blue-600"><?= htmlspecialchars($ruas['kode_ruas']) ?></span>
            </div>
            <h1 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($ruas['nama_ruas']) ?></h1>
            <p class="text-xs text-gray-500 mt-1">
                Kode: <strong><?= htmlspecialchars($ruas['kode_ruas']) ?></strong> &nbsp;|&nbsp;
                Panjang: <strong><?= $fkm($totalPanjangRuas / 1000) ?> km</strong> &nbsp;|&nbsp;
                Tahun Penanganan: <strong class="text-purple-600"><?= $tahunPenanganan ?></strong>
            </p>
        </div>
        <a href="<?= base_url('rekap/prediksi') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold border border-gray-300/80 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <!-- Komparasi Kondisi Sebelum vs Sesudah -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Sebelum -->
        <div class="bg-white p-6 rounded-2xl border border-amber-200 shadow-sm">
            <h3 class="text-sm font-bold text-amber-700 mb-4 flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                Kondisi Saat Ini (Database n-1)
            </h3>
            <div class="space-y-3">
                <?php
                $kondisiList = [
                    ['label' => 'Baik',         'key' => 'baik',         'color' => 'bg-green-500',  'val' => $sebelum['baik']],
                    ['label' => 'Sedang',        'key' => 'sedang',       'color' => 'bg-yellow-400', 'val' => $sebelum['sedang']],
                    ['label' => 'Rusak Ringan',  'key' => 'rusak_ringan', 'color' => 'bg-orange-500', 'val' => $sebelum['rusak_ringan']],
                    ['label' => 'Rusak Berat',   'key' => 'rusak_berat',  'color' => 'bg-red-500',    'val' => $sebelum['rusak_berat']],
                ];
                $totalSebelum = array_sum(array_column($kondisiList, 'val')) ?: 1;
                foreach ($kondisiList as $k): ?>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-semibold text-gray-700"><?= $k['label'] ?></span>
                        <span class="font-bold text-gray-800"><?= $fkm($k['val'] / 1000) ?> km</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="<?= $k['color'] ?> h-2.5 rounded-full" style="width: <?= round(($k['val'] / $totalSebelum) * 100, 1) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sesudah (Prediksi) -->
        <div class="bg-white p-6 rounded-2xl border border-purple-200 shadow-sm">
            <h3 class="text-sm font-bold text-purple-700 mb-4 flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-purple-500"></div>
                Prediksi Kondisi Sesudah Penanganan (n)
            </h3>
            <div class="space-y-3">
                <?php
                $kondisiSesudahList = [
                    ['label' => 'Baik',         'key' => 'baik',         'color' => 'bg-green-500',  'val' => $sesudah['baik']],
                    ['label' => 'Sedang',        'key' => 'sedang',       'color' => 'bg-yellow-400', 'val' => $sesudah['sedang']],
                    ['label' => 'Rusak Ringan',  'key' => 'rusak_ringan', 'color' => 'bg-orange-500', 'val' => $sesudah['rusak_ringan']],
                    ['label' => 'Rusak Berat',   'key' => 'rusak_berat',  'color' => 'bg-red-500',    'val' => $sesudah['rusak_berat']],
                ];
                $totalSesudah = array_sum(array_column($kondisiSesudahList, 'val')) ?: 1;
                foreach ($kondisiSesudahList as $i => $k):
                    $sebelumVal = $kondisiList[$i]['val'];
                    $diff = $k['val'] - $sebelumVal;
                    ?>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-semibold text-gray-700"><?= $k['label'] ?></span>
                        <div class="flex items-center gap-2">
                            <?php if (abs($diff) > 0.5): ?>
                            <span class="text-[10px] font-bold <?= $diff > 0 ? 'text-green-600' : 'text-red-500' ?>">
                                <?= $diff > 0 ? '+' : '' ?><?= $fkm($diff / 1000) ?> km
                            </span>
                            <?php endif; ?>
                            <span class="font-bold text-purple-800"><?= $fkm($k['val'] / 1000) ?> km</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="<?= $k['color'] ?> h-2.5 rounded-full opacity-60" style="width: <?= round(($k['val'] / $totalSesudah) * 100, 1) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Per Segmen Penanganan -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Detail Segmen Penanganan & Prediksi</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                <?= count($detail) ?> segmen penanganan ditemukan untuk tahun <?= $tahunPenanganan ?>.
                Klik <a href="<?= base_url('stripmap/' . $ruas['id']) ?>" class="text-blue-600 hover:underline font-semibold">halaman strip map</a> untuk menambah / mengubah data penanganan.
            </p>
        </div>

        <?php if (empty($detail)): ?>
        <div class="flex flex-col items-center justify-center h-40 text-center p-6">
            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-sm font-semibold text-gray-500">Belum ada segmen penanganan dengan Jenis Pelaksana yang dipilih.</p>
            <p class="text-xs text-gray-400 mt-1">Buka halaman strip map dan isi field <strong>Jenis Pelaksana</strong> pada data penanganan.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">STA</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Panjang</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Jenis Pelaksana</th>
                        <th class="px-4 py-3 text-center font-bold text-amber-600 uppercase tracking-wider bg-amber-50">Kondisi Sebelum</th>
                        <th class="px-4 py-3 text-center font-bold text-purple-600 uppercase tracking-wider bg-purple-50">Prediksi Sesudah</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($detail as $d): ?>
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3 font-mono">
                            <span class="font-semibold text-gray-800"><?= meter_to_sta((float)$d['sta_awal']) ?></span>
                            <span class="text-gray-400 mx-1">—</span>
                            <span class="font-semibold text-gray-800"><?= meter_to_sta((float)$d['sta_akhir']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700"><?= $fm($d['panjang']) ?> m</td>
                        <td class="px-4 py-3">
                            <?php if (empty($d['jenis_pelaksana'])): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                    ⚙ Belum dikonfigurasi
                                </span>
                            <?php else: ?>
                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($d['label_pelaksana']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center bg-amber-50/40">
                            <?php
                            $kondisiSebelumColors = [
                                'baik' => 'bg-green-100 text-green-800',
                                'sedang' => 'bg-yellow-100 text-yellow-800',
                                'rusak_ringan' => 'bg-orange-100 text-orange-800',
                                'rusak_berat' => 'bg-red-100 text-red-800',
                            ];
                            $ksBefore = $d['kondisi_sebelum'];
                            $ksBadge  = $kondisiSebelumColors[$ksBefore] ?? 'bg-gray-100 text-gray-700';
                            $ksLabel  = PrediksiService::getKondisiLabel($ksBefore);
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $ksBadge ?>">
                                <?= $ksLabel ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center bg-purple-50/40">
                            <?php if (empty($d['jenis_pelaksana'])): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                    — Pilih pelaksana
                                </span>
                            <?php elseif (!$d['bisa_dilaksanakan']): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                    ⚠ Tidak Bisa
                                </span>
                            <?php elseif ($d['perlu_verifikasi']): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    ⚠ Verifikasi Manual
                                </span>
                            <?php else: ?>
                                <?php
                                $ksAfter  = $d['kondisi_sesudah'];
                                $ksBadgeA = $kondisiSebelumColors[$ksAfter] ?? 'bg-gray-100 text-gray-700';
                                $ksLabelA = PrediksiService::getKondisiLabel($ksAfter);
                                $pkHasil  = PrediksiService::getPerkerasanLabel($d['perkerasan_hasil'] ?? '');
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $ksBadgeA ?>">
                                    <?= $ksLabelA ?>
                                </span>
                                <p class="text-[10px] text-gray-500 mt-0.5"><?= htmlspecialchars($pkHasil) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php
                            $statusBadges = [
                                'rencana' => 'bg-sky-100 text-sky-700',
                                'proses'  => 'bg-indigo-100 text-indigo-700',
                                'selesai' => 'bg-emerald-100 text-emerald-700',
                            ];
                            $statusLabels = ['rencana' => 'Rencana', 'proses' => 'Proses', 'selesai' => 'Selesai'];
                            $st = $d['status'];
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $statusBadges[$st] ?? 'bg-gray-100 text-gray-600' ?>">
                                <?= $statusLabels[$st] ?? ucfirst($st) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-40">
                            <?php if (!$d['bisa_dilaksanakan'] || $d['perlu_verifikasi']): ?>
                                <p class="text-orange-600 font-medium leading-tight line-clamp-2"><?= htmlspecialchars($d['pesan']) ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>
