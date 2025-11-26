<?php
class ReviewModel {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // Tambah review baru
    public function addReview($peminjaman_id, $user_id, $buku_id, $rating, $komentar){
        $stmt = $this->db->prepare("
            INSERT INTO reviews (peminjaman_id, user_id, buku_id, rating, komentar)
            VALUES (?,?,?,?,?)
        ");
        return $stmt->execute([$peminjaman_id, $user_id, $buku_id, $rating, $komentar]);
    }

    // Update review
    public function updateReview($review_id, $rating, $komentar, $user_id){
        $stmt = $this->db->prepare("
            UPDATE reviews 
            SET rating=?, komentar=?
            WHERE id=? AND user_id=?
        ");
        return $stmt->execute([$rating, $komentar, $review_id, $user_id]);
    }

    // Hapus review
    public function deleteReview($review_id, $user_id){
        $stmt = $this->db->prepare("
            DELETE FROM reviews
            WHERE id=? AND user_id=?
        ");
        return $stmt->execute([$review_id, $user_id]);
    }

    // Ambil berdasarkan peminjaman
    public function findByPeminjaman($peminjaman_id){
        $stmt = $this->db->prepare("SELECT * FROM reviews WHERE peminjaman_id = ?");
        $stmt->execute([$peminjaman_id]);
        return $stmt->fetch();
    }

    // Ambil berdasarkan review_id
    public function findByReviewId($review_id){
        $stmt = $this->db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        return $stmt->fetch();
    }

    /* ============================================================
       FUNGSI TAMBAHAN UNTUK INDEX.PHP
    =============================================================== */

    // Ambil rata-rata rating & jumlah review
    public function getBookRating($buku_id){
        $stmt = $this->db->prepare("
            SELECT 
                AVG(rating) AS avg_rating,
                COUNT(*) AS count_review
            FROM reviews
            WHERE buku_id = ?
        ");
        $stmt->execute([$buku_id]);
        return $stmt->fetch();
    }

    // Ambil komentar terbaru
    public function getLatestReview($buku_id){
        $stmt = $this->db->prepare("
            SELECT komentar 
            FROM reviews
            WHERE buku_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$buku_id]);
        return $stmt->fetch();
    }

    // Ambil semua ulasan untuk 1 buku
    public function getAllReviewsByBook($buku_id){
        $stmt = $this->db->prepare("
            SELECT r.*, u.username
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.buku_id = ?
            ORDER BY r.id DESC
        ");
        $stmt->execute([$buku_id]);
        return $stmt->fetchAll();
    }
}
