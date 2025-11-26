<?php
// app/Models/Book.php

class BookModel {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    /**
     * Ambil semua data buku tanpa filter
     */
    public function all(){
        $stmt = $this->db->query("SELECT * FROM buku ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Ambil data buku + filter tanggal (untuk laporan lama)
     */
    public function getAll($from = null, $to = null){
        $sql = "SELECT * FROM buku WHERE 1";
        $params = [];

        if ($from) {
            $sql .= " AND created_at >= ?";
            $params[] = $from;
        }

        if ($to) {
            $sql .= " AND created_at <= ?";
            $params[] = $to;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * 🔥 Laporan buku
     */
    public function reportAll($from = null, $to = null){
        $sql = "SELECT * FROM buku WHERE 1";
        $params = [];

        if ($from) {
            $sql .= " AND created_at >= ?";
            $params[] = $from;
        }

        if ($to) {
            $sql .= " AND created_at <= ?";
            $params[] = $to;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find($id){
        $stmt = $this->db->prepare("SELECT * FROM buku WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * ➕ CREATE BUKU + COVER
     */
    public function create($d){
        $stmt = $this->db->prepare("
            INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok, cover)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $d['judul'],
            $d['penulis'],
            $d['penerbit'],
            $d['tahun_terbit'],
            $d['stok'],
            $d['cover'] ?? null  // cover bisa kosong
        ]);
    }

    /**
     * ✏ UPDATE BUKU + COVER
     */
    public function update($id, $d){
        $stmt = $this->db->prepare("
            UPDATE buku 
            SET judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, stok = ?, cover = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $d['judul'],
            $d['penulis'],
            $d['penerbit'],
            $d['tahun_terbit'],
            $d['stok'],
            $d['cover'] ?? null,
            $id
        ]);
    }

    public function delete($id){
        $stmt = $this->db->prepare("DELETE FROM buku WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Hitung jumlah buku
     */
    public function countBooks($from = null, $to = null){
        $sql = "SELECT COUNT(*) FROM buku WHERE 1";
        $params = [];

        if ($from) {
            $sql .= " AND created_at >= ?";
            $params[] = $from;
        }

        if ($to) {
            $sql .= " AND created_at <= ?";
            $params[] = $to;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /* ===============================
        🎉 FUNGSI TAMBAHAN COVER
       (TIDAK MENGUBAH APAPUN YANG LAMA)
    =============================== */

    /**
     * Update hanya cover saja
     */
    public function updateCover($id, $cover){
        $stmt = $this->db->prepare("
            UPDATE buku SET cover = ? WHERE id = ?
        ");
        return $stmt->execute([$cover, $id]);
    }

    /**
     * Hapus cover (set NULL)
     */
    public function deleteCover($id){
        $stmt = $this->db->prepare("
            UPDATE buku SET cover = NULL WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
}
