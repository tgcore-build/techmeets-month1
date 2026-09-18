<?php
require 'db.php';

$stmt = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC');
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>My Blog</title>
</head>
<body>
    <header>
        <h1>My Blog</h1>
    </header>
    <p><a href="create.php">新規投稿</a></p>

    <?php if (empty($posts)): ?>
        <p>投稿はまだありません。</p>
    <?php else: ?>
        <ul>
            <?php foreach ($posts as $post): ?>
                <li>
                    <a href="show.php?id=<?= $post['id'] ?>">
                        <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    ｜<?= htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8') ?>
                    ｜<?= $post['created_at'] ?>
                    ｜<a href="edit.php?id=<?= $post['id'] ?>">編集</a>
                    ｜<a href="delete.php?id=<?= $post['id'] ?>">削除</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
