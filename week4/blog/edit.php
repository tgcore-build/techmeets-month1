<?php
require 'db.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_POST['author'];

    $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ?, author = ? WHERE id = ?');
    $stmt->execute([$title, $content, $author, $id]);

    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>記事の編集</title>
</head>
<body>
    <p><a href="index.php">← 一覧へ戻る</a></p>

    <h1>記事の編集</h1>

    <form method="post" action="edit.php?id=<?= $post['id'] ?>">
        <p>
            <label>タイトル：<br>
                <input type="text" name="title" value="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
        </p>
        <p>
            <label>著者：<br>
                <input type="text" name="author" value="<?= htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8') ?>">
            </label>
        </p>
        <p>
            <label>本文：<br>
                <textarea name="content"><?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
        </p>
        <p>
            <button type="submit">更新する</button>
        </p>
    </form>
</body>
</html>