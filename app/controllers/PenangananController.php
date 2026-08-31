<?php

/**
 * ============================================================
 * Controller: PenangananController
 * ============================================================
 * Mengelola request CRUD data segmentasi penanganan jalan.
 */

class PenangananController
{
    private PenangananService $service;
    private RuasService       $ruasService;
    private PrediksiService   $prediksiService;

    public function __construct()
    {
        $this->service         = new PenangananService();
        $this->ruasService     = new RuasService();
        $this->prediksiService = new PrediksiService();
    }

    /**
     * Simpan data segmen penanganan baru
     */
    public function store(int $ruasId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('stripmap/' . $ruasId));
            return;
        }

        $ruas = $this->ruasService->findById($ruasId);
        if (!$ruas) {
            flash('error', 'Ruas jalan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $jenisPelaksana = trim($_POST['jenis_pelaksana'] ?? '');

        // Hitung prediksi otomatis jika jenis_pelaksana diisi
        $kondisiPrediksi  = null;
        $perkerasanHasil  = null;
        $perluVerifikasi  = 0;
        if (!empty($jenisPelaksana)) {
            $kondisiAwal   = trim($_POST['kondisi_awal_dominan'] ?? 'baik');
            $perkerasanAwal = trim($_POST['perkerasan_awal_dominan'] ?? 'aspal');
            $prediksi = $this->prediksiService->hitung($kondisiAwal, $perkerasanAwal, $jenisPelaksana);
            $kondisiPrediksi = $prediksi['kondisi_prediksi'];
            $perkerasanHasil = $prediksi['perkerasan_hasil'];
            $perluVerifikasi = $prediksi['perlu_verifikasi'] ? 1 : 0;
        }

        $data = [
            'ruas_id'          => $ruasId,
            'tahun'            => (int) ($_POST['tahun'] ?? date('Y')),
            'sta_awal'         => $_POST['sta_awal'] ?? 0,
            'sta_akhir'        => $_POST['sta_akhir'] ?? 0,
            'jenis_penanganan' => trim($_POST['jenis_penanganan'] ?? ''),
            'jenis_pelaksana'  => $jenisPelaksana ?: null,
            'perkerasan_hasil' => $perkerasanHasil,
            'kondisi_prediksi' => $kondisiPrediksi,
            'perlu_verifikasi' => $perluVerifikasi,
            'status'           => trim($_POST['status'] ?? 'rencana'),
            'nama_paket'       => trim($_POST['nama_paket'] ?? ''),
            'anggaran'         => $_POST['anggaran'] ?? 0,
            'sumber_dana'      => trim($_POST['sumber_dana'] ?? ''),
            'warna'            => trim($_POST['warna'] ?? ''),
            'keterangan'       => trim($_POST['keterangan'] ?? ''),
        ];

        $result = $this->service->create($data);
        if (!$result['success']) {
            $err = implode(' ', $result['errors']);
            flash('error', 'Gagal menyimpan data penanganan: ' . $err);
        } else {
            flash('success', 'Data penanganan jalan berhasil ditambahkan.');
        }

        redirect(base_url('stripmap/' . $ruasId . '#penanganan-section'));
    }

    /**
     * Update data segmen penanganan
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('ruas'));
            return;
        }

        $penanganan = $this->service->findById($id);
        if (!$penanganan) {
            flash('error', 'Data penanganan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $ruasId = (int) $penanganan['ruas_id'];

        $jenisPelaksana = trim($_POST['jenis_pelaksana'] ?? '');

        // Hitung prediksi otomatis jika jenis_pelaksana diisi
        $kondisiPrediksi  = $penanganan['kondisi_prediksi'] ?? null;
        $perkerasanHasil  = $penanganan['perkerasan_hasil'] ?? null;
        $perluVerifikasi  = (int)($penanganan['perlu_verifikasi'] ?? 0);
        if (!empty($jenisPelaksana)) {
            $kondisiAwal    = trim($_POST['kondisi_awal_dominan'] ?? 'baik');
            $perkerasanAwal = trim($_POST['perkerasan_awal_dominan'] ?? 'aspal');
            $prediksi = $this->prediksiService->hitung($kondisiAwal, $perkerasanAwal, $jenisPelaksana);
            $kondisiPrediksi = $prediksi['kondisi_prediksi'];
            $perkerasanHasil = $prediksi['perkerasan_hasil'];
            $perluVerifikasi = $prediksi['perlu_verifikasi'] ? 1 : 0;
        }

        $data = [
            'ruas_id'          => $ruasId,
            'tahun'            => (int) ($_POST['tahun'] ?? $penanganan['tahun']),
            'sta_awal'         => $_POST['sta_awal'] ?? $penanganan['sta_awal'],
            'sta_akhir'        => $_POST['sta_akhir'] ?? $penanganan['sta_akhir'],
            'jenis_penanganan' => trim($_POST['jenis_penanganan'] ?? ''),
            'jenis_pelaksana'  => $jenisPelaksana ?: null,
            'perkerasan_hasil' => $perkerasanHasil,
            'kondisi_prediksi' => $kondisiPrediksi,
            'perlu_verifikasi' => $perluVerifikasi,
            'status'           => trim($_POST['status'] ?? 'rencana'),
            'nama_paket'       => trim($_POST['nama_paket'] ?? ''),
            'anggaran'         => $_POST['anggaran'] ?? 0,
            'sumber_dana'      => trim($_POST['sumber_dana'] ?? ''),
            'warna'            => trim($_POST['warna'] ?? ''),
            'keterangan'       => trim($_POST['keterangan'] ?? ''),
        ];

        $result = $this->service->update($id, $data);
        if (!$result['success']) {
            $err = implode(' ', $result['errors']);
            flash('error', 'Gagal memperbarui data penanganan: ' . $err);
        } else {
            flash('success', 'Data penanganan jalan berhasil diperbarui.');
        }

        redirect(base_url('stripmap/' . $ruasId . '#penanganan-section'));
    }

    /**
     * Hapus data segmen penanganan
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('ruas'));
            return;
        }

        $penanganan = $this->service->findById($id);
        if (!$penanganan) {
            flash('error', 'Data penanganan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $ruasId = (int) $penanganan['ruas_id'];
        $this->service->delete($id);
        flash('success', 'Data penanganan berhasil dihapus.');

        redirect(base_url('stripmap/' . $ruasId . '#penanganan-section'));
    }

    /**
     * Terapkan hasil penanganan berstatus 'selesai' ke stripmap kondisi jalan menjadi 'Baik'
     */
    public function applyKondisi(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('ruas'));
            return;
        }

        $penanganan = $this->service->findById($id);
        if (!$penanganan) {
            flash('error', 'Data penanganan tidak ditemukan.');
            redirect(base_url('ruas'));
            return;
        }

        $ruasId = (int) $penanganan['ruas_id'];
        $result = $this->service->applyKondisiToStripmap($id);

        if ($result['success']) {
            flash('success', $result['message']);
        } else {
            flash('error', $result['message']);
        }

        redirect(base_url('stripmap/' . $ruasId));
    }
}
