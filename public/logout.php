<?php
require_once __DIR__.'/../app/config.php';
$auth = new Auth($db->pdo());
$auth->logout();
header('Location: login.php'); exit;
