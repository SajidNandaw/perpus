<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    if($auth->login($u, $p)){
        header('Location: index.php');
        exit;
    } else {
        $err = "Username atau password salah";
    }
}
?>

<style>
    .login-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-card-simple {
        background: #0b1120; /* dark navy */
        border: 1px solid #1f2937;
        border-radius: 10px;
        padding: 24px 20px;
    }

    .login-title-simple {
        font-size: 1.5rem;
        font-weight: 700;
        color: #e5e7eb;
        text-align: center;
        letter-spacing: 0.15em;
    }

    .login-subtitle {
        font-size: .9rem;
        color: #9ca3af;
        text-align: center;
        margin-top: 4px;
    }

    .login-label {
        font-size: .9rem;
        color: #e5e7eb;
        margin-bottom: 4px;
        display: block;
    }

    .login-input {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #374151;
        background: #020617;
        color: #e5e7eb;
        font-size: 0.9rem;
    }

    .login-input:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 1px #60a5fa;
    }

    .login-btn {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        background: #2563eb;
        color: #f9fafb;
        font-weight: 600;
        font-size: .95rem;
    }

    .login-btn:hover {
        background: #1d4ed8;
    }

    .login-error {
        background: #7f1d1d;
        border: 1px solid #b91c1c;
        color: #fecaca;
        font-size: .85rem;
        padding: 8px 10px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 12px;
    }

    .login-bottom-text {
        font-size: .85rem;
        color: #9ca3af;
        text-align: center;
        margin-top: 10px;
    }

    .login-link {
        color: #60a5fa;
        text-decoration: underline;
    }

    .login-link:hover {
        color: #93c5fd;
    }
</style>

<div class="login-wrapper px-4">
    <div class="w-full max-w-sm login-card-simple">

        <h2 class="login-title-simple">LOGIN</h2>
        <p class="login-subtitle">Masuk untuk mengakses perpustakaan</p>

        <?php if(!empty($err)): ?>
            <div class="login-error mt-4">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-5 space-y-4">

            <div>
                <label class="login-label">Username</label>
                <input name="username"
                       class="login-input"
                       required>
            </div>

            <div>
                <label class="login-label">Password</label>
                <input type="password"
                       name="password"
                       class="login-input"
                       required>
            </div>

            <button class="login-btn mt-2">
                Login
            </button>
        </form>

        <div class="login-bottom-text">
            Belum punya akun?
            <a href="register_peminjam.php" class="login-link">
                Register
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
