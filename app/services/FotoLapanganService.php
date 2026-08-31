<?php

/**
 * ============================================================
 * Service: FotoLapanganService
 * ============================================================
 * Logika bisnis untuk upload, parsing STA, dan manajemen foto lapangan.
 */

class FotoLapanganService
{
    private FotoLapangan $model;

    public function __construct()
    {
        $this->model = new FotoLapangan();
    }

    /**
     * Parse STA dari nama file (contoh: '0+100.jpg', '0+200.png', 'sta_1+250.jpg', '0+100-rusak.jpg')
     */
    public function parseStaFromFilename(string $filename): ?array
    {
        $nameNoExt = pathinfo($filename, PATHINFO_FILENAME);

        // Pattern 1: Format KM+m (Contoh: 0+100, 1+250, sta_0+100, 0+100_kanan)
        if (preg_match('/(\d+)\+(\d+)/', $nameNoExt, $matches)) {
            $km = (int) $matches[1];
            $m  = (int) $matches[2];
            $staMeter = ($km * 1000) + $m;
            $staTitik = $km . '+' . sprintf('%03d', $m);
            return [
                'sta_titik' => $staTitik,
                'sta_meter' => $staMeter,
            ];
        }

        // Pattern 2: Angka m murni (Contoh: 100.jpg, STA100.png)
        if (preg_match('/(?:sta[_\s-]*)?(\d+)/i', $nameNoExt, $matches)) {
            $staMeter = (int) $matches[1];
            $km = (int) floor($staMeter / 1000);
            $m  = $staMeter % 1000;
            $staTitik = $km . '+' . sprintf('%03d', $m);
            return [
                'sta_titik' => $staTitik,
                'sta_meter' => $staMeter,
            ];
        }

        return null;
    }

    /**
     * Ambil daftar foto lapangan untuk ruas tertentu dengan URL siap pakai
     */
    public function getByRuasId(int $ruasId): array
    {
        $fotos = $this->model->getByRuasId($ruasId);
        return array_map(function ($f) {
            $f['url'] = base_url(ltrim($f['file_path'], '/'));
            return $f;
        }, $fotos);
    }

    /**
     * Handle upload multiple foto dari $_FILES
     */
    public function handleBatchUpload(int $ruasId, array $files): array
    {
        if (empty($files['name']) || !is_array($files['name'])) {
            return ['success' => false, 'message' => 'Tidak ada file yang dipilih.'];
        }

        $uploadDir = BASE_PATH . '/public/uploads/foto_lapangan/' . $ruasId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $successCount = 0;
        $failedFiles  = [];

        $totalFiles = count($files['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $origName = $files['name'][$i];
            $tmpName  = $files['tmp_name'][$i];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $failedFiles[] = $origName . ' (format tidak didukung)';
                continue;
            }

            $parsed = $this->parseStaFromFilename($origName);
            if (!$parsed) {
                $failedFiles[] = $origName . ' (STA tidak terdeteksi)';
                continue;
            }

            // Simpan file dengan nama konsisten STA (misal 0+100.jpg)
            $cleanName = str_replace('+', '_', $parsed['sta_titik']) . '.' . $ext;
            $targetPath = $uploadDir . '/' . $cleanName;
            $relativePath = 'uploads/foto_lapangan/' . $ruasId . '/' . $cleanName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $this->model->save([
                    'ruas_id'   => $ruasId,
                    'sta_titik' => $parsed['sta_titik'],
                    'sta_meter' => $parsed['sta_meter'],
                    'file_name' => $origName,
                    'file_path' => $relativePath,
                    'file_size' => filesize($targetPath),
                ]);
                $successCount++;
            } else {
                $failedFiles[] = $origName . ' (gagal disimpan)';
            }
        }

        $msg = "Berhasil mengunggah $successCount foto lapangan.";
        if (!empty($failedFiles)) {
            $msg .= " File yang dilewati: " . implode(', ', $failedFiles);
        }

        return [
            'success' => $successCount > 0,
            'message' => $msg,
            'count'   => $successCount,
        ];
    }

    /**
     * Handle upload archive ZIP berisi foto-foto STA
     */
    public function handleZipUpload(int $ruasId, array $zipFile): array
    {
        if (empty($zipFile['tmp_name']) || $zipFile['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Gagal mengunggah file ZIP.'];
        }

        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'message' => 'Ekstensi PHP ZipArchive tidak aktif pada server.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile['tmp_name']) !== true) {
            return ['success' => false, 'message' => 'File ZIP tidak valid atau rusak.'];
        }

        $uploadDir = BASE_PATH . '/public/uploads/foto_lapangan/' . $ruasId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tempExtractDir = BASE_PATH . '/public/uploads/foto_lapangan/tmp_' . time() . '_' . uniqid();
        mkdir($tempExtractDir, 0755, true);

        $zip->extractTo($tempExtractDir);
        $zip->close();

        $successCount = 0;
        $failedFiles  = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempExtractDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;

            $filename = $file->getFilename();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            $parsed = $this->parseStaFromFilename($filename);
            if (!$parsed) {
                $failedFiles[] = $filename;
                continue;
            }

            $cleanName = str_replace('+', '_', $parsed['sta_titik']) . '.' . $ext;
            $targetPath = $uploadDir . '/' . $cleanName;
            $relativePath = 'uploads/foto_lapangan/' . $ruasId . '/' . $cleanName;

            if (copy($file->getPathname(), $targetPath)) {
                $this->model->save([
                    'ruas_id'   => $ruasId,
                    'sta_titik' => $parsed['sta_titik'],
                    'sta_meter' => $parsed['sta_meter'],
                    'file_name' => $filename,
                    'file_path' => $relativePath,
                    'file_size' => filesize($targetPath),
                ]);
                $successCount++;
            }
        }

        // Hapus folder temp
        $this->deleteRecursive($tempExtractDir);

        $msg = "Berhasil mengekstrak dan menyimpan $successCount foto dari ZIP.";
        if (!empty($failedFiles)) {
            $msg .= " (Beberapa file tanpa format STA dilewati)";
        }

        return [
            'success' => $successCount > 0,
            'message' => $msg,
            'count'   => $successCount,
        ];
    }

    /**
     * Hapus foto berdasarkan ID
     */
    public function deleteFoto(int $id): array
    {
        $deleted = $this->model->delete($id);
        if ($deleted) {
            return ['success' => true, 'message' => 'Foto lapangan berhasil dihapus.'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus foto lapangan.'];
    }

    /**
     * Helper hapus direktori recursive
     */
    private function deleteRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteRecursive("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
