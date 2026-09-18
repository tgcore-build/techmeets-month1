<?php 
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_POST['author'];

    $stmt = $pdo->prepare('INSERT INTO posts (title, content, author) VALUES (?, ?, ?)');
    $stmt->execute([$title, $content, $author]);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規投稿</title>
</head>
<body>
    <p><a href="index.php">← 一覧へ戻る</a></p>

    <h1>新規投稿</h1>

    <form method="post" action="create.php">
        <p>
            <label>タイトル：<br>
                <input type="text" name="title">
            </label>
        </p>
        <p>
            <label>著者：<br>
                <input type="text" name="author">
            </label>
        </p>
        <p>
            <label>本文：<br>
                <textarea name="content"></textarea>
            </label>
        </p>
        <p>
            <button type="submit">投稿する</button>
        </p>
    </form>
</body>
</html>