<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<!-- スマホなど画面幅が狭い端末でも見やすく表示するための設定 -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>お問い合わせ</title>
<style>
  /* ページ全体の見た目（余白・文字色・幅など） */
  body { font-family: sans-serif; padding: 24px; max-width: 480px; margin: 0 auto; color: #333333; }
  h1 { font-size: 22px; }

  /* 各項目のラベル（お名前、メールアドレスなど）の見た目 */
  label { display: block; margin-top: 12px; font-weight: bold; }

  /* テキスト入力欄・メール入力欄・複数行入力欄に共通の見た目 */
  input[type="text"], input[type="email"], textarea {
    width: 100%;
    padding: 8px;
    margin-top: 4px;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 15px;
    border: 1px solid #999999;
    border-radius: 4px;
  }

  /* 入力エラーがある項目は枠を赤くして目立たせる */
  input[aria-invalid="true"], textarea[aria-invalid="true"] {
    border-color: #cc0000;
  }

  textarea { height: 100px; resize: vertical; }

  /* 送信ボタンの見た目 */
  button {
    margin-top: 16px;
    padding: 8px 20px;
    font-size: 15px;
    cursor: pointer;
  }

  /* エラーメッセージの見た目 */
  .error { color: #cc0000; font-size: 13px; margin-top: 4px; }

  /* 送信完了後に表示する内容確認エリアの見た目 */
  .result { background: #f0f4ff; padding: 12px; border-radius: 6px; margin-top: 20px; }

  /* 送信履歴エリアの見た目 */
  .history { margin-top: 32px; }
  .history h2 { font-size: 18px; }
  .submission-list { list-style: none; padding: 0; margin-top: 8px; }
  .submission-item { border: 1px solid #dddddd; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px; }
  .submission-summary { font-size: 14px; }
  .submission-summary .subject { font-weight: bold; }

  /* 「詳細」をボタンのような見た目にする（<summary>はクリックで開閉できる） */
  summary {
    cursor: pointer;
    display: inline-block;
    margin-top: 6px;
    padding: 4px 10px;
    background: #eeeeee;
    border-radius: 4px;
    font-size: 13px;
  }
  .submission-detail p { margin: 6px 0; font-size: 14px; }
</style>
</head>
<body>

<h1>お問い合わせ</h1>

<?php
// 各入力項目の最大文字数（バリデーション用の定数）
const MAX_NAME_LENGTH    = 100;
const MAX_EMAIL_LENGTH   = 254;
const MAX_SUBJECT_LENGTH = 200;
const MAX_MESSAGE_LENGTH = 2000;

// 送信履歴の保存件数と保存先ファイル
const MAX_SUBMISSIONS   = 10;
const SUBMISSIONS_FILE  = __DIR__ . '/data/submissions.php';

// ファイルの先頭に付けておく「ガード行」。
// ブラウザからこのファイルに直接アクセスされても、PHPとして実行された時点で
// 即座に処理が終わるため、後ろに続くデータ（お名前・メールアドレスなど）が
// 画面に表示されることはない。
const SUBMISSIONS_GUARD = "<?php http_response_code(404); exit; ?>\n";

// 保存されている送信履歴を読み込む
// ※ include ではなく file_get_contents で読み込むことで、PHPの内部キャッシュ（OPcache）が
//   古い内容を返してしまう問題を避け、保存直後の最新内容を確実に取得できるようにしている
function loadSubmissions(): array {
    if (!file_exists(SUBMISSIONS_FILE)) {
        return [];
    }

    $content = file_get_contents(SUBMISSIONS_FILE);
    $jsonPart = strstr($content, "\n"); // ガード行（1行目）より後ろの部分を取り出す
    if ($jsonPart === false) {
        return [];
    }

    $data = json_decode($jsonPart, true);
    return is_array($data) ? $data : [];
}

// 送信履歴を保存する（最新 MAX_SUBMISSIONS 件だけ残す）
function saveSubmissions(array $submissions): void {
    $submissions = array_slice($submissions, 0, MAX_SUBMISSIONS);

    $dir = dirname(SUBMISSIONS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $content = SUBMISSIONS_GUARD . json_encode($submissions, JSON_UNESCAPED_UNICODE);
    file_put_contents(SUBMISSIONS_FILE, $content, LOCK_EX);
}

// 入力値・エラー内容を入れておく変数を初期化
$name = "";
$email = "";
$subject = "";
$message = "";
$errors = [];
$submitted = false; // フォームが送信されたかどうかのフラグ

// フォームが送信された（POSTリクエストが来た）ときだけ処理を行う
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submitted = true;

    // 送信された値を取得し、前後の空白を取り除く
    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    // お名前のチェック：未入力 → 文字数オーバーの順に確認
    if ($name === "") {
        $errors["name"] = "お名前を入力してください。";
    } elseif (mb_strlen($name) > MAX_NAME_LENGTH) {
        $errors["name"] = "お名前は" . MAX_NAME_LENGTH . "文字以内で入力してください。";
    }

    // メールアドレスのチェック：未入力 → 文字数オーバー → 形式不正の順に確認
    if ($email === "") {
        $errors["email"] = "メールアドレスを入力してください。";
    } elseif (mb_strlen($email) > MAX_EMAIL_LENGTH) {
        $errors["email"] = "メールアドレスは" . MAX_EMAIL_LENGTH . "文字以内で入力してください。";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "メールアドレスの形式が正しくありません。";
    }

    // 件名のチェック：未入力 → 文字数オーバーの順に確認
    if ($subject === "") {
        $errors["subject"] = "件名を入力してください。";
    } elseif (mb_strlen($subject) > MAX_SUBJECT_LENGTH) {
        $errors["subject"] = "件名は" . MAX_SUBJECT_LENGTH . "文字以内で入力してください。";
    }

    // お問い合わせ内容のチェック：未入力 → 文字数オーバーの順に確認
    if ($message === "") {
        $errors["message"] = "お問い合わせ内容を入力してください。";
    } elseif (mb_strlen($message) > MAX_MESSAGE_LENGTH) {
        $errors["message"] = "お問い合わせ内容は" . MAX_MESSAGE_LENGTH . "文字以内で入力してください。";
    }

    // エラーが1つもなければ、今回の送信内容を履歴の先頭に追加して保存する
    if (empty($errors)) {
        $submissions = loadSubmissions();
        array_unshift($submissions, [
            "name"         => $name,
            "email"        => $email,
            "subject"      => $subject,
            "message"      => $message,
            "submitted_at" => date("Y-m-d H:i:s"),
        ]);
        saveSubmissions($submissions);
    }
}

// 画面下部の履歴一覧に表示するため、最新の状態を読み込んでおく
$submissions = loadSubmissions();
?>

<!-- 入力フォーム本体（送信先は自分自身＝このファイル） -->
<form method="post" action="" novalidate>

  <!-- お名前入力欄 -->
  <label for="name">お名前</label>
  <input
    type="text"
    id="name"
    name="name"
    value="<?php echo htmlspecialchars($name); ?>"
    maxlength="<?php echo MAX_NAME_LENGTH; ?>"
    autocomplete="name"
    aria-invalid="<?php echo isset($errors["name"]) ? "true" : "false"; ?>"
    <?php echo isset($errors["name"]) ? 'aria-describedby="name-error"' : ""; ?>
  >
  <?php if (isset($errors["name"])): ?>
    <!-- お名前にエラーがある場合だけメッセージを表示 -->
    <div class="error" id="name-error"><?php echo htmlspecialchars($errors["name"]); ?></div>
  <?php endif; ?>

  <!-- メールアドレス入力欄 -->
  <label for="email">メールアドレス</label>
  <input
    type="email"
    id="email"
    name="email"
    value="<?php echo htmlspecialchars($email); ?>"
    maxlength="<?php echo MAX_EMAIL_LENGTH; ?>"
    autocomplete="email"
    aria-invalid="<?php echo isset($errors["email"]) ? "true" : "false"; ?>"
    <?php echo isset($errors["email"]) ? 'aria-describedby="email-error"' : ""; ?>
  >
  <?php if (isset($errors["email"])): ?>
    <!-- メールアドレスにエラーがある場合だけメッセージを表示 -->
    <div class="error" id="email-error"><?php echo htmlspecialchars($errors["email"]); ?></div>
  <?php endif; ?>

  <!-- 件名入力欄 -->
  <label for="subject">件名</label>
  <input
    type="text"
    id="subject"
    name="subject"
    value="<?php echo htmlspecialchars($subject); ?>"
    maxlength="<?php echo MAX_SUBJECT_LENGTH; ?>"
    aria-invalid="<?php echo isset($errors["subject"]) ? "true" : "false"; ?>"
    <?php echo isset($errors["subject"]) ? 'aria-describedby="subject-error"' : ""; ?>
  >
  <?php if (isset($errors["subject"])): ?>
    <!-- 件名にエラーがある場合だけメッセージを表示 -->
    <div class="error" id="subject-error"><?php echo htmlspecialchars($errors["subject"]); ?></div>
  <?php endif; ?>

  <!-- お問い合わせ内容入力欄（複数行） -->
  <label for="message">お問い合わせ内容</label>
  <textarea
    id="message"
    name="message"
    maxlength="<?php echo MAX_MESSAGE_LENGTH; ?>"
    aria-invalid="<?php echo isset($errors["message"]) ? "true" : "false"; ?>"
    <?php echo isset($errors["message"]) ? 'aria-describedby="message-error"' : ""; ?>
  ><?php echo htmlspecialchars($message); ?></textarea>
  <?php if (isset($errors["message"])): ?>
    <!-- お問い合わせ内容にエラーがある場合だけメッセージを表示 -->
    <div class="error" id="message-error"><?php echo htmlspecialchars($errors["message"]); ?></div>
  <?php endif; ?>

  <button type="submit">送信</button>
</form>

<?php if ($submitted && empty($errors)): ?>
  <!-- 送信済み かつ エラーが1つもない場合だけ、入力内容を確認表示する -->
  <div class="result">
    <h2>送信内容</h2>
    <p>お名前：<?php echo htmlspecialchars($name); ?></p>
    <p>メールアドレス：<?php echo htmlspecialchars($email); ?></p>
    <p>件名：<?php echo htmlspecialchars($subject); ?></p>
    <!-- nl2br() で改行を <br> に変換し、複数行の入力内容も見た目通りに表示 -->
    <p>お問い合わせ内容：<?php echo nl2br(htmlspecialchars($message)); ?></p>
  </div>
<?php endif; ?>

<!-- これまでの送信履歴（最新10件）。各項目の「詳細」を押すと内容が展開される -->
<div class="history">
  <h2>これまでのお問い合わせ（最新<?php echo MAX_SUBMISSIONS; ?>件）</h2>

  <?php if (empty($submissions)): ?>
    <p>まだお問い合わせはありません。</p>
  <?php else: ?>
    <ul class="submission-list">
      <?php foreach ($submissions as $item): ?>
        <li class="submission-item">
          <!-- 一覧では日時・件名・お名前だけを表示 -->
          <div class="submission-summary">
            <?php echo htmlspecialchars($item["submitted_at"]); ?>
            ／<span class="subject"><?php echo htmlspecialchars($item["subject"]); ?></span>
            ／<?php echo htmlspecialchars($item["name"]); ?>様
          </div>

          <!-- <details><summary>詳細</summary>...</details> はJS不要でクリックすると開閉できるHTML標準の機能 -->
          <details>
            <summary>詳細</summary>
            <div class="submission-detail">
              <p>お名前：<?php echo htmlspecialchars($item["name"]); ?></p>
              <p>メールアドレス：<?php echo htmlspecialchars($item["email"]); ?></p>
              <p>件名：<?php echo htmlspecialchars($item["subject"]); ?></p>
              <p>お問い合わせ内容：<?php echo nl2br(htmlspecialchars($item["message"])); ?></p>
            </div>
          </details>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

</body>
</html>
