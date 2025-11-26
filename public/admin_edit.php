<?php
require_once __DIR__.'/../templates/header.php';
$auth = new Auth($db->pdo());
$auth->requireRole(['administrator']);
$userModel = new UserModel($db->pdo());

$id = intval($_GET['id'] ?? 0);
$user = $userModel->find($id);

if(!$user){
    echo "Akun tidak ditemukan.";
    require_once __DIR__.'/../templates/footer.php';
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username']);
    $nama     = $_POST['nama_lengkap'];
    $email    = $_POST['email'];
    $alamat   = $_POST['alamat'];
    $role     = $_POST['role'];

    // Jika password diisi → update. Jika kosong → jangan diubah
    $password_sql = "";
    $params = [$username, $nama, $email, $alamat, $role, $id];

    if(!empty($_POST['password'])){
        $password_sql = ", password = ?";
        array_splice($params, 5, 0, password_hash($_POST['password'], PASSWORD_DEFAULT));
    }

    $stmt = $db->pdo()->prepare("
        UPDATE users 
        SET username=?, nama_lengkap=?, email=?, alamat=?, role=? $password_sql 
        WHERE id=?
    ");
    $stmt->execute($params);

    header("Location: admin_users.php");
    exit;
}
?>

<style>
    /* === CARD EDIT ADMIN WATER THEME === */
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
    .edit-title-water {
        font-size: 1.9rem;
        font-weight: 800;
        color: #e0f2fe;
        text-shadow:
            0 0 18px rgba(56, 189, 248, 0.9),
            0 0 30px rgba(37, 99, 235, 0.8);
        letter-spacing: 0.18em;
        text-transform: uppercase;
        position: relative;
    }

    .edit-title-water::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -8px;
        width: 160px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.95);
    }

    .sh-back {
        color: #bae6fd;
        font-size: 0.85rem;
        transition: color .25s ease, text-shadow .25s ease, transform .15s ease;
    }

    .sh-back:hover {
        color: #e0f2fe;
        text-shadow:
            0 0 10px rgba(56, 189, 248, 0.9),
            0 0 14px rgba(37, 99, 235, 0.7);
        transform: translateY(-1px);
    }

    /* INPUT, TEXTAREA, SELECT */
    .sh-label {
        font-size: 0.9rem;
        color: #bae6fd;
        font-weight: 500;
    }

    .sh-input {
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

    .sh-input::placeholder {
        color: #64748b;
    }

    .sh-input:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow:
            0 0 0 1px rgba(56, 189, 248, 0.9),
            0 0 18px rgba(56, 189, 248, 0.7);
        background: radial-gradient(circle at top, rgba(15, 23, 42, 1), rgba(8, 47, 73, 0.95));
        transform: translateY(-1px);
    }

    textarea.sh-input {
        min-height: 90px;
        resize: vertical;
    }

    select.sh-input {
        cursor: pointer;
    }

    /* BUTTON SUBMIT */
    .sh-btn {
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

    .sh-btn:hover {
        background: linear-gradient(135deg, #38bdf8, #1d4ed8);
        transform: translateY(-2px);
        box-shadow:
            0 18px 40px rgba(30, 64, 175, 0.95),
            0 0 36px rgba(56, 189, 248, 1);
    }

    .sh-btn:active {
        transform: translateY(0);
        box-shadow:
            0 10px 22px rgba(15, 23, 42, 0.9),
            0 0 20px rgba(56, 189, 248, 0.7);
    }
</style>

<div class="max-w-lg mx-auto card-shinigami">

  <!-- HEADER + BUTTON BACK -->
  <div class="flex justify-between items-center mb-4 relative z-10">
      <h2 class="edit-title-water">Edit Akun</h2>

      <a href="admin_users.php" class="sh-back">← Kembali</a>
  </div>

  <form method="post" class="relative z-10">

    <label class="block sh-label">Username
      <input name="username" class="sh-input"
             value="<?= htmlspecialchars($user['username']) ?>" required>
    </label>

    <label class="block sh-label mt-3">Password (opsional)
      <input type="password" name="password"
             class="sh-input"
             placeholder="Biarkan kosong jika tidak diubah">
    </label>

    <label class="block sh-label mt-3">Nama Lengkap
      <input name="nama_lengkap" class="sh-input"
             value="<?= htmlspecialchars($user['nama_lengkap']) ?>">
    </label>

    <label class="block sh-label mt-3">Email
      <input type="email" name="email" class="sh-input"
             value="<?= htmlspecialchars($user['email']) ?>">
    </label>

    <label class="block sh-label mt-3">Alamat
      <textarea name="alamat" class="sh-input"
                rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea>
    </label>

    <label class="block sh-label mt-3">Role
      <select name="role" class="sh-input">
        <option value="administrator" <?= $user['role']=='administrator'?'selected':'' ?>>Administrator</option>
        <option value="petugas" <?= $user['role']=='petugas'?'selected':'' ?>>Petugas</option>
      </select>
    </label>

    <button class="sh-btn">
      Update Akun
    </button>

  </form>

</div>

<?php require_once __DIR__.'/../templates/footer.php'; ?>
