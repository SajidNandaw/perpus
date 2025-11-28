<?php
require_once __DIR__ . '/../templates/header.php';

$auth    = new Auth($db->pdo());
$isLogin = $auth->check();

$bookModel   = new BookModel($db->pdo());
$reviewModel = new ReviewModel($db->pdo());

// Ambil ID buku
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$book    = $bookModel->find($book_id);

if (!$book) {
    echo "<div class='max-w-3xl mx-auto py-10 text-center text-red-400'>
            Buku tidak ditemukan.
          </div>";
    require_once __DIR__ . '/../templates/footer.php';
    exit;
}

// Ambil semua ulasan buku
$reviews = $reviewModel->getAllReviewsByBook($book_id);

// Ambil rating rata-rata
$ratingData  = $reviewModel->getBookRating($book_id);
$avgRating   = $ratingData['avg_rating'] ?? 0;
$reviewCount = $ratingData['count_review'] ?? 0;
?>

<style>
    body {
        background: #020617;
        color: #e5e7eb;
    }

    .book-page {
        max-width: 720px;
        margin: 0 auto;
        padding: 2rem 1.25rem 2.5rem;
    }

    .back-link {
        color: #38bdf8;
        font-size: .9rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        margin-bottom: .75rem;
    }

    .back-link:hover {
        color: #e0f2fe;
    }

    .book-card {
        background: #020617;
        border-radius: 10px;
        border: 1px solid #1f2937;
        padding: 16px 16px 18px;
    }

    .book-header {
        display: flex;
        gap: 1rem;
    }

    .book-cover-box {
        width: 6rem;
        height: 9rem;
        border-radius: 6px;
        border: 1px solid #1f2937;
        background: #020617;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        color: #6b7280;
        overflow: hidden;
    }

    .book-cover-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .book-info-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .book-info-author {
        font-size: .9rem;
        color: #9ca3af;
    }

    .rating-row {
        display: flex;
        align-items: center;
        gap: .25rem;
        margin-top: .6rem;
        font-size: .9rem;
    }

    .rating-stars span {
        font-size: 1.1rem;
    }

    .rating-text {
        margin-left: .25rem;
        font-size: .9rem;
    }

    .rating-count {
        font-size: .8rem;
        color: #9ca3af;
        margin-left: .4rem;
    }

    .section-title {
        font-size: .95rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: .6rem;
    }

    .section-title-line {
        width: 50px;
        height: 2px;
        border-radius: 999px;
        background: #1f2937;
        margin-top: 2px;
    }

    .text-muted {
        color: #9ca3af;
        font-size: .9rem;
    }

    .review-list {
        margin-top: .25rem;
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .review-item {
        background: #020617;
        border-radius: 8px;
        border: 1px solid #1f2937;
        padding: 10px 12px;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem;
        margin-bottom: .35rem;
    }

    .review-username {
        font-size: .9rem;
        font-weight: 600;
    }

    .review-stars span {
        font-size: 1rem;
    }

    .review-score {
        font-size: .8rem;
        margin-left: .3rem;
    }

    .review-comment {
        font-size: .9rem;
        margin-top: .2rem;
        white-space: pre-line;
    }

    .review-meta {
        font-size: .75rem;
        color: #9ca3af;
        margin-top: .35rem;
    }

    @media (max-width: 640px) {
        .book-page {
            padding-inline: 1rem;
        }
        .book-header {
            flex-direction: row;
        }
        .book-info-title {
            font-size: 1.15rem;
        }
    }
</style>

<div class="book-page">

    <a href="index.php" class="back-link">
        <span>&larr;</span>
        <span>Kembali ke daftar buku</span>
    </a>

    <div class="book-card">

        <!-- HEADER BUKU -->
        <div class="book-header">
            <div class="book-cover-box">
                <?php if (!empty($book['cover'])): ?>
                    <img src="uploads/cover/<?= htmlspecialchars($book['cover']) ?>" alt="Cover">
                <?php else: ?>
                    <span>Tidak ada cover</span>
                <?php endif; ?>
            </div>

            <div class="book-info">
                <h1 class="book-info-title"><?= htmlspecialchars($book['judul']) ?></h1>
                <p class="book-info-author">
                    Oleh: <span><?= htmlspecialchars($book['penulis']) ?></span>
                </p>

                <!-- RATING -->
                <div class="rating-row">
                    <div class="rating-stars">
                        <?php
                        for ($i = 1; $i <= 5; $i++):
                            if ($avgRating >= $i) {
                                echo '<span class="text-yellow-400">★</span>';
                            } elseif ($avgRating >= $i - 0.5) {
                                echo '<span class="text-yellow-300">☆</span>';
                            } else {
                                echo '<span class="text-gray-700">★</span>';
                            }
                        endfor;
                        ?>
                    </div>
                    <span class="rating-text">
                        (<?= number_format($avgRating, 1) ?>/5)
                    </span>
                    <?php if ($reviewCount > 0): ?>
                        <span class="rating-count">
                            • <?= $reviewCount ?> ulasan
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ULASAN -->
        <div style="margin-top: 1.75rem;">
            <div class="section-title">
                Ulasan Pembaca
                <div class="section-title-line"></div>
            </div>

            <?php if (empty($reviews)): ?>

                <p class="text-muted">Belum ada ulasan untuk buku ini.</p>

            <?php else: ?>

                <div class="review-list">
                    <?php foreach ($reviews as $r): ?>
                        <div class="review-item">

                            <div class="review-header">
                                <div class="review-username">
                                    <?= htmlspecialchars($r['username']) ?>
                                </div>
                                <div style="display:flex; align-items:center;">
                                    <div class="review-stars">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++):
                                            if ($r['rating'] >= $i) {
                                                echo '<span class="text-yellow-400">★</span>';
                                            } else {
                                                echo '<span class="text-gray-700">★</span>';
                                            }
                                        endfor;
                                        ?>
                                    </div>
                                    <span class="review-score">(<?= $r['rating'] ?>/5)</span>
                                </div>
                            </div>

                            <p class="review-comment">
                                <?= nl2br(htmlspecialchars($r['komentar'])) ?>
                            </p>

                            <p class="review-meta">
                                Ditulis pada: <?= htmlspecialchars($r['created_at']) ?>
                            </p>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>

    </div>

</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
