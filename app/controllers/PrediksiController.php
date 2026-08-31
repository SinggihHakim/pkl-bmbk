<?php

/**
 * ============================================================
 * Controller: PrediksiController
 * ============================================================
 * Menyajikan halaman Prediksi Kondisi Jalan berdasarkan
 * matriks penanganan dari Ide Strip Map.xlsx.
 */

class PrediksiController
{
    private PrediksiService   $prediksiService;
    private PenangananService $penangananService;
    private StripmapService   $stripmapService;
    private RuasService       $ruasService;

    public function __construct()
    {
        $this->prediksiService   = new PrediksiService();
        $this->penangananService = new PenangananService();
        $this->stripmapService   = new StripmapService();
        $this->ruasService       = new RuasService();
    }

    /**
     * Halaman utama: Prediksi Kondisi Jalan (seluruh jaringan, per ruas)
     */
    public function index(): void
    {
        $ruasList = $this->ruasService->getAll();

        // Ambil filter tahun dari query string — default 2026, 'semua' juga valid
        $tahunRaw        = $_GET['tahun'] ?? '2026';
        $modeSemua       = ($tahunRaw === 'semua');
        $tahunPenanganan = $modeSemua ? 2026 : (int)$tahunRaw;

        // Fungsi helper: hitung summary jaringan untuk satu tahun
        $hitungUntukTahun = function (int $tahun) use ($ruasList): array {
            $totalSebelum = ['baik' => 0.0, 'sedang' => 0.0, 'rusak_ringan' => 0.0, 'rusak_berat' => 0.0];
            $totalSesudah = ['baik' => 0.0, 'sedang' => 0.0, 'rusak_ringan' => 0.0, 'rusak_berat' => 0.0];
            $perRuasOut   = [];

            foreach ($ruasList as $ruas) {
                $ruasId         = (int)$ruas['id'];
                $penangananList = $this->penangananService->getByRuasId($ruasId, $tahun);
                $stripmapList   = $this->stripmapService->getByRuasId($ruasId);

                $sebelumRuas = ['baik' => 0.0, 'sedang' => 0.0, 'rusak_ringan' => 0.0, 'rusak_berat' => 0.0];
                foreach ($stripmapList as $sm) {
                    $sebelumRuas['baik']        += (float)($sm['baik'] ?? 0);
                    $sebelumRuas['sedang']       += (float)($sm['sedang'] ?? 0);
                    $sebelumRuas['rusak_ringan'] += (float)($sm['rusak_ringan'] ?? 0);
                    $sebelumRuas['rusak_berat']  += (float)($sm['rusak_berat'] ?? 0);
                }

                $summary      = $this->prediksiService->hitungSummary($penangananList, $stripmapList);
                $totalPanjang = (float)$ruas['panjang'];

                $perRuasOut[] = [
                    'id'               => $ruasId,
                    'kode_ruas'        => $ruas['kode_ruas'],
                    'nama_ruas'        => $ruas['nama_ruas'],
                    'koridor'          => $ruas['koridor'] ?? '',
                    'panjang_km'       => round($totalPanjang / 1000, 2),
                    'sebelum'          => [
                        'baik_km'         => round($sebelumRuas['baik'] / 1000, 2),
                        'sedang_km'       => round($sebelumRuas['sedang'] / 1000, 2),
                        'rusak_ringan_km' => round($sebelumRuas['rusak_ringan'] / 1000, 2),
                        'rusak_berat_km'  => round($sebelumRuas['rusak_berat'] / 1000, 2),
                        'pct_mantap'      => $totalPanjang > 0
                            ? round((($sebelumRuas['baik'] + $sebelumRuas['sedang']) / $totalPanjang) * 100, 1) : 0,
                    ],
                    'sesudah'          => [
                        'baik_km'         => round($summary['sesudah']['baik'] / 1000, 2),
                        'sedang_km'       => round($summary['sesudah']['sedang'] / 1000, 2),
                        'rusak_ringan_km' => round($summary['sesudah']['rusak_ringan'] / 1000, 2),
                        'rusak_berat_km'  => round($summary['sesudah']['rusak_berat'] / 1000, 2),
                        'pct_mantap'      => $totalPanjang > 0
                            ? round((($summary['sesudah']['baik'] + $summary['sesudah']['sedang']) / $totalPanjang) * 100, 1) : 0,
                    ],
                    'ada_penanganan'   => !empty($penangananList),
                    'total_penanganan' => count($penangananList),
                ];

                foreach (['baik', 'sedang', 'rusak_ringan', 'rusak_berat'] as $k) {
                    $totalSebelum[$k] += $sebelumRuas[$k];
                    $totalSesudah[$k] += $summary['sesudah'][$k];
                }
            }

            $totalPanjangJaringan = array_sum(array_column($ruasList, 'panjang'));
            $sebelumMantap        = $totalSebelum['baik'] + $totalSebelum['sedang'];
            $sesudahMantap        = $totalSesudah['baik'] + $totalSesudah['sedang'];

            return [
                'perRuas'      => $perRuasOut,
                'totalSebelum' => [
                    'baik_km'         => round($totalSebelum['baik'] / 1000, 2),
                    'sedang_km'       => round($totalSebelum['sedang'] / 1000, 2),
                    'rusak_ringan_km' => round($totalSebelum['rusak_ringan'] / 1000, 2),
                    'rusak_berat_km'  => round($totalSebelum['rusak_berat'] / 1000, 2),
                    'pct_mantap'      => $totalPanjangJaringan > 0
                        ? round(($sebelumMantap / $totalPanjangJaringan) * 100, 1) : 0,
                ],
                'totalSesudah' => [
                    'baik_km'         => round($totalSesudah['baik'] / 1000, 2),
                    'sedang_km'       => round($totalSesudah['sedang'] / 1000, 2),
                    'rusak_ringan_km' => round($totalSesudah['rusak_ringan'] / 1000, 2),
                    'rusak_berat_km'  => round($totalSesudah['rusak_berat'] / 1000, 2),
                    'pct_mantap'      => $totalPanjangJaringan > 0
                        ? round(($sesudahMantap / $totalPanjangJaringan) * 100, 1) : 0,
                ],
                'totalPanjangKm' => round($totalPanjangJaringan / 1000, 2),
            ];
        };

        // Hitung data untuk tahun yang dipilih (atau default 2026 saat mode semua)
        $hasilTahunIni = $hitungUntukTahun($tahunPenanganan);

        // Jika mode semua: hitung juga semua tahun untuk chart multi-tahun
        $allYearsData = [];
        if ($modeSemua) {
            foreach (range(2025, 2030) as $thn) {
                $h = $hitungUntukTahun($thn);
                $allYearsData[$thn] = [
                    'pct_sebelum' => $h['totalSebelum']['pct_mantap'],
                    'pct_sesudah' => $h['totalSesudah']['pct_mantap'],
                    'sebelum'     => $h['totalSebelum'],
                    'sesudah'     => $h['totalSesudah'],
                ];
            }
        }

        $data = [
            'title'                  => 'Prediksi Kondisi Jalan Setelah Penanganan',
            'tahunPenanganan'        => $modeSemua ? 'semua' : $tahunPenanganan,
            'modeSemua'              => $modeSemua,
            'perRuas'                => $hasilTahunIni['perRuas'],
            'totalSebelum'           => $hasilTahunIni['totalSebelum'],
            'totalSesudah'           => $hasilTahunIni['totalSesudah'],
            'totalPanjangKm'         => $hasilTahunIni['totalPanjangKm'],
            'allYearsData'           => $allYearsData,
            'pelaksanaLabels'        => PrediksiService::PELAKSANA_LABELS,
            'kondisiLabels'          => PrediksiService::KONDISI_LABELS,
            'kondisiColors'          => PrediksiService::KONDISI_COLORS,
        ];

        view('layouts.app', array_merge($data, ['content' => 'rekap.prediksi']));
    }

    /**
     * Detail prediksi untuk satu ruas jalan (AJAX / halaman detail)
     */
    public function detail(int $ruasId): void
    {
        $ruas = $this->ruasService->findById($ruasId);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('rekap/prediksi'));
            return;
        }

        $tahunPenanganan = (int)($_GET['tahun'] ?? 2026);
        $penangananList  = $this->penangananService->getByRuasId($ruasId, $tahunPenanganan);
        $stripmapList    = $this->stripmapService->getByRuasId($ruasId);

        $summary = $this->prediksiService->hitungSummary($penangananList, $stripmapList);

        $data = [
            'title'             => 'Detail Prediksi — ' . $ruas['nama_ruas'],
            'ruas'              => $ruas,
            'tahunPenanganan'   => $tahunPenanganan,
            'penangananList'    => $penangananList,
            'summary'           => $summary,
            'pelaksanaLabels'   => PrediksiService::PELAKSANA_LABELS,
            'kondisiLabels'     => PrediksiService::KONDISI_LABELS,
            'kondisiColors'     => PrediksiService::KONDISI_COLORS,
        ];

        view('layouts.app', array_merge($data, ['content' => 'rekap.prediksi_detail']));
    }
}
