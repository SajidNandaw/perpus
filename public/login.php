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
    /* === CARD LOGIN WATER THEME === */
    .login-card {
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 55%),
            radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.22), transparent 60%),
            linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(8, 47, 73, 0.98) 60%, rgba(15, 23, 42, 1));
        border: 1px solid rgba(56, 189, 248, 0.45);
        padding: 28px;
        border-radius: 18px;
        box-shadow:
            0 24px 55px rgba(15, 23, 42, 0.95),
            0 0 32px rgba(56, 189, 248, 0.4);
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease, background .35s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(16px);
    }

    .login-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 0%, rgba(125, 211, 252, 0.25), transparent 55%),
            radial-gradient(circle at 80% 110%, rgba(56, 189, 248, 0.18), transparent 60%);
        opacity: 0;
        transition: opacity .5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .login-card:hover {
        transform: translateY(-6px);
        box-shadow:
            0 28px 65px rgba(8, 47, 73, 1),
            0 0 45px rgba(56, 189, 248, 0.6);
        border-color: rgba(56, 189, 248, 0.8);
    }

    .login-card:hover::before {
        opacity: 1;
    }

    .login-title {
        text-shadow:
            0 0 14px rgba(56, 189, 248, 0.8),
            0 0 30px rgba(37, 99, 235, 0.7);
        letter-spacing: 4px;
        color: #e0f2fe;
        position: relative;
    }

    .login-title::after {
        content: "";
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: -8px;
        width: 70px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.9);
    }

    /* INPUTS */
    input {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.6);
        color: #e0f2fe;
        transition: border-color .3s ease, box-shadow .3s ease, background .3s ease, transform .2s ease;
        border-radius: 0.75rem;
    }

    input::placeholder {
        color: #64748b;
    }

    input:focus {
        border-color: #38bdf8;
        box-shadow:
            0 0 0 1px rgba(56, 189, 248, 0.9),
            0 0 18px rgba(56, 189, 248, 0.7);
        outline: none;
        background: radial-gradient(circle at top, rgba(15, 23, 42, 1), rgba(8, 47, 73, 0.95));
        transform: translateY(-1px);
    }

    label {
        color: #bae6fd;
    }

    /* BUTTON */
    .btn-login {
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        box-shadow:
            0 14px 34px rgba(37, 99, 235, 0.8),
            0 0 28px rgba(56, 189, 248, 0.8);
        border-radius: 9999px;
    }

    .btn-login:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        transform: translateY(-2px);
        box-shadow:
            0 18px 40px rgba(30, 64, 175, 0.95),
            0 0 36px rgba(56, 189, 248, 1);
    }

    .btn-login:active {
        transform: translateY(0);
        box-shadow:
            0 8px 22px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.7);
    }

    /* ERROR ALERT */
    .alert-error {
        color: #fecaca;
        background: rgba(127, 29, 29, 0.25);
        border: 1px solid rgba(248, 113, 113, 0.7);
        border-radius: 9999px;
        padding: 8px 14px;
        box-shadow: 0 0 18px rgba(248, 113, 113, 0.5);
    }

    /* TEXT BELOW */
    .text-gray-400 {
        color: #9ca3af;
    }

    .link-register {
        color: #7dd3fc;
        text-decoration-color: rgba(125, 211, 252, 0.7);
        transition: color .25s ease, text-decoration-color .25s ease;
    }

    .link-register:hover {
        color: #38bdf8;
        text-decoration-color: rgba(56, 189, 248, 0.9);
    }
</style>

<div class="max-w-md mx-auto mt-24">

    <div class="login-card">

        <h2 class="text-3xl font-extrabold text-center mb-10 login-title">
            LOGIN
        </h2>

        <?php if(!empty($err)): ?>
            <div class="mb-4 text-center text-sm alert-error">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-5 relative z-10">

            <div>
                <label class="block mb-1 font-medium">Username</label>
                <input name="username"
                       class="w-full p-3"
                       required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Password</label>
                <input type="password"
                       name="password"
                       class="w-full p-3"
                       required>
            </div>

            <button class="btn-login w-full text-white py-2 font-semibold text-lg">
                Login
            </button>
        </form>

        <div class="text-center text-gray-400 mt-6 text-sm">
            Belum punya akun?
            <a href="register_peminjam.php"
               class="link-register underline">
                Register
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
