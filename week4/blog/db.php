<?php
$host = '127.0.0.1';
$port = '3306';
$dbname = 'blog';
$user = 'root';
$pass ='root';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]);
 } catch (PDOException $e) {
     exit('DB接続えらー: ' . $e->getMessage());
     }

     