<?php
require 'db.php';

$id = $_GET['id'];

$stm = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stm->execute([$id]);
$post = $stm->fetch();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <p><a href="index.php">一覧に戻る</a></p>
    <h1><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>投稿者: <?= htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8') ?></p>
    <p>投稿日: <?= $post['created_at'] ?></p>
    <div>
        <?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?>
    </div>
</body>
</html>


