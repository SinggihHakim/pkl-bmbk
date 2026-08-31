<?php

/**
 * ============================================================
 * Model: FotoLapangan
 * ============================================================
 * Mengelola data foto kondisi real di lapangan per STA.
 */

class FotoLapangan
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTableExists();
    }

    /**
     * Memastikan tabel foto_lapangan sudah ada di database
     */
    private function ensureTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `foto_lapangan` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ruas_id` INT UNSIGNED NOT NULL,
            `sta_titik` VARCHAR(20) NOT NULL,
            `sta_meter` INT NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_size` INT DEFAULT 0,
            `keterangan` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_ruas_id` (`ruas_id`),
            KEY `idx_ruas_sta` (`ruas_id`, `sta_meter`),
            CONSTRAINT `fk_foto_lapangan_ruas`
                FOREIGN KEY (`ruas_id`)
                REFERENCES `ruas_jalan` (`id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            // Abaikan jika constraint sudah ada atau SQLite/MySQL mode ringan
        }
    }

    /**
     * Ambil semua foto lapangan berdasarkan ruas_id terurut STA meter ASC
     */
    public function getByRuasId(int $ruasId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM foto_lapangan WHERE ruas_id = :ruas_id ORDER BY sta_meter ASC, id ASC'
        );
        $stmt->execute(['ruas_id' => $ruasId]);
        return $stmt->fetchAll();
    }

    /**
     * Cari foto berdasarkan ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM foto_lapangan WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cari foto berdasarkan ruas_id dan sta_meter
     */
    public function findByRuasAndSta(int $ruasId, int $staMeter): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM foto_lapangan WHERE ruas_id = :ruas_id AND sta_meter = :sta_meter LIMIT 1');
        $stmt->execute(['ruas_id' => $ruasId, 'sta_meter' => $staMeter]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Simpan data foto baru (atau perbarui jika STA meter & ruas_id sama)
     */
    public function save(array $data): int
    {
        $existing = $this->findByRuasAndSta($data['ruas_id'], $data['sta_meter']);
        if ($existing) {
            // Hapus file fisik lama jika berganti nama file
            if (!empty($existing['file_path']) && $existing['file_path'] !== $data['file_path']) {
                $oldFile = BASE_PATH . '/public/' . ltrim($existing['file_path'], '/');
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $stmt = $this->db->prepare(
                'UPDATE foto_lapangan 
                 SET sta_titik = :sta_titik, file_name = :file_name, file_path = :file_path, file_size = :file_size, keterangan = :keterangan
                 WHERE id = :id'
            );
            $stmt->execute([
                'sta_titik'  => $data['sta_titik'],
                'file_name'  => $data['file_name'],
                'file_path'  => $data['file_path'],
                'file_size'  => $data['file_size'] ?? 0,
                'keterangan' => $data['keterangan'] ?? null,
                'id'         => $existing['id'],
            ]);
            return (int) $existing['id'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO foto_lapangan (ruas_id, sta_titik, sta_meter, file_name, file_path, file_size, keterangan)
             VALUES (:ruas_id, :sta_titik, :sta_meter, :file_name, :file_path, :file_size, :keterangan)'
        );
        $stmt->execute([
            'ruas_id'    => $data['ruas_id'],
            'sta_titik'  => $data['sta_titik'],
            'sta_meter'  => $data['sta_meter'],
            'file_name'  => $data['file_name'],
            'file_path'  => $data['file_path'],
            'file_size'  => $data['file_size'] ?? 0,
            'keterangan' => $data['keterangan'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Hapus foto berdasarkan ID
     */
    public function delete(int $id): bool
    {
        $foto = $this->findById($id);
        if ($foto && !empty($foto['file_path'])) {
            $filePath = BASE_PATH . '/public/' . ltrim($foto['file_path'], '/');
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        $stmt = $this->db->prepare('DELETE FROM foto_lapangan WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Hapus semua foto berdasarkan ruas_id
     */
    public function deleteByRuasId(int $ruasId): void
    {
        $fotos = $this->getByRuasId($ruasId);
        foreach ($fotos as $f) {
            $this->delete((int)$f['id']);
        }
    }
}
