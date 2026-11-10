<?php
require __DIR__ . '/config.php';
unset($_SESSION['admin_id'], $_SESSION['user_id'], $_SESSION['user_nama']);
session_regenerate_id(true);
flash('Kamu sudah logout.');
go('index.php');
