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
    /* === GLOBAL === */
    body {
        background: radial-gradient(circle at top, #0b3a57 0, #020617 45%, #020617 100%);
        color: #e0f2fe;
        font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .page-title {
        font-family: 'Poppins', sans-serif;
        letter-spacing: 3px;
        color: #e0f2fe;
        text-shadow:
            0 0 18px rgba(56, 189, 248, 0.7),
            0 0 35px rgba(59, 130, 246, 0.65);
        position: relative;
    }

    .page-title::after {
        content: "";
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: -10px;
        width: 90px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.9);
    }

    /* === CARD === */
    .shinigami-card {
        position: relative;
        overflow: hidden;
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease, background .35s ease;
        z-index: 1;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 55%),
            radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.12), transparent 60%),
            linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(8, 47, 73, 0.98) 60%, rgba(15, 23, 42, 1));
        box-shadow:
            0 12px 30px rgba(15, 23, 42, 0.9),
            0 0 30px rgba(15, 118, 210, 0.18);
        backdrop-filter: blur(10px);
    }

    .shinigami-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow:
            0 18px 45px rgba(8, 47, 73, 0.95),
            0 0 40px rgba(56, 189, 248, 0.38);
        z-index: 20;
        border-color: rgba(56, 189, 248, 0.55);
        background:
            radial-gradient(circle at top, rgba(56, 189, 248, 0.25), transparent 60%),
            linear-gradient(160deg, rgba(8, 47, 73, 1), rgba(15, 23, 42, 1) 60%, rgba(8, 47, 73, 1));
    }

    .shinigami-card::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: 0;
        background:
            radial-gradient(circle at 20% -10%, rgba(125, 211, 252, 0.28), transparent 70%),
            radial-gradient(circle at 80% 110%, rgba(56, 189, 248, 0.16), transparent 70%);
        transition: opacity .45s ease;
        pointer-events: none;
        z-index: 5;
    }

    .shinigami-card:hover::before {
        opacity: 1;
    }

    /* === STOCK BADGE === */
    .stock-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 50;
        pointer-events: none;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 9999px;
        font-weight: 600;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(15, 23, 42, 0.35);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.7);
    }

    .stock-badge.bg-red-600 {
        background: radial-gradient(circle at top, #f97373, #b91c1c);
        color: #fee2e2;
        border-color: rgba(248, 113, 113, 0.6);
        text-shadow: 0 0 6px rgba(248, 113, 113, 0.9);
    }

    .stock-badge.bg-green-600 {
        background: radial-gradient(circle at top, #34d399, #065f46);
        color: #ecfdf5;
        border-color: rgba(45, 212, 191, 0.6);
        text-shadow: 0 0 6px rgba(45, 212, 191, 0.9);
    }

    /* === COVER AREA === */
    .shinigami-card .w-full.h-72 {
        background:
            radial-gradient(circle at top, rgba(56, 189, 248, 0.16), transparent 65%),
            rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(51, 65, 85, 0.7);
        box-shadow:
            inset 0 0 20px rgba(15, 23, 42, 0.9),
            0 0 25px rgba(56, 189, 248, 0.25);
    }

    .cover-img {
        transition: transform .7s ease, filter .7s ease;
        transform-origin: center;
    }

    .cover-img:hover {
        transform: scale(1.08) translateY(-2px);
        filter: brightness(1.12) saturate(1.15);
    }

    /* === TEXT STYLING === */
    h2.text-xl {
        color: #e0f2fe;
        text-shadow: 0 0 10px rgba(15, 118, 210, 0.35);
    }

    p.text-gray-300 {
        color: #bae6fd;
    }

    p.text-gray-300 span {
        color: #7dd3fc;
    }

    .text-gray-400 {
        color: #9ca3af;
    }

    .text-gray-500 {
        color: #6b7280;
    }

    .text-white {
        color: #f9fafb;
    }

    /* === RATING === */
    .text-yellow-400 {
        color: #facc15;
        text-shadow: 0 0 8px rgba(250, 204, 21, 0.8);
    }

    .text-yellow-300 {
        color: #fde047;
    }

    .text-gray-700.text-lg {
        color: #1f2937;
        text-shadow: none;
    }

    /* === LATEST COMMENT LINK === */
    a.text-purple-400 {
        color: #7dd3fc;
        text-decoration-color: rgba(125, 211, 252, 0.6);
    }

    a.text-purple-400:hover {
        color: #38bdf8;
        text-decoration-color: rgba(56, 189, 248, 0.8);
    }

    /* === BUTTONS === */
    a.bg-purple-700 {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        box-shadow:
            0 10px 25px rgba(37, 99, 235, 0.55),
            0 0 25px rgba(56, 189, 248, 0.55);
    }

    a.bg-purple-700:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        box-shadow:
            0 14px 32px rgba(30, 64, 175, 0.8),
            0 0 35px rgba(56, 189, 248, 0.8);
    }

    .bg-gray-500 {
        background: linear-gradient(135deg, #64748b, #020617);
        border: 1px solid rgba(148, 163, 184, 0.8);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 18px rgba(148, 163, 184, 0.4);
    }

    .bg-gray-700 {
        background: radial-gradient(circle at top, #1e293b, #020617);
        border: 1px solid rgba(51, 65, 85, 0.9);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.9);
    }

    a.bg-green-600 {
        background: linear-gradient(135deg, #22c55e, #0f766e);
        box-shadow:
            0 10px 26px rgba(16, 185, 129, 0.7),
            0 0 24px rgba(45, 212, 191, 0.6);
    }

    a.bg-green-600:hover {
        background: linear-gradient(135deg, #34d399, #0d9488);
        box-shadow:
            0 14px 34px rgba(16, 185, 129, 0.95),
            0 0 35px rgba(45, 212, 191, 0.9);
    }

    .rounded-lg,
    .rounded-xl {
        border-radius: 1rem;
    }
</style>

<div class="max-w-7xl mx-auto py-12 px-4">

    <h1 class="text-4xl font-extrabold mb-12 text-center page-title">
        KOLEKSI BUKU
    </h1>

    <?php if (empty($books)): ?>
        <p class="text-center text-gray-400">Belum ada buku tersedia.</p>

    <?php else: ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10">

        <?php foreach($books as $b): ?>

        <?php
            $ratingData  = $reviewModel->getBookRating($b['id']);
            $avgRating   = $ratingData['avg_rating'] ?? 0;
            $reviewCount = $ratingData['count_review'] ?? 0;
            $latestReview = $reviewModel->getLatestReview($b['id']);
        ?>

        <div class="shinigami-card rounded-xl p-5 relative flex flex-col h-full shadow-lg">

            <!-- STOCK BADGE -->
            <?php if (intval($b['stok']) <= 0): ?>
                <span class="stock-badge bg-red-600 text-white">STOK HABIS</span>
            <?php else: ?>
                <span class="stock-badge bg-green-600 text-white">STOK: <?= intval($b['stok']) ?></span>
            <?php endif; ?>

            <!-- COVER -->
            <div class="w-full h-72 bg-black/40 rounded-lg overflow-hidden flex items-center justify-center shadow-inner">
                <?php if (!empty($b['cover'])): ?>
                    <img src="uploads/cover/<?= htmlspecialchars($b['cover']) ?>"
                         class="cover-img w-full h-full object-cover brightness-95">
                <?php else: ?>
                    <span class="text-gray-500 text-sm">Tidak ada cover</span>
                <?php endif; ?>
            </div>

            <!-- JUDUL -->
            <h2 class="text-xl font-bold mt-4 text-white line-clamp-2 tracking-wide">
                <?= htmlspecialchars($b['judul']) ?>
            </h2>

            <!-- PENULIS -->
            <p class="text-gray-300 text-sm mt-1">
                Oleh: <span class="text-purple-300 font-medium"><?= htmlspecialchars($b['penulis']) ?></span>
            </p>

            <!-- RATING -->
            <div class="mt-3 flex items-center gap-1 text-yellow-400">
                <?php
                for ($i = 1; $i <= 5; $i++):
                    if ($avgRating >= $i) {
                        echo '<span class="text-yellow-400 text-lg">★</span>';
                    } elseif ($avgRating >= $i - 0.5) {
                        echo '<span class="text-yellow-300 text-lg">☆</span>';
                    } else {
                        echo '<span class="text-gray-700 text-lg">★</span>';
                    }
                endfor;
                ?>

                <span class="text-sm text-gray-400 ml-1">
                    (<?= number_format($avgRating, 1) ?>/5)
                </span>

                <?php if($reviewCount > 0): ?>
                    <span class="text-xs text-gray-500">• <?= $reviewCount ?> ulasan</span>
                <?php endif; ?>
            </div>

            <!-- KOMENTAR TERBARU (HANYA USER LOGIN) -->
            <?php if ($isLogin && $latestReview): ?>
                <div class="text-xs text-gray-300 mt-3 italic line-clamp-2">
                    "<?= htmlspecialchars(substr($latestReview['komentar'], 0, 70)) ?>..."
                </div>

                <a href="reviews.php?book_id=<?= $b['id'] ?>"
                   class="text-purple-400 text-xs underline mt-2 block hover:text-purple-300 transition">
                    Lihat Semua Ulasan →
                </a>
            <?php endif; ?>

            <!-- BUTTON -->
            <div class="mt-auto pt-6">
                <?php if (!$isLogin): ?>

                    <a href="login.php"
                       class="block text-center bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg
                              font-semibold shadow-md transition-all duration-300 hover:-translate-y-1">
                        Register/Login untuk meminjam
                    </a>

                <?php elseif ($user['role'] !== 'peminjam'): ?>

                    <div class="text-center bg-gray-500 text-white py-2 rounded-lg cursor-not-allowed font-medium">
                        Admin/Petugas tidak bisa meminjam
                    </div>

                <?php else: ?>

                    <?php if (intval($b['stok']) <= 0): ?>
                        <div class="text-center bg-gray-700 text-gray-400 py-2 rounded-lg cursor-not-allowed font-medium">
                            Tidak Tersedia
                        </div>

                    <?php else: ?>
                        <a href="borrow.php?pinjam_buku=<?= $b['id'] ?>"
                           class="block text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg 
                                  font-semibold shadow-lg transition-all duration-300 hover:-translate-y-1">
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
