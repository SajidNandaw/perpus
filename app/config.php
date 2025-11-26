<?php
// app/config.php
session_start();

define('DB_HOST','127.0.0.1');
define('DB_NAME','perpus');
define('DB_USER','root');
define('DB_PASS',''); // isi sesuai environment

// base url kalau perlu
define('BASE_URL', '/perpus-digital/public'); // ubah sesuai path hostmu

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Models/Book.php';
require_once __DIR__ . '/Models/Peminjaman.php';
require_once __DIR__ . '/Models/Log.php';
require_once __DIR__ . '/Models/Review.php';

$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
