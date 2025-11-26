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
        background: radial-gradient(circle at top, #0b3a57 0, #020617 45%, #020617 100%);
        color: #e0f2fe;
        font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* NAVBAR WATER THEME */
    .nav-water {
        background:
          radial-gradient(circle at 10% 0, rgba(56, 189, 248, 0.25), transparent 55%),
          radial-gradient(circle at 90% 120%, rgba(37, 99, 235, 0.3), transparent 60%),
          rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(18px);
        border-bottom: 1px solid rgba(56, 189, 248, 0.4);
        box-shadow:
          0 18px 45px rgba(15, 23, 42, 0.9),
          0 0 35px rgba(56, 189, 248, 0.4);
    }

    .nav-inner {
        max-width: 1120px;
    }

    .nav-logo {
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #e0f2fe;
        text-shadow:
          0 0 14px rgba(56, 189, 248, 0.75),
          0 0 28px rgba(37, 99, 235, 0.7);
        position: relative;
    }

    .nav-logo::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -4px;
        width: 55%;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.9);
    }

    .nav-user-chip {
        background: radial-gradient(circle at top, rgba(56, 189, 248, 0.35), rgba(15, 23, 42, 0.95));
        border-radius: 9999px;
        border: 1px solid rgba(56, 189, 248, 0.6);
        box-shadow:
          0 12px 30px rgba(15, 23, 42, 0.9),
          0 0 20px rgba(56, 189, 248, 0.6);
    }

    .nav-user-role {
        color: #7dd3fc;
        text-shadow: 0 0 8px rgba(56, 189, 248, 0.9);
    }

    .nav-link {
        position: relative;
        font-weight: 500;
        font-size: 0.95rem;
        color: #bae6fd;
        transition: color .25s ease, transform .25s ease;
    }

    .nav-link::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -4px;
        width: 0;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, #38bdf8, #0ea5e9);
        box-shadow: 0 0 8px rgba(56, 189, 248, 0.9);
        transition: width .25s ease;
    }

    .nav-link:hover {
        color: #e0f2fe;
        transform: translateY(-1px);
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .nav-logout {
        font-weight: 600;
        font-size: 0.95rem;
        color: #fb7185;
        text-shadow: 0 0 8px rgba(248, 113, 113, 0.9);
        transition: color .25s ease, transform .25s ease, text-shadow .25s ease;
    }

    .nav-logout:hover {
        color: #fecaca;
        transform: translateY(-1px);
        text-shadow:
          0 0 12px rgba(248, 113, 113, 1),
          0 0 20px rgba(239, 68, 68, 0.8);
    }
  </style>
</head>

<body class="min-h-screen">

<nav class="nav-water">
  <div class="container mx-auto px-4 py-4 flex justify-between items-center nav-inner">

    <!-- LOGO -->
    <a href="index.php"
       class="nav-logo font-bold text-xl sm:text-2xl tracking-[0.25em]">
      PERPUSTAKAAN DIGITAL
    </a>

    <div class="flex items-center space-x-6">

      <?php if($user): ?>

        <!-- USER INFO -->
        <span class="nav-user-chip font-semibold text-xs sm:text-sm py-1.5 px-3 sm:px-4 flex items-center gap-1">
          <span>Halo, <?= htmlspecialchars($user['nama'] ?: $user['username']) ?></span>
          <span class="mx-1">•</span>
          <span class="nav-user-role italic text-xs">
            <?= htmlspecialchars($user['role']) ?>
          </span>
        </span>

        <!-- NAV LINK FUNCTION -->
        <?php
          function navlink($href, $text) {
            return "<a href='$href' class=\"nav-link\">$text</a>";
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

      <?php endif; ?>
    </div>

  </div>
</nav>

<main class="container mx-auto p-4">
