<?php
require_once __DIR__.'/../templates/header.php';

$auth = new Auth($db->pdo());
$auth->requireRole(['administrator']); // hanya admin yang boleh membuka halaman ini

$userModel = new UserModel($db->pdo());

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama     = $_POST['nama_lengkap'];
    $email    = $_POST['email'];
    $alamat   = $_POST['alamat'];
    $role     = $_POST['role'] === 'petugas' ? 'petugas' : 'administrator';

    // Cek username duplikat
    if($userModel->findByUsername($username)){
        $err = "Username sudah dipakai.";
    } else {

        // Simpan akun baru ke database
        $userModel->create([
            'username'      => $username,
            'password'      => $password,
            'nama_lengkap'  => $nama,
            'email'         => $email,
            'role'          => $role,
            'alamat'        => $alamat
        ]);

        $sukses = "Akun $role berhasil dibuat.";
    }
}
?>

<style>
    /* === CARD REGISTER ADMIN WATER THEME === */
    .card-shinigami {
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 55%),
            radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.22), transparent 60%),
            linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(8, 47, 73, 0.98) 60%, rgba(15, 23, 42, 1));
        border-radius: 20px;
        padding: 28px;
        box-shadow:
            0 24px 55px rgba(15, 23, 42, 0.95),
            0 0 32px rgba(56, 189, 248, 0.4);
        backdrop-filter: blur(16px);
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease, background .35s ease;
        border: 1px solid rgba(56, 189, 248, 0.55);
        position: relative;
        overflow: hidden;
        margin-top: 2.5rem;
    }

    .card-shinigami::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 15% 0%, rgba(125, 211, 252, 0.28), transparent 55%),
            radial-gradient(circle at 90% 100%, rgba(37, 99, 235, 0.28), transparent 60%);
        opacity: 0;
        transition: opacity .5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .card-shinigami:hover {
        box-shadow:
            0 28px 65px rgba(8, 47, 73, 1),
            0 0 45px rgba(56, 189, 248, 0.7);
        transform: translateY(-6px);
        border-color: rgba(56, 189, 248, 0.9);
    }

    .card-shinigami:hover::before {
        opacity: 1;
    }

    /* HEADER */
    .reg-title-water {
        font-size: 1.7rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow:
            0 0 18px rgba(56, 189, 248, 0.9),
            0 0 30px rgba(37, 99, 235, 0.8);
        letter-spacing: 0.16em;
        text-transform: uppercase;
        position: relative;
    }

    .reg-title-water::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -8px;
        width: 250px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.95);
    }

    .link-back {
        color: #bae6fd;
        font-size: 0.85rem;
        transition: color .25s ease, text-shadow .25s ease, transform .15s ease;
    }

    .link-back:hover {
        color: #e0f2fe;
        text-shadow:
            0 0 10px rgba(56, 189, 248, 0.9),
            0 0 14px rgba(37, 99, 235, 0.7);
        transform: translateY(-1px);
    }

    /* INPUT, TEXTAREA, SELECT */
    .shinigami-label {
        font-size: 0.9rem;
        color: #bae6fd;
        font-weight: 500;
    }

    .shinigami-input {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.6);
        color: #e0f2fe;
        padding: 10px 12px;
        border-radius: 0.75rem;
        width: 100%;
        margin-top: 5px;
        font-size: 0.95rem;
        transition: border-color .3s ease, box-shadow .3s ease, background .3s ease, transform .2s ease;
    }

    .shinigami-input::placeholder {
        color: #64748b;
    }

    .shinigami-input:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow:
            0 0 0 1px rgba(56, 189, 248, 0.9),
            0 0 18px rgba(56, 189, 248, 0.7);
        background: radial-gradient(circle at top, rgba(15, 23, 42, 1), rgba(8, 47, 73, 0.95));
        transform: translateY(-1px);
    }

    textarea.shinigami-input {
        min-height: 90px;
        resize: vertical;
    }

    select.shinigami-input {
        cursor: pointer;
    }

    /* BUTTON SUBMIT */
    .shinigami-btn {
        width: 100%;
        padding: 11px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #e0f2fe;
        border-radius: 9999px;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        margin-top: 1.5rem;
        box-shadow:
            0 14px 34px rgba(37, 99, 235, 0.8),
            0 0 28px rgba(56, 189, 248, 0.8);
    }

    .shinigami-btn:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        transform: translateY(-2px);
        box-shadow:
            0 18px 40px rgba(30, 64, 175, 0.95),
            0 0 36px rgba(56, 189, 248, 1);
    }

    .shinigami-btn:active {
        transform: translateY(0);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.7);
    }

    /* ALERTS */
    .alert-error {
        color: #fecaca;
        font-weight: 600;
        text-align: center;
        margin-bottom: 12px;
        background: rgba(127, 29, 29, 0.25);
        border-radius: 9999px;
        padding: 8px 14px;
        border: 1px solid rgba(248, 113, 113, 0.7);
        box-shadow: 0 0 18px rgba(248, 113, 113, 0.5);
    }

    .alert-success {
        color: #bbf7d0;
        font-weight: 600;
        text-align: center;
        margin-bottom: 12px;
        background: rgba(5, 46, 22, 0.4);
        border-radius: 9999px;
        padding: 8px 14px;
        border: 1px solid rgba(74, 222, 128, 0.7);
        box-shadow: 0 0 18px rgba(52, 211, 153, 0.55);
    }
</style>

<div class="max-w-lg mx-auto card-shinigami">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4 relative z-10">
        <h2 class="reg-title-water">Register Admin / Petugas</h2>

        <a href="admin_users.php" class="link-back text-sm">← Kembali</a>
    </div>

    <!-- ERROR -->
    <?php if(!empty($err)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($err) ?>
        </div>
    <?php endif; ?>

    <!-- SUKSES -->
    <?php if(!empty($sukses)): ?>
        <div class="alert-success">
            <?= htmlspecialchars($sukses) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="relative z-10">

        <label class="shinigami-label block">Username
            <input name="username" class="shinigami-input" required>
        </label>

        <label class="shinigami-label mt-3 block">Password
            <input type="password" name="password" class="shinigami-input" required>
        </label>

        <label class="shinigami-label mt-3 block">Nama Lengkap
            <input name="nama_lengkap" class="shinigami-input">
        </label>

        <label class="shinigami-label mt-3 block">Email
            <input type="email" name="email" class="shinigami-input">
        </label>

        <label class="shinigami-label mt-3 block">Alamat
            <textarea name="alamat" class="shinigami-input" rows="3"></textarea>
        </label>

        <label class="shinigami-label mt-3 block">Role
            <select name="role" class="shinigami-input">
                <option value="administrator">Administrator</option>
                <option value="petugas">Petugas</option>
            </select>
        </label>

        <button class="shinigami-btn">
            Buat Akun
        </button>

    </form>
</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
