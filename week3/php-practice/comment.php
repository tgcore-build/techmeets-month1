<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>コメント投稿</title>
</head>
<body>

<h1>コメント投稿フォーム</h1>

<!-- コメント投稿フォーム本体（送信先は自分自身＝このファイル） -->
<form method="POST">
  <label>名前:</label>
  <input type="text" name="name"><br>
  <label>コメント:</label>
  <textarea name="comment"></textarea><br>
  <button type="submit">投稿する</button>
</form>

<?php
// フォームが送信された（POSTリクエストが来た）ときだけ処理を行う
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 送信された名前・コメントを取得
    $name    = $_POST["name"];
    $comment = $_POST["comment"];

    // 名前・コメントのどちらかが空文字ならエラーメッセージを表示
    if ($name === "" || $comment === "") {
        echo "名前とコメントを入力してください。";
    } else {
        // htmlspecialchars() でエスケープしてからHTMLに出力する
        // （入力値をそのまま出力するとXSS〔スクリプト埋め込み〕の危険があるため）
        echo "<p>" . htmlspecialchars($name) . "さんのコメント:</p>";
        echo "<p>" . htmlspecialchars($comment) . "</p>";
    }
}
?>

</body>
</html>
