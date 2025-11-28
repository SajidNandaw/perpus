<?php
// templates/header.php
require_once __DIR__ . '/../app/config.php';

$auth = new Auth($db->pdo());
$user = $auth->user();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perpustakaan Digital</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <style>
    body {
        background: #020617; /* biru gelap sederhana */
        color: #e5e7eb;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* NAVBAR SEDERHANA */
    .nav-bar {
        background: #020617;
        border-bottom: 1px solid #1f2937;
    }

    .nav-inner {
        max-width: 1120px;
    }

    .nav-logo {
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #e5e7eb;
        font-size: 1rem;
        font-weight: 700;
    }

    .nav-links a {
        font-size: 0.9rem;
        color: #cbd5f5;
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
    }

    .nav-links a:hover {
        background: #111827;
        color: #e5e7eb;
    }

    .nav-user-chip {
        font-size: 0.8rem;
        background: #0b1120;
        border-radius: 999px;
        padding: 0.25rem 0.75rem;
        border: 1px solid #1f2937;
    }

    .nav-user-role {
        color: #93c5fd;
        font-style: italic;
        font-size: 0.75rem;
    }

    .nav-logout {
        font-size: 0.85rem;
        color: #fca5a5;
    }

    .nav-logout:hover {
        color: #fecaca;
    }
  </style>
</head>

<body class="min-h-screen">

<nav class="nav-bar">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center nav-inner">

    <!-- LOGO -->
    <a href="index.php" class="nav-logo">
      PERPUSTAKAAN DIGITAL
    </a>

    <?php if($user): ?>
    <div class="flex items-center space-x-4 nav-links">

      <!-- USER INFO -->
      <span class="nav-user-chip flex items-center space-x-2">
        <span>Halo, <?= htmlspecialchars($user['nama'] ?: $user['username']) ?></span>
        <span class="nav-user-role"><?= htmlspecialchars($user['role']) ?></span>
      </span>

      <?php
        // fungsi helper link
        function navlink($href, $text) {
          return "<a href=\"$href\">$text</a>";
        }
      ?>

      <?= navlink('index.php', 'Home'); ?>

      <?php if (in_array($user['role'], ['administrator', 'petugas'])): ?>
        <?= navlink('books.php', 'Pendataan Buku'); ?>
      <?php endif; ?>

      <?php if ($user['role'] === 'peminjam'): ?>
        <?= navlink('borrow.php', 'Peminjaman'); ?>
      <?php endif; ?>

      <?php if ($user['role'] === 'administrator'): ?>
        <?= navlink('admin_users.php', 'Manajemen User'); ?>
        <?= navlink('reports.php', 'Laporan'); ?>
      <?php elseif ($user['role'] === 'petugas'): ?>
        <?= navlink('reports.php', 'Laporan'); ?>
      <?php endif; ?>

      <!-- LOGOUT -->
      <a href="logout.php" class="nav-logout">
        Logout
      </a>

    </div>
    <?php endif; ?>

  </div>
</nav>

<main class="container mx-auto p-4">
