<?php
require_once __DIR__ . '/../templates/header.php';

$auth = new Auth($db->pdo());
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
    /* CARD UTAMA DETAIL BUKU */
    .card-water {
        background: linear-gradient(160deg, #020617, #0f172a 55%, #020617);
        border: 1px solid rgba(56, 189, 248, 0.4);
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.85);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        position: relative;
        overflow: hidden;
    }

    .card-water::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 0%, rgba(56, 189, 248, 0.18), transparent 60%),
            radial-gradient(circle at 80% 110%, rgba(37, 99, 235, 0.14), transparent 65%);
        opacity: 0.35;
        pointer-events: none;
        z-index: 0;
    }

    .card-water:hover {
        border-color: rgba(56, 189, 248, 0.65);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.9);
        transform: translateY(-2px);
    }

    .title-water {
        font-size: 1.8rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow: 0 0 10px rgba(56, 189, 248, 0.6);
        letter-spacing: 0.08em;
    }

    .title-water-sub {
        color: #bae6fd;
        font-size: 0.9rem;
    }

    .text-muted {
        color: #9ca3af;
    }

    .back-link {
        color: #38bdf8;
        font-weight: 500;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: color .2s ease, transform .15s ease;
    }

    .back-link:hover {
        color: #e0f2fe;
        transform: translateX(-2px);
    }

    .cover-box {
        background: #020617;
        border-radius: 0.9rem;
        border: 1px solid rgba(30, 64, 175, 0.5);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.9);
    }

    /* RATING BINTANG */
    .stars-wrapper span {
        text-shadow: 0 0 4px rgba(250, 204, 21, 0.5);
    }

    /* BOX ULASAN */
    .review-box {
        background: #020617;
        border-radius: 14px;
        border: 1px solid rgba(30, 64, 175, 0.6);
        padding: 16px 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.95);
        transition: background .15s ease, border-color .15s ease;
        position: relative;
        overflow: hidden;
    }

    .review-box::before {
        content: "";
        position: absolute;
        inset: -40%;
        background: radial-gradient(circle at 0 0, rgba(56, 189, 248, 0.16), transparent 60%);
        opacity: 0.4;
        pointer-events: none;
    }

    .review-box:hover {
        background: #020c1b;
        border-color: rgba(56, 189, 248, 0.7);
    }

    .review-username {
        color: #e0f2fe;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .review-meta {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .review-comment {
        color: #e5e7eb;
        font-size: 0.9rem;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #e0f2fe;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .section-title::after {
        content: "";
        display: inline-block;
        width: 60px;
        height: 2px;
        border-radius: 999px;
        margin-left: 8px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8);
        vertical-align: middle;
        opacity: 0.7;
    }
</style>


<div class="max-w-3xl mx-auto py-10">

    <a href="index.php" class="back-link">
        <span>&larr;</span> <span>Kembali ke daftar buku</span>
    </a>

    <div class="card-water mt-5">

        <!-- HEADER BUKU -->
        <div class="flex gap-5 relative z-10">

            <div class="h-40 w-32 overflow-hidden cover-box flex items-center justify-center">
                <?php if (!empty($book['cover'])): ?>
                    <img src="uploads/cover/<?= htmlspecialchars($book['cover']) ?>" 
                         class="h-full w-full object-cover">
                <?php else: ?>
                    <span class="text-gray-400 text-xs">Tidak ada cover</span>
                <?php endif; ?>
            </div>

            <div class="flex-1">
                <h1 class="title-water"><?= htmlspecialchars($book['judul']) ?></h1>

                <p class="title-water-sub mt-1">
                    Oleh: <span class="font-semibold"><?= htmlspecialchars($book['penulis']) ?></span>
                </p>

                <!-- RATING -->
                <div class="flex items-center gap-1 mt-3 stars-wrapper">
                    <?php
                    for ($i = 1; $i <= 5; $i++):
                        if ($avgRating >= $i) {
                            echo '<span class="text-yellow-400 text-xl">★</span>';
                        } elseif ($avgRating >= $i - 0.5) {
                            echo '<span class="text-yellow-300 text-xl">☆</span>';
                        } else {
                            echo '<span class="text-gray-700 text-xl">★</span>';
                        }
                    endfor;
                    ?>

                    <span class="ml-1 text-sm text-gray-200">
                        (<?= number_format($avgRating, 1) ?>/5)
                    </span>

                    <?php if ($reviewCount > 0): ?>
                        <span class="text-xs text-gray-400 ml-2">
                            • <?= $reviewCount ?> ulasan
                        </span>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- DAFTAR ULASAN -->
        <h2 class="section-title mt-8 mb-4">
            Ulasan Pembaca
        </h2>

        <?php if (empty($reviews)): ?>

            <p class="text-gray-400 italic">Belum ada ulasan untuk buku ini.</p>

        <?php else: ?>

            <div class="space-y-4">

                <?php foreach ($reviews as $r): ?>
                    <div class="review-box">

                        <div class="flex items-center justify-between mb-1 relative z-10">

                            <div class="review-username">
                                <?= htmlspecialchars($r['username']) ?>
                            </div>

                            <div class="flex items-center">
                                <?php
                                for ($i = 1; $i <= 5; $i++):
                                    if ($r['rating'] >= $i) {
                                        echo '<span class="text-yellow-400 text-lg">★</span>';
                                    } else {
                                        echo '<span class="text-gray-700 text-lg">★</span>';
                                    }
                                endfor;
                                ?>
                                <span class="text-sm ml-1 text-gray-200">(<?= $r['rating'] ?>/5)</span>
                            </div>

                        </div>

                        <!-- KOMENTAR -->
                        <p class="review-comment mt-2 relative z-10">
                            <?= nl2br(htmlspecialchars($r['komentar'])) ?>
                        </p>

                        <p class="review-meta mt-1 relative z-10">
                            Ditulis pada: <?= $r['created_at'] ?>
                        </p>

                    </div>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
