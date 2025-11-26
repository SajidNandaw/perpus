<?php
// app/Auth.php
class Auth {
    private $db;

    public function __construct($db){
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password){
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $u = $stmt->fetch();

        if ($u) {
            $dbPass = $u['password'];
            $isCorrect = false;

            // 1) Jika hash → verifikasi hash
            if (password_verify($password, $dbPass)) {
                $isCorrect = true;
            }
            // 2) Jika plaintext → bandingkan langsung
            else if ($password === $dbPass) {
                $isCorrect = true;
            }

            if ($isCorrect) {
                $_SESSION['user'] = [
                    'id'       => $u['id'],
                    'username' => $u['username'],
                    'role'     => $u['role'],
                    'nama'     => $u['nama_lengkap']
                ];


                return true;
            }
        }

        return false;
    }

    public function logout(){
        session_unset();
        session_destroy();
    }

    public function check(){
        return isset($_SESSION['user']);
    }

    public function user(){
        return $_SESSION['user'] ?? null;
    }

    public function requireRole($roles = []){
        if (!$this->check()) {
            header('Location: login.php');
            exit;
        }

        if (!in_array($this->user()['role'], $roles)) {
            echo "Akses ditolak";
            exit;
        }
    }
}
