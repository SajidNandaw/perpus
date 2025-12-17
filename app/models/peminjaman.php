<?php

// app/Models/Peminjaman.php
// Model yang mengelola proses pinjam & pengembalian buku

class PeminjamanModel {
    private $db; // koneksi database

    public function __construct($db){
        $this->db = $db;
    }

    /*
      Ambil semua data peminjaman untuk laporan (dengan filter date range)
     */
    public function reportAll($from = null, $to = null){
        $sql = "
            SELECT p.*, u.username, b.judul
            FROM peminjaman p
            JOIN users u ON p.user_id = u.id
            JOIN buku b ON p.buku_id = b.id
            WHERE 1
        ";

        $params = [];

        // Filter dari tanggal tertentu
        if ($from) {
            $sql .= " AND p.created_at >= ?";
            $params[] = $from;
        }

        // Filter sampai tanggal tertentu
        if ($to) {
            $sql .= " AND p.created_at <= ?";
            $params[] = $to;
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /*
      Ambil semua peminjaman + username + judul buku
    */
    public function all(){
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, b.judul, b.cover
            FROM peminjaman p
            JOIN users u ON p.user_id = u.id
            JOIN buku b ON p.buku_id = b.id
            ORDER BY p.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*
      Ambil detail peminjaman berdasarkan ID
    */
    public function find($id){
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, b.judul, b.cover
            FROM peminjaman p
            JOIN users u ON p.user_id = u.id
            JOIN buku b ON p.buku_id = b.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ===========================================
    //  Tambah peminjaman (dengan tanggal tenggat)
    // ===========================================
    public function borrow($user_id, $buku_id, $tanggal_pinjam, $tanggal_tenggat = null){

        // Cek apakah user sudah meminjam buku ini
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM peminjaman
            WHERE user_id = ? AND buku_id = ? AND status = 'dipinjam'
        ");
        $stmt->execute([$user_id, $buku_id]);
        $sudah = $stmt->fetchColumn();

        if ($sudah > 0) {
            return "SUDAH_DIPINJAM";
        }

        // Cek stok buku
        $stmt = $this->db->prepare("SELECT stok FROM buku WHERE id = ?");
        $stmt->execute([$buku_id]);
        $stok = $stmt->fetchColumn();

        if ($stok <= 0) {
            return "STOK_HABIS";
        }

        // Jika tanggal_tenggat tidak diisi, buat default +7 hari dari tanggal_pinjam
        if (empty($tanggal_tenggat)) {
            $tanggal_tenggat = date('Y-m-d', strtotime($tanggal_pinjam . ' +7 days'));
        }

        // Insert peminjaman baru
        $stmt = $this->db->prepare("
            INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, tanggal_tenggat, status)
            VALUES (?, ?, ?, ?, 'dipinjam')
        ");
        $stmt->execute([$user_id, $buku_id, $tanggal_pinjam, $tanggal_tenggat]);

        // Kurangi stok buku
        $stmt = $this->db->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?");
        $stmt->execute([$buku_id]);

        return "OK";
    }

    /*
      Kembalikan buku
      Cek dulu apakah sudah dikembalikan sebelumnya, agar stok tidak double(kemarin ada bug soalnya)
     */
    public function returnBook($id, $tanggal_kembali){

        // Ambil data peminjaman berdasarkan ID
        $stmt = $this->db->prepare("
            SELECT buku_id, status
            FROM peminjaman
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return false; // tidak ditemukan
        }

        $buku_id     = $row['buku_id'];
        $status_lama = $row['status'];

        // Jika sudah dikembalikan sebelumnya, jangan tambah stok lagi
        if ($status_lama === 'dikembalikan') {
            return false;
        }

        // Update status peminjaman menjadi dikembalikan
        $stmt = $this->db->prepare("
            UPDATE peminjaman
            SET status = 'dikembalikan', tanggal_kembali = ?
            WHERE id = ?
        ");
        $stmt->execute([$tanggal_kembali, $id]);

        // Tambahkan stok buku kembali
        if ($buku_id) {
            $stmt = $this->db->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?");
            $stmt->execute([$buku_id]);
        }

        return true;
    }
}
