<?php
require_once __DIR__.'/../templates/header.php';

$auth    = new Auth($db->pdo());
$isLogin = $auth->check();
$user    = $isLogin ? $auth->user() : null;

$bookModel   = new BookModel($db->pdo());
$reviewModel = new ReviewModel($db->pdo());

$books = $bookModel->all();
?>

<style>
    body {
        background: #020617;
        color: #e5e7eb;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .page-wrapper {
        max-width: 1120px;
        margin: 0 auto;
        padding: 2.5rem 1rem 3rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 2rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #e5e7eb;
    }

    .page-subtitle {
        font-size: 0.9rem;
        text-align: center;
        color: #9ca3af;
        margin-top: -1.2rem;
        margin-bottom: 2.2rem;
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
    }

    @media (min-width: 640px) {
        .books-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .books-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .book-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        border-radius: 0.75rem;
        border: 1px solid #1f2933;
        background: #020617;
        padding: 0.75rem;
        gap: 0.6rem;
    }

    .book-cover {
        width: 100%;
        height: 220px;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #020617;
        border: 1px solid #111827;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .book-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .book-title {
        font-size: 0.98rem;
        font-weight: 600;
        color: #f9fafb;
        margin-top: 0.2rem;
        line-height: 1.3;
    }

    .book-author {
        font-size: 0.85rem;
        color: #9ca3af;
    }

    .book-author span {
        color: #e5e7eb;
        font-weight: 500;
    }

    .stock-badge {
        font-size: 0.7rem;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        display: inline-block;
        margin-top: 0.25rem;
    }

    .stock-badge.available {
        background: #064e3b;
        color: #bbf7d0;
    }

    .stock-badge.empty {
        background: #7f1d1d;
        color: #fecaca;
    }

    .rating-row {
        display: flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .rating-stars span {
        font-size: 0.95rem;
    }

    .rating-value {
        color: #9ca3af;
        margin-left: 0.15rem;
    }

    .rating-count {
        font-size: 0.75rem;
        color: #6b7280;
        margin-left: 0.2rem;
    }

    .latest-review {
        font-size: 0.78rem;
        color: #9ca3af;
        margin-top: 0.4rem;
        font-style: italic;
    }

    .latest-review-link {
        font-size: 0.78rem;
        color: #38bdf8;
        margin-top: 0.2rem;
        display: inline-block;
        text-decoration: underline;
    }

    .card-footer {
        margin-top: auto;
        padding-top: 0.75rem;
    }

    .btn {
        display: block;
        width: 100%;
        text-align: center;
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #16a34a;
        color: #ecfdf5;
    }

    .btn-primary:hover {
        background: #15803d;
    }

    .btn-disabled {
        background: #111827;
        color: #6b7280;
        cursor: not-allowed;
    }

    .btn-info {
        background: #4f46e5;
        color: #eef2ff;
    }

    .btn-info:hover {
        background: #4338ca;
    }

    .info-text {
        text-align: center;
        color: #9ca3af;
        font-size: 0.9rem;
    }
</style>

<div class="page-wrapper">

    <h1 class="page-title">KOLEKSI BUKU</h1>
    <p class="page-subtitle">Daftar buku yang tersedia di perpustakaan.</p>

    <?php if (empty($books)): ?>
        <p class="info-text">Belum ada buku tersedia.</p>

    <?php else: ?>

    <div class="books-grid">

        <?php foreach($books as $b): ?>

        <?php
            $ratingData   = $reviewModel->getBookRating($b['id']);
            $avgRating    = $ratingData['avg_rating'] ?? 0;
            $reviewCount  = $ratingData['count_review'] ?? 0;
            $latestReview = $reviewModel->getLatestReview($b['id']);
        ?>

        <div class="book-card">

            <!-- COVER -->
            <div class="book-cover">
                <?php if (!empty($b['cover'])): ?>
                    <img src="uploads/cover/<?= htmlspecialchars($b['cover']) ?>" alt="Cover <?= htmlspecialchars($b['judul']) ?>">
                <?php else: ?>
                    <span style="font-size:0.8rem; color:#6b7280;">Tidak ada cover</span>
                <?php endif; ?>
            </div>

            <!-- JUDUL -->
            <div class="book-title">
                <?= htmlspecialchars($b['judul']) ?>
            </div>

            <!-- PENULIS -->
            <div class="book-author">
                Oleh: <span><?= htmlspecialchars($b['penulis']) ?></span>
            </div>

            <!-- STOK -->
            <?php if (intval($b['stok']) <= 0): ?>
                <span class="stock-badge empty">Stok habis</span>
            <?php else: ?>
                <span class="stock-badge available">Stok: <?= intval($b['stok']) ?></span>
            <?php endif; ?>

            <!-- RATING -->
            <div class="rating-row">
                <div class="rating-stars">
                    <?php
                    for ($i = 1; $i <= 5; $i++):
                        if ($avgRating >= $i) {
                            echo '<span>★</span>';
                        } elseif ($avgRating >= $i - 0.5) {
                            echo '<span>☆</span>';
                        } else {
                            echo '<span style="color:#4b5563;">★</span>';
                        }
                    endfor;
                    ?>
                </div>

                <span class="rating-value">
                    (<?= number_format($avgRating, 1) ?>/5)
                </span>

                <?php if($reviewCount > 0): ?>
                    <span class="rating-count">
                        • <?= $reviewCount ?> ulasan
                    </span>
                <?php endif; ?>
            </div>

            <!-- KOMENTAR TERBARU (HANYA USER LOGIN) -->
            <?php if ($isLogin && $latestReview): ?>
                <div class="latest-review">
                    "<?= htmlspecialchars(substr($latestReview['komentar'], 0, 70)) ?>..."
                </div>

                <a href="reviews.php?book_id=<?= $b['id'] ?>" class="latest-review-link">
                    Lihat semua ulasan
                </a>
            <?php endif; ?>

            <!-- BUTTON -->
            <div class="card-footer">
                <?php if (!$isLogin): ?>

                    <a href="login.php" class="btn btn-info">
                        Login / Register untuk meminjam
                    </a>

                <?php elseif ($user['role'] !== 'peminjam'): ?>

                    <div class="btn btn-disabled">
                        Admin/Petugas tidak bisa meminjam
                    </div>

                <?php else: ?>

                    <?php if (intval($b['stok']) <= 0): ?>
                        <div class="btn btn-disabled">
                            Tidak tersedia
                        </div>
                    <?php else: ?>
                        <a href="borrow.php?pinjam_buku=<?= $b['id'] ?>" class="btn btn-primary">
                            Pinjam
                        </a>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
